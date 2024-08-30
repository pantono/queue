<?php

namespace Pantono\Queue\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pantono\Queue\QueueManager;

class ResendTask extends Command
{
    private QueueManager $queueManager;

    public function __construct(QueueManager $queueManager)
    {
        $this->queueManager = $queueManager;

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $task = $this->queueManager->getTaskById($input->getArgument('id'));
        if ($task === null) {
            throw new \RuntimeException('Task does not exist');
        }
        $task->setError(null);
        $this->queueManager->saveTask($task);
        $this->queueManager->resendTask($task);
        $output->writeln('Resent task ' . $task->getId());
        return 0;
    }

    protected function configure()
    {
        $this->setName('app:resend-task')
            ->addArgument('id', InputArgument::REQUIRED, 'Task ID');
    }
}
