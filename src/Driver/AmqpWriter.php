<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver;

interface AmqpWriter
{
    /**
     * @param string $message
     * @return void
     */
    public function addMessage($message);
}
