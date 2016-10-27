<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt\AmpqExtQueueFactory
 */
class AmpqExtQueueFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsAnAMQPQueueInstance()
    {
        if (!extension_loaded("amqp")) {
            $this->markTestSkipped('PHP extension amqp not found');
        }

        $testConnection = new \AMQPConnection();
        $testConnection->connect();
        $queueFactory = new AmpqExtQueueFactory(new \AMQPChannel($testConnection));
        
        $this->assertInstanceOf(\AMQPQueue::class, $queueFactory->create());
    }
}
