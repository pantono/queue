<?php

namespace Pantono\Queue\Factory;

use Interop\Queue\ConnectionFactory;
use Pantono\Queue\Model\Queue;
use Pantono\Queue\Queue\QueueInstance;
use Pantono\Utilities\ApplicationHelper;

class QueueFactory
{
    private ConnectionFactory $connectionFactory;

    private string $env;

    private array $queues = [];

    public function __construct(ConnectionFactory $connectionFactory)
    {
        $this->connectionFactory = $connectionFactory;
        $this->env = ApplicationHelper::getEnv();
    }

    public function generateQueue(Queue $queue): QueueInstance
    {
        $name = $this->env . $queue->getShortName();
        if (!isset($this->queues[$name])) {
            $context = $this->connectionFactory->createContext();
            $topic = $context->createTopic($name);

            $queue = $context->createQueue($name);

            $this->queues[$name] = new QueueInstance($context, $topic, $queue);
        }

        return $this->queues[$name];
    }
}
