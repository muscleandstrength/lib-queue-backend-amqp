<?php

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

    /**
     * @return \AMQPQueue
     */
    public function create()
    {
        $AMQPQueue = new \AMQPQueue($this->AMQPChannel);
        $AMQPQueue->setFlags(\AMQP_DURABLE);
        return $AMQPQueue;
    }
}