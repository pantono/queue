<?php

namespace Pantono\Queue;

use Interop\Queue\Consumer;
use Interop\Queue\Message;
use Pantono\Hydrator\Hydrator;
use Pantono\Queue\Model\Queue;
use Pantono\Queue\Model\QueueSubscription;
use Pantono\Queue\Model\QueueTask;
use Pantono\Queue\Repository\QueueRepository;
use Symfony\Component\Console\Output\OutputInterface;
use Pantono\Utilities\Timer;
use DateTimeInterface;
use Pantono\Queue\Factory\QueueFactory;
use DateTime;
use Pantono\Hydrator\Traits\LocatorAwareTrait;
use Symfony\Component\HttpFoundation\ParameterBag;
use Pantono\Config\Config;
use Pantono\Utilities\ApplicationHelper;

class QueueManager
{
    use LocatorAwareTrait;

    public const int PRIORITY_LOW = 1;
    public const int PRIORITY_NORMAL = 3;
    public const int PRIORITY_HIGH = 6;
    public const int PRIORITY_CRITICAL = 9;
    private QueueRepository $repository;
    private Hydrator $hydrator;
    private QueueFactory $factory;
    private Config $config;
    private string $queueName;

    public function __construct(QueueRepository $repository, QueueFactory $factory, Hydrator $hydrator, Config $config)
    {
        $this->repository = $repository;
        $this->hydrator = $hydrator;
        $this->factory = $factory;
        $this->config = $config;
        $value = $config->getApplicationConfig()->getValue('queue.name');
        if (!$value) {
            $parts = explode(DIRECTORY_SEPARATOR, ApplicationHelper::getApplicationRoot());
            $value = array_pop($parts);
        }
        $this->queueName = $value;
    }

    /**
     * @return array<int, Queue>
     */
    public function getAllQueue(): array
    {
        return $this->hydrator->hydrateSet(Queue::class, $this->repository->getAllQueues());
    }

    public function getQueueById(int $id): ?Queue
    {
        return $this->hydrator->hydrate(Queue::class, $this->repository->getQueueById($id));
    }

    public function getQueueByShortName(string $name): ?Queue
    {
        return $this->hydrator->hydrate(Queue::class, $this->repository->getQueueByShortName($name));
    }

    /**
     * @return QueueTask[]
     */
    public function createTask(string $taskName, array $parameters = [], ?string $taskId = null, ?int $ttl = null, int $priority = self::PRIORITY_NORMAL): array
    {
        $tasks = [];
        foreach ($this->getSubscribersForTask($taskName) as $sub) {
            $tasks[] = $this->createSingleSubscriberTask($sub, $parameters, $taskId, $ttl, $priority);
        }

        return $tasks;
    }

    /**
     * @return QueueSubscription[]
     */
    public function getSubscribersForTask(string $taskName): array
    {
        return $this->hydrator->hydrateSet(QueueSubscription::class, $this->repository->getSubscribersForTask($taskName));
    }

    private function createSingleSubscriberTask(QueueSubscription $sub, array $parameters = [], ?string $taskId = null, ?int $ttl = null, int $priority = self::PRIORITY_NORMAL): QueueTask
    {
        $task = new QueueTask();
        $task->setQueueSubscription($sub);
        $task->setDateCreated(new \DateTimeImmutable());
        $task->setStatus([]);
        $task->setParameters($parameters);
        $task->setPriority($priority);
        $task->setTtl($ttl);
        $this->saveTask($task);

        try {
            $rQueue = $this->factory->generateQueue($sub->getQueue());
            $rQueue->send(['task_id' => $task->getId(), 'attempt' => 1]);
        } catch (\Exception $e) {
            $task->setError($e->getMessage());
            $this->saveTask($task);
        }
        return $task;
    }

    public function saveTask(QueueTask $task): void
    {
        $this->repository->saveTask($task);
    }

    /**
     * @return QueueTask[]
     */
    public function getLateTasks(): array
    {
        return $this->hydrator->hydrateSet(QueueTask::class, $this->repository->getLateTasks());
    }

    public function updateQueueSubscriptions(Queue $queue): int
    {
        $subs = [];
        $count = 0;
        $tasks = $this->config->getConfigForType('queue_tasks');
        foreach ($tasks->getAllData() as $name => $task) {
            $sub = $this->getSubscriptionByTaskAndQueue($task['task'], $queue);
            if (!$sub) {
                $count++;
                $sub = new QueueSubscription();
                $sub->setController($task['controller']);
                $sub->setQueue($queue);
                $sub->setTaskName($task['task']);
                $sub->setDateCreated(new \DateTimeImmutable());
            }
            if ($sub->isDeleted()) {
                $count++;
            }
            $sub->setController($task['controller']);
            $sub->setDateLastTask(new \DateTimeImmutable());
            $sub->setDeleted(false);
            $this->saveSubscription($sub);
            $subs[] = $sub->getId();
        }
        $count += $this->repository->markSubscriptionsDeletedNotIn($queue, $subs);

        return $count;
    }

    public function getSubscriptionByTaskAndQueue(string $taskName, Queue $queue): ?QueueSubscription
    {
        return $this->hydrator->hydrate(QueueSubscription::class, $this->repository->getSubscriptionByTaskAndQueue($taskName, $queue));
    }

    public function saveSubscription(QueueSubscription $subscription): void
    {
        $this->repository->saveSubscription($subscription);
    }

    public function listen(Queue $queue, ?OutputInterface $output = null): bool
    {
        $listener = $this->factory->generateQueue($queue);
        $listener->listen(function (Message $message, Consumer $consumer) use ($output, $listener) {
            $data = json_decode($message->getBody());
            $taskId = $data->task_id;
            $attempt = (int)$data->attempt;
            $task = $this->getTaskById($taskId);
            if (!$task) {
                if ($output !== null) {
                    $output->writeln('[' . date('d/m/Y H:i:s') . '] Task ID: ' . $taskId . 'Cannot find task ' . $message->getBody() . ' re-sending...');
                }
                $consumer->acknowledge($message);
                if ($attempt > 2000) {
                    return;
                }
                usleep(5000);
                $listener->send(['task_id' => $taskId, 'attempt' => $attempt + 1]);

                return;
            }
            $controller = $this->getLocator()->getClassAutoWire($task->getQueueSubscription()->getController());
            if (!$controller) {
                $task->setError('No controller available ' . $task->getQueueSubscription()->getController());
                $this->saveTask($task);
                $consumer->acknowledge($message);
                return;
            }
            $task->getQueueSubscription()->setDateLastTask(new \DateTimeImmutable());
            $this->saveSubscription($task->getQueueSubscription());
            if ($output) {
                $output->writeLn('[' . date('d/m/Y H:i:s') . '] Task ID: ' . $taskId . ' Task: ' . $task->getQueueSubscription()->getTaskName());
            }
            try {
                $consumer->acknowledge($message);
                Timer::start('task');
                $response = $controller->process(new ParameterBag($task->getParameters()));
                Timer::end('task');
                $task->setStatus($response);
            } catch (\Exception $e) {
                if ($output) {
                    $output->writeln('[' . date('d/m/Y H:i:s') . '] Task ID: ' . $taskId . ' Error: ' . $e->getMessage());
                }
                $task->setError($e->getMessage());
            }
            $task->setDateCompleted(new \DateTimeImmutable());
            $task->setTimeTaken(Timer::getTime('task'));
            $this->saveTask($task);
            if ($output !== null) {
                $output->writeln('[' . date('d/m/Y H:i:s') . '] Task ID: ' . $taskId . ' Time Taken: ' . Timer::getTime('task') . 'ms');
            }
        });

        return true;
    }

    public function getTaskById(int $id): ?QueueTask
    {
        return $this->hydrator->hydrate(QueueTask::class, $this->repository->getTaskById($id));
    }

    public function saveQueue(Queue $queue): void
    {
        $this->repository->saveQueue($queue);
    }

    public function getQueueName(): string
    {
        return $this->queueName;
    }

    public function pruneQueueTasks(): void
    {
        $this->repository->pruneQueueTasks(new DateTime('-1 week'));
    }

    public function getErrors(DateTimeInterface $fromDate): array
    {
        return $this->repository->getErrors($fromDate);
    }

    public function resendTask(QueueTask $task, int $attempt = 1): void
    {
        $listener = $this->factory->generateQueue($task->getQueueSubscription()->getQueue());
        $listener->send(['task_id' => $task->getId(), 'attempt' => $attempt + 1]);
    }

    public function getSubscriptionById(int $id): ?QueueSubscription
    {
        return $this->hydrator->hydrate(QueueSubscription::class, $this->repository->getSubscriptionById($id));
    }
}
