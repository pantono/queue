<?php

namespace Pantono\Queue\Filter;

use Pantono\Queue\Model\QueueSubscription;
use Pantono\Contracts\Filter\PageableInterface;
use Pantono\Database\Traits\Pageable;

class QueueTaskFilter implements PageableInterface
{
    use Pageable;

    private ?\DateTimeInterface $dateCreatedStart = null;
    private ?\DateTimeInterface $dateCreatedEnd = null;
    private ?\DateTimeInterface $datePickedUpStart = null;
    private ?\DateTimeInterface $datePickedUpEnd = null;
    private ?\DateTimeInterface $dateCompletedStart = null;
    private ?\DateTimeInterface $dateCompletedEnd = null;
    private ?QueueSubscription $subscription = null;
    private ?string $taskName = null;
    private ?bool $completed = null;
    private ?bool $pickedUp = null;

    public function getDateCreatedStart(): ?\DateTimeInterface
    {
        return $this->dateCreatedStart;
    }

    public function setDateCreatedStart(?\DateTimeInterface $dateCreatedStart): void
    {
        $this->dateCreatedStart = $dateCreatedStart;
    }

    public function getDateCreatedEnd(): ?\DateTimeInterface
    {
        return $this->dateCreatedEnd;
    }

    public function setDateCreatedEnd(?\DateTimeInterface $dateCreatedEnd): void
    {
        $this->dateCreatedEnd = $dateCreatedEnd;
    }

    public function getDatePickedUpStart(): ?\DateTimeInterface
    {
        return $this->datePickedUpStart;
    }

    public function setDatePickedUpStart(?\DateTimeInterface $datePickedUpStart): void
    {
        $this->datePickedUpStart = $datePickedUpStart;
    }

    public function getDatePickedUpEnd(): ?\DateTimeInterface
    {
        return $this->datePickedUpEnd;
    }

    public function setDatePickedUpEnd(?\DateTimeInterface $datePickedUpEnd): void
    {
        $this->datePickedUpEnd = $datePickedUpEnd;
    }

    public function getDateCompletedStart(): ?\DateTimeInterface
    {
        return $this->dateCompletedStart;
    }

    public function setDateCompletedStart(?\DateTimeInterface $dateCompletedStart): void
    {
        $this->dateCompletedStart = $dateCompletedStart;
    }

    public function getDateCompletedEnd(): ?\DateTimeInterface
    {
        return $this->dateCompletedEnd;
    }

    public function setDateCompletedEnd(?\DateTimeInterface $dateCompletedEnd): void
    {
        $this->dateCompletedEnd = $dateCompletedEnd;
    }

    public function getSubscription(): ?QueueSubscription
    {
        return $this->subscription;
    }

    public function setSubscription(?QueueSubscription $subscription): void
    {
        $this->subscription = $subscription;
    }

    public function getTaskName(): ?string
    {
        return $this->taskName;
    }

    public function setTaskName(?string $taskName): void
    {
        $this->taskName = $taskName;
    }

    public function getCompleted(): ?bool
    {
        return $this->completed;
    }

    public function setCompleted(?bool $completed): void
    {
        $this->completed = $completed;
    }

    public function getPickedUp(): ?bool
    {
        return $this->pickedUp;
    }

    public function setPickedUp(?bool $pickedUp): void
    {
        $this->pickedUp = $pickedUp;
    }
}
