<?php

namespace Pantono\Queue\Task;

use Symfony\Component\HttpFoundation\ParameterBag;
use Pantono\Queue\Model\QueueTask;
use Pantono\Queue\QueueManager;
use Pantono\Hydrator\Hydrator;

abstract class AbstractTask
{
    private ParameterBag $config;
    private QueueTask $task;
    private QueueManager $queueManager;
    private ?Hydrator $hydrator = null;

    /**
     * @return array<mixed>
     */
    abstract public function process(ParameterBag $parameters): array;

    public function getConfig(): ParameterBag
    {
        return $this->config;
    }

    public function setConfig(ParameterBag $config): void
    {
        $this->config = $config;
    }

    public function getQueueManager(): QueueManager
    {
        return $this->queueManager;
    }

    public function setQueueManager(QueueManager $queueManager): void
    {
        $this->queueManager = $queueManager;
    }

    public function getHydrator(): ?Hydrator
    {
        return $this->hydrator;
    }

    public function setHydrator(?Hydrator $hydrator): void
    {
        $this->hydrator = $hydrator;
    }

    public function setStatusForCurrentTask(array $data): void
    {
        $this->getTask()->setStatus($data);
        $this->queueManager->saveTask($this->getTask());
    }

    public function getTask(): QueueTask
    {
        return $this->task;
    }

    public function setTask(QueueTask $task): void
    {
        $this->task = $task;
    }
}
