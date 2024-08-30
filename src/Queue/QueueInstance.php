<?php

namespace Pantono\Queue\Queue;

use Interop\Queue\Context;
use Interop\Queue\Queue;
use Interop\Queue\SubscriptionConsumer;
use Interop\Queue\Topic;
use Interop\Queue\Message;
use Interop\Queue\Consumer;

class QueueInstance
{
    private Context $context;

    private Topic $topic;

    private Queue $queue;
    private ?Consumer $consumer = null;

    private ?SubscriptionConsumer $subscriptionConsumer = null;

    public function __construct(Context $context, Topic $topic, Queue $queue)
    {
        $this->context = $context;
        $this->topic = $topic;
        $this->queue = $queue;
    }

    public function send(array $data): Message
    {
        $data['topic'] = $this->topic->getTopicName();
        $dataStr = json_encode($data, JSON_THROW_ON_ERROR);
        $message = $this->context->createMessage($dataStr);
        $producer = $this->context->createProducer();
        $producer->send($this->queue, $message);

        return $message;
    }

    public function listen(callable $callback): void
    {
        $this->generateConsumers();
        if ($this->consumer === null) {
            throw new \RuntimeException('Consumer not generated');
        }
        $message = $this->consumer->receive();
        $callback($message, $this->consumer);
    }

    private function generateConsumers(): void
    {
        if ($this->consumer === null) {
            $this->consumer = $this->context->createConsumer($this->queue);
        }
    }
}
