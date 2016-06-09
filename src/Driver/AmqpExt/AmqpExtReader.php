<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt;

use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpReader;

class AmqpExtReader implements AmqpReader
{
    /**
     * @var \AMQPQueue
     */
    private $AMQPQueue;

    public function __construct(\AMQPQueue $AMQPQueue)
    {
        $this->AMQPQueue = $AMQPQueue;
    }

    /**
     * @return int
     */
    public function countMessages()
    {
        return $this->AMQPQueue->declareQueue();
    }

    /**
     * @param callable $callback
     */
    public function consume(callable $callback)
    {
        $flags = \AMQP_NOPARAM; // | \AMQP_AUTOACK;
        $this->AMQPQueue->consume(function (\AMQPEnvelope $envelope) use ($callback, $flags) {
            $callbackResult = $callback($envelope->getBody());
            if (! ($flags & \AMQP_AUTOACK)) {
                $this->AMQPQueue->ack($envelope->getDeliveryTag());
            }
            return $callbackResult === AmqpReader::CANCEL_CONSUME ?
                false :
                null;
        }, $flags);
        $this->cancel();
    }

    public function cancel()
    {
        $this->AMQPQueue->cancel();
    }

    public function purgeQueue()
    {
        $this->AMQPQueue->purge();
    }

    public function deleteQueue()
    {
        $this->AMQPQueue->delete();
    }
}
