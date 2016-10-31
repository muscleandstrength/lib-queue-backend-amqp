<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver;

use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt\AmqpExtFactory;
use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpLib\AmqpLibFactory;

class DriverFactoryLocator
{
    public function getDriverFactory() : AmqpDriverFactory
    {
        return extension_loaded('amqp') ?
            new AmqpExtFactory() :
            new AmqpLibFactory();
    }
}
