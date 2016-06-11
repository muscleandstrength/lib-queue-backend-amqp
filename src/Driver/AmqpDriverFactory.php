<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver;

use LizardsAndPumpkins\Util\Factory\Factory;

interface AmqpDriverFactory extends Factory
{
    /**
     * @param string $exchangeName
     * @return AmqpReader
     */
    public function createAmqpReader($exchangeName);

    /**
     * @param string $exchangeName
     * @return AmqpWriter
     */
    public function createAmqpWriter($exchangeName);
}
