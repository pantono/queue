<?php

namespace Pantono\Queue\Factory;

use Pantono\Contracts\Locator\FactoryInterface;
use Nyholm\Dsn\DsnParser;
use Enqueue\AmqpLib\AmqpConnectionFactory;
use Enqueue\Fs\FsConnectionFactory;
use Interop\Queue\ConnectionFactory;

class DsnQueueFactory implements FactoryInterface
{
    private string $dsn;

    public function __construct(string $dsn)
    {
        $this->dsn = $dsn;
    }

    public function createInstance(): ConnectionFactory
    {
        $dsn = DsnParser::parse($this->dsn);
        $scheme = $dsn->getScheme();
        if ($scheme === 'rabbitmq') {
            return new AmqpConnectionFactory($this->dsn);
        }
        if ($scheme === 'file') {
            return new FsConnectionFactory($this->dsn);
        }
        throw new \RuntimeException('Queue scheme not supported');
    }
}