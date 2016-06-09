<?php

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

    /**
     * @param AMQPChannel $AMQPChannel
     * @param string $exchangeName
     */
    public function __construct(AMQPChannel $AMQPChannel, $exchangeName)
    {
        $AMQPChannel->exchange_declare(
            $exchange = $exchangeName,
            $type = 'direct',
            $passive = false,
            $durable = true,
            $autoDelete = false
        );
        $this->AMQPChannel = $AMQPChannel;
        $this->exchangeName = $exchangeName;
    }

    /**
     * @param string $message
     */
    public function addMessage($message)
    {
        $msg = new AMQPMessage($message, ['delivery_mode' => $this->persistent]);
        $this->AMQPChannel->basic_publish($msg, $this->exchangeName, $routingKey = '', $mandatory = true);
    }
}
