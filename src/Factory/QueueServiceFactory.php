<?php

namespace Pantono\Queue\Factory;

use Interop\Queue\ConnectionFactory;
use Pantono\Contracts\Locator\LocatorInterface;
use Pantono\Contracts\Locator\FactoryInterface;

class QueueServiceFactory implements FactoryInterface
{
    private LocatorInterface $locator;
    private string $queueService;

    public function __construct(LocatorInterface $locator, string $queueService)
    {
        $this->locator = $locator;
        $this->queueService = $queueService;
    }

    public function createInstance(): ConnectionFactory
    {
        $service = $this->locator->loadDependency($this->queueService);
        if (!$service instanceof ConnectionFactory) {
            throw new \RuntimeException('Queue service ' . $this->queueService . ' does not implement ' . ConnectionFactory::class);
        }
        return $service;
    }
}
