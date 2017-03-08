<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver;

use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt\AmqpExtFactory;
use LizardsAndPumpkins\Messaging\Queue\Amqp\IntegrationTestMasterFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class AmqpExtTest extends AmqpDriverTestIntegration
{
    /**
     * @return AmqpDriverFactory|IntegrationTestMasterFactory
     */
    final protected static function createMasterFactoryWithAmqpDriver()
    {
        return self::createTestMasterFactory(new AmqpExtFactory());
    }

    final protected static function getQueueName() : string
    {
        return 'test-amqp-ext';
    }

    public function testCanCountMessagesInQueue()
    {
        $this->markTestSkipped('AMQPQueue::declareQueue() message count return value seems not reliable');
    }

    final protected function closeConnection(MasterFactory $factory)
    {
        /** @var AmqpExtFactory $factory */
        $factory->getAMQPConnection()->disconnect();
    }
}
