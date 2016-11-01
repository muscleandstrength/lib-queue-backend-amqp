<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp;

use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;

class IntegrationTestFactory implements Factory
{
    use FactoryTrait;

    public function createAmqpConfig() : AmqpConfig
    {
        return new IntegrationTestAmqpConfig();
    }
}
