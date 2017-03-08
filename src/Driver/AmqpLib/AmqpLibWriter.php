<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpLib;

use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpWriter;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpLibWriter implements AmqpWriter
{
    private $persistent = 2;

    /**
     * @var AMQPChannel
     */
    private $AMQPChannel;

    /**
     * @var string
     */
    private $exchangeName;

    public function __construct(AMQPChannel $AMQPChannel, string $exchangeName)
    {
        $this->AMQPChannel = $AMQPChannel;
        $this->exchangeName = $exchangeName;
    }

    public function addMessage(string $message)
    {
        $msg = new AMQPMessage($message, ['delivery_mode' => $this->persistent]);
        $this->AMQPChannel->basic_publish($msg, $this->exchangeName, $routingKey = '', $mandatory = true);
    }
}
