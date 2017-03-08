<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver;

use LizardsAndPumpkins\Util\Factory\Factory;

interface AmqpDriverFactory extends Factory
{
    public function createAmqpReader(string $exchangeName) : AmqpReader;

    public function createAmqpWriter(string $exchangeName) : AmqpWriter;
}
