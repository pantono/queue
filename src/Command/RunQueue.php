<?php

namespace Pantono\Queue\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pantono\Utilities\ApplicationHelper;
use Pantono\Queue\QueueManager;
use Pantono\Queue\Model\Queue;

class RunQueue extends Command
{
    private QueueManager $queueManager;

    public function __construct(QueueManager $queueManager)
    {
        $this->queueManager = $queueManager;
        parent::__construct();
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        $queueName = $this->queueManager->getQueueName();
        $queue = $this->queueManager->getQueueByShortName($queueName);
        if ($queue === null) {
            $queue = new Queue();
            $queue->setName($queueName);
            $queue->setShortName(strtolower(str_replace(' ', '_', $queueName)));
            $queue->setDateCreated(new \DateTimeImmutable());
            $this->queueManager->saveQueue($queue);
            $output->writeln('Created new queue ' . $queue->getName() . ' (' . $queue->getShortName() . ')');
        }

        $output->writeln('Listening on ' . $queue->getName() . ' - ' . ApplicationHelper::getEnv() . $queue->getShortName());
        $messages = 0;
        while ($this->queueManager->listen($queue, $output)) {
            $messages++;
        }
        $output->writeln('No message after processing ' . $messages);
        return 1;
    }

    protected function configure(): void
    {
        $this->setName('queue:run');
    }
}
