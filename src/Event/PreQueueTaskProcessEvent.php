<?php

namespace Pantono\Queue\Event;

use Symfony\Contracts\EventDispatcher\Event;

class PreQueueTaskProcessEvent extends Event
{
    private int $taskId;
    private array $data;
    private bool $skip = false;

    public function getTaskId(): int
    {
        return $this->taskId;
    }

    public function setTaskId(int $taskId): void
    {
        $this->taskId = $taskId;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function isSkip(): bool
    {
        return $this->skip;
    }

    public function setSkip(bool $skip): void
    {
        $this->skip = $skip;
    }
}
