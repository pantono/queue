<?php

namespace Pantono\Queue\Model;

use Pantono\Contracts\Attributes\Filter;
use Pantono\Contracts\Attributes\Locator;
use Pantono\Database\Traits\SavableModel;
use DateTimeImmutable;

class QueueTask
{
    use SavableModel;

    private ?int $id = null;
    #[Locator('QueueManager', 'getSubscriptionById')]
    private QueueSubscription $queueSubscription;
    private ?string $messageId = null;

    private DateTimeImmutable $dateCreated;
    private ?DateTimeImmutable $datePickedUp = null;
    private DateTimeImmutable $dateCompleted;
    #[Filter('json_decode')]
    private array $parameters = [];
    #[Filter('json_decode')]
    private array $status = [];
    private ?float $timeTaken = null;
    private ?string $error = null;
    private int $priority;
    private ?int $ttl = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getQueueSubscription(): QueueSubscription
    {
        return $this->queueSubscription;
    }

    public function setQueueSubscription(QueueSubscription $queueSubscription): void
    {
        $this->queueSubscription = $queueSubscription;
    }

    public function getMessageId(): ?string
    {
        return $this->messageId;
    }

    public function setMessageId(?string $messageId): void
    {
        $this->messageId = $messageId;
    }

    public function getDateCreated(): DateTimeImmutable
    {
        return $this->dateCreated;
    }

    public function setDateCreated(DateTimeImmutable $dateCreated): void
    {
        $this->dateCreated = $dateCreated;
    }

    public function getDatePickedUp(): ?DateTimeImmutable
    {
        return $this->datePickedUp;
    }

    public function setDatePickedUp(?DateTimeImmutable $datePickedUp): void
    {
        $this->datePickedUp = $datePickedUp;
    }

    public function getDateCompleted(): DateTimeImmutable
    {
        return $this->dateCompleted;
    }

    public function setDateCompleted(DateTimeImmutable $dateCompleted): void
    {
        $this->dateCompleted = $dateCompleted;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function getStatus(): array
    {
        return $this->status;
    }

    public function setStatus(array $status): void
    {
        $this->status = $status;
    }

    public function getTimeTaken(): ?float
    {
        return $this->timeTaken;
    }

    public function setTimeTaken(?float $timeTaken): void
    {
        $this->timeTaken = $timeTaken;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $error): void
    {
        $this->error = $error;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    public function setTtl(?int $ttl): void
    {
        $this->ttl = $ttl;
    }
}
