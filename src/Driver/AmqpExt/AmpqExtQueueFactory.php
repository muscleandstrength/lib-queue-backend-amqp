<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt;

class AmpqExtQueueFactory
{
    /**
     * @var \AMQPChannel
     */
    private $AMQPChannel;

    public function __construct(\AMQPChannel $channel)
    {
        $this->AMQPChannel = $channel;
    }

    public function create() : \AMQPQueue
    {
        $AMQPQueue = new \AMQPQueue($this->AMQPChannel);
        $AMQPQueue->setFlags(\AMQP_DURABLE);
        return $AMQPQueue;
    }
}
