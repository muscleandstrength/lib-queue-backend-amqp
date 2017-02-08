<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt\AmpqExtQueueFactory
 */
class AmpqExtQueueFactoryTest extends TestCase
{
    public function testReturnsAnAMQPQueueInstance()
    {
        if (!extension_loaded('amqp')) {
            $this->markTestSkipped('PHP extension amqp not found');
        }

        $testConnection = new \AMQPConnection();
        $testConnection->connect();
        $queueFactory = new AmpqExtQueueFactory(new \AMQPChannel($testConnection));
        
        $this->assertInstanceOf(\AMQPQueue::class, $queueFactory->create());
    }
}
