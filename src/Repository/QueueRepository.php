<?php

namespace Pantono\Queue\Repository;

use Pantono\Database\Repository\MysqlRepository;
use Pantono\Queue\Model\QueueTask;
use Pantono\Queue\Model\Queue;
use Pantono\Queue\Model\QueueSubscription;
use DateTimeInterface;
use DateTime;
use Pantono\Queue\Filter\QueueTaskFilter;

class QueueRepository extends MysqlRepository
{
    /**
     * @return array<mixed>|null
     */
    public function getTaskById(int $id): ?array
    {
        return $this->selectSingleRow('queue_task', 'id', $id);
    }

    public function saveTask(QueueTask $task): void
    {
        $id = $this->insertOrUpdate('queue_task', 'id', $task->getId(), $task->getAllData());
        if ($id) {
            $task->setId($id);
        }
    }

    public function getQueueById(int $id): ?array
    {
        return $this->selectSingleRow('queue', 'id', $id);
    }

    public function getQueueByShortName(string $name): ?array
    {
        return $this->selectSingleRow('queue', 'short_name', $name);
    }

    public function getTasksInQueue(Queue $queue): array
    {
        $select = $this->getDb()->select()->from('queue_task')
            ->joinInner('queue_subscription', 'queue_task.subscription_id=queue_subscription.id', [])
            ->where('queue_task.queue_id=?', $queue->getId());

        return $this->getDb()->fetchAll($select);
    }

    public function getAllQueues(): array
    {
        return $this->selectAll('queue');
    }

    public function saveQueue(Queue $queue): void
    {
        $data = [
            'name' => $queue->getName(),
            'short_name' => $queue->getShortName(),
            'date_created' => $queue->getDateCreated()->format('Y-m-d H:i:s'),
        ];
        $id = $this->insertOrUpdate('queue', 'id', $queue->getId(), $data);
        if ($id) {
            $queue->setId($id);
        }
    }

    public function getSubscriptionByTaskAndQueue(string $taskName, Queue $queue): ?array
    {
        return $this->selectRowByValues('queue_subscription', ['task_name' => $taskName, 'queue_id' => $queue->getId()]);
    }

    public function markSubscriptionsDeletedNotIn(Queue $queue, array $subs): int
    {
        if (count($subs)) {
            return $this->getDb()->update('queue_subscription', ['deleted' => 1], ['queue_id=?' => $queue->getId(), 'id NOT IN (?)' => $subs]);
        }

        return 0;
    }

    public function saveSubscription(QueueSubscription $subscription): void
    {
        $id = $this->insertOrUpdate('queue_subscription', 'id', $subscription->getId(), $subscription->getAllData());
        if ($id) {
            $subscription->setId($id);
        }
    }

    public function getSubscribersForTask(string $task): array
    {
        return $this->selectRowsByValues('queue_subscription', ['task_name' => $task, 'deleted' => 0]);
    }

    public function getTaskCount(Queue $queue, DateTime $start, DateTime $end): int
    {
        $select = $this->getDb()->select()->from('queue_task')
            ->where('queue=?', $queue->getId())
            ->where('date_created >= ?', $start->format('Y-m-d H:i:s'))
            ->where('date_created <= ?', $end->format('Y-m-d H:i:s'));

        return $this->getCount($select);
    }

    public function getOutstandingTaskCount(Queue $queue): int
    {
        $select = $this->getDb()->select()->from('queue_task')->where('queue=?', $queue->getId())
            ->where('date_picked_up IS NULL');

        return $this->getCount($select);
    }

    public function getLateTasksInQueue(Queue $queue): int
    {
        $select = $this->getDb()->select()->from('queue_task')
            ->where('queue=?', $queue->getId())
            ->where('date_picked_up IS NULL')
            ->where('date_created <= ?', (new \DateTimeImmutable('-30 minute'))->format('Y-m-d H:i:s'));

        return $this->getCount($select);
    }

    public function pruneQueueTasks(DateTimeInterface $beforeDate): void
    {
        $this->getDb()->delete(
            'queue_task',
            [
                'date_picked_up <= ? ' => $beforeDate->format('Y-m-d H:i:s'),
                'date_picked_up IS NOT NULL'
            ]
        );

        $this->getDb()->query('DELETE from queue_task where date_picked_up IS NULL and date_created <= DATE_SUB(NOW(), INTERVAL 1 WEEK)');
    }

    public function getLateTasks(): array
    {
        $select = $this->getDb()->select()->from('queue_task')
            ->where('date_created <= ?', (new \DateTimeImmutable('-30 minute'))->format('Y-m-d H:i:s'))
            ->where('date_picked_up IS NULL');

        return $this->getDb()->fetchAll($select);
    }

    public function getErrors(DateTimeInterface $fromDate): array
    {
        $select = $this->getDb()->select()->from('queue_task')
            ->where('error IS NOT NULL')
            ->where('date_created >= ?', $fromDate->format('Y-m-d H:i:s'));

        return $this->getDb()->fetchAll($select);
    }

    public function getSubscriptionById(int $id): ?array
    {
        return $this->selectSingleRow('queue_subscription', 'id', $id);
    }

    public function getQueueTasksByFilter(QueueTaskFilter $filter): array
    {
        $select = $this->getDb()->select()->from('queue_task')
            ->joinInner('queue_subscription', 'queue_task.subscription_id=queue_subscription.id', []);

        if ($filter->getCompleted() === true) {
            $select->where('date_completed IS NOT NULL');
        }
        if ($filter->getCompleted() === false) {
            $select->where('date_completed IS NULL');
        }
        if ($filter->getPickedUp() === true) {
            $select->where('date_picked_up IS NOT NULL');
        }
        if ($filter->getPickedUp() === false) {
            $select->where('date_picked_up IS NULL');
        }
        if ($filter->getDatePickedUpStart() !== null) {
            $select->where('date_picked_up >= ?', $filter->getDatePickedUpStart()->format('Y-m-d H:i:s'));
        }
        if ($filter->getDatePickedUpEnd() !== null) {
            $select->where('date_picked_up <= ?', $filter->getDatePickedUpEnd()->format('Y-m-d H:i:s'));
        }
        if ($filter->getDateCompletedStart() !== null) {
            $select->where('date_completed >= ?', $filter->getDateCompletedStart()->format('Y-m-d H:i:s'));
        }
        if ($filter->getDateCompletedEnd() !== null) {
            $select->where('date_completed <= ?', $filter->getDateCompletedEnd()->format('Y-m-d H:i:s'));
        }
        if ($filter->getDateCreatedStart() !== null) {
            $select->where('date_created >= ?', $filter->getDateCreatedStart()->format('Y-m-d H:i:s'));
        }
        if ($filter->getDateCreatedEnd() !== null) {
            $select->where('date_created <= ?', $filter->getDateCreatedEnd()->format('Y-m-d H:i:s'));
        }
        if ($filter->getSubscription() !== null) {
            $select->where('queue_task.subscription_id=?', $filter->getSubscription()->getId());
        }
        if ($filter->getTaskName() !== null) {
            $select->where('queue_subscription.task_name=?', $filter->getTaskName());
        }
        $filter->setTotalResults($this->getCount($select));

        $select->limitPage($filter->getPage(), $filter->getPerPage());
        return $this->getDb()->fetchAll($select);
    }

    public function getQueueStats(): array
    {
        $sql = <<<SQL
SELECT 'total' as name, COUNT(1) as cnt from queue_task
UNION ALL
SELECT 'outstanding' as name, COUNT(1) from queue_task where date_picked_up IS NULL
UNION ALL
SELECT 'error' as name, COUNT(1) from queue_task where error is not null
UNION ALL
SELECT 'recent' as name, COUNT(1) as cnt from queue_task where date_created >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
SQL;
        return $this->getDb()->fetchAll($sql);
    }
}
