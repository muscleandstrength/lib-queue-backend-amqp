<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver;

use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt\AmqpExtFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class AmqpExtTest extends AmqpDriverTestIntegration
{
    /**
     * @return AmqpExtFactory
     */
    final protected static function createMasterFactoryWithAmqpDriver()
    {
        return self::createTestMasterFactory(new AmqpExtFactory());
    }

    /**
     * @return string
     */
    final protected static function getQueueName()
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
