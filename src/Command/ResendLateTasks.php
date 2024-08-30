<?php

namespace Pantono\Queue\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pantono\Queue\QueueManager;

class ResendLateTasks extends Command
{
    private QueueManager $queueManager;

    public function __construct(QueueManager $queueManager)
    {
        $this->queueManager = $queueManager;

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->queueManager->getLateTasks() as $task) {
            $output->writeln('Resent ' . $task->getId() . ' Name: ' . $task->getQueueSubscription()->getTaskName());
            $this->queueManager->resendTask($task);
        }
        return 0;
    }

    protected function configure(): void
    {
        $this->setName('app:resend-late-tasks');
    }
}
