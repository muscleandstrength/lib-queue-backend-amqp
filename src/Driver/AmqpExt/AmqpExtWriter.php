<?php

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

    /**
     * @param string $message
     */
    public function addMessage($message)
    {
        $this->AMQPExchange->publish($message, null, \AMQP_MANDATORY, ['delivery_mode' => \AMQP_DURABLE]);
    }
}
