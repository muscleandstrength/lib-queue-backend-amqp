<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver;

use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpLib\AmqpLibFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

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

    final protected function closeConnection(MasterFactory $factory)
    {
        /** @var AmqpLibFactory $factory */
        $factory->getAMQPConnection()->close();
    }
}
