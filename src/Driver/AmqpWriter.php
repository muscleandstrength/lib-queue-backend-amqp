<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver;

interface AmqpWriter
{
    /**
     * @param string $message
     * @return void
     */
    public function addMessage(string $message);
}
