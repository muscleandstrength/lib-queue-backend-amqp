<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp;

use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;

class IntegrationTestFactory implements Factory
{
    use FactoryTrait;

    /**
     * @return AmqpConfig
     */
    public function createAmqpConfig()
    {
        return new IntegrationTestAmqpConfig();
    }
}
