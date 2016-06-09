<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver;

use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt\AmqpExtFactory;
use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpLib\AmqpLibFactory;

class DriverFactoryLocator
{
    /**
     * @return AmqpDriverFactory
     */
    public function getDriverFactory()
    {
        return extension_loaded('amqp') ?
            new AmqpExtFactory() :
            new AmqpLibFactory();
    }
}
