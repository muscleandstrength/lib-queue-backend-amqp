<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt;

use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpWriter;

class AmqpExtWriter implements AmqpWriter
{
    /**
     * @var \AMQPExchange
     */
    private $AMQPExchange;

    public function __construct(\AMQPExchange $AMQPExchange)
    {
        $this->AMQPExchange = $AMQPExchange;
    }

    public function addMessage(string $message)
    {
        $this->AMQPExchange->publish($message, '', \AMQP_MANDATORY, ['delivery_mode' => \AMQP_DURABLE]);
    }
}
