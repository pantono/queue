<?php

namespace Pantono\Queue\Model;

use Pantono\Contracts\Attributes\Locator;
use Pantono\Database\Traits\SavableModel;
use Pantono\Contracts\Attributes\FieldName;

class QueueSubscription
{
    use SavableModel;

    private ?int $id = null;
    #[Locator('QueueManager', 'getQueueById'), FieldName('queue_id')]
    private Queue $queue;
    private string $taskName;
    private string $controller;
    private \DateTimeImmutable $dateCreated;
    private ?\DateTimeImmutable $dateLastTask;
    private bool $deleted = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getQueue(): Queue
    {
        return $this->queue;
    }

    public function setQueue(Queue $queue): void
    {
        $this->queue = $queue;
    }

    public function getTaskName(): string
    {
        return $this->taskName;
    }

    public function setTaskName(string $taskName): void
    {
        $this->taskName = $taskName;
    }

    public function getController(): string
    {
        return $this->controller;
    }

    public function setController(string $controller): void
    {
        $this->controller = $controller;
    }

    public function getDateCreated(): \DateTimeImmutable
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeImmutable $dateCreated): void
    {
        $this->dateCreated = $dateCreated;
    }

    public function getDateLastTask(): ?\DateTimeImmutable
    {
        return $this->dateLastTask;
    }

    public function setDateLastTask(?\DateTimeImmutable $dateLastTask): void
    {
        $this->dateLastTask = $dateLastTask;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }
}
