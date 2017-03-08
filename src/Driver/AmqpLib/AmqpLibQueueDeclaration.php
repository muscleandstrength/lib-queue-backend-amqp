<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpLib;

use PhpAmqpLib\Channel\AMQPChannel;

class AmqpLibQueueDeclaration
{
    /**
     * @var AMQPChannel
     */
    private $AMQPChannel;

    public function __construct(AMQPChannel $AMQPChannel)
    {
        $this->AMQPChannel = $AMQPChannel;
    }
    
    public function declareQueue(string $queueName) : int
    {
        $messageCount = $this->AMQPChannel->queue_declare(
            $queueName,
            $passive = false,
            $durable = true,
            $exclusive = false,
            $autoDelete = false,
            $noWait = false
        )[1];
        return $messageCount;
    }
}
