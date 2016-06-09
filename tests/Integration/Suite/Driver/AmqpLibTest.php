<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver;

use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpLib\AmqpLibFactory;

class AmqpLibTest extends AmqpDriverTestIntegration
{
    /**
     * @return AmqpLibFactory
     */
    final protected static function createMasterFactoryWithAmqpDriver()
    {
        return self::createTestMasterFactory(new AmqpLibFactory());
    }

    /**
     * @return string
     */
    final protected static function getQueueName()
    {
        return 'test-amqp-lib';
    }
}
