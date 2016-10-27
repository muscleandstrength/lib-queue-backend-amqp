<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp;

use LizardsAndPumpkins\Messaging\MessageQueueFactory;
use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpDriverFactory;
use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\DriverFactoryLocator;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryWithCallback;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\Amqp\AmqpFactory
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\DriverFactoryLocator
 */
class AmqpFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AmqpFactory
     */
    private $amqpFactory;

    protected function setUp()
    {
        $this->amqpFactory = new AmqpFactory();
        
        $masterFactory = new SampleMasterFactory();
        $masterFactory->register($this->amqpFactory);
    }

    public function testImplementsFactory()
    {
        $this->assertInstanceOf(Factory::class, $this->amqpFactory);
    }

    public function testImplementsRegistersDelegateFactory()
    {
        $this->assertInstanceOf(FactoryWithCallback::class, $this->amqpFactory);
    }

    public function testImplementsMessageQueueFactoryInterface()
    {
        $this->assertInstanceOf(MessageQueueFactory::class, $this->amqpFactory);
    }

    public function testReturnsDriverLocatorInstance()
    {
        $this->assertInstanceOf(DriverFactoryLocator::class, $this->amqpFactory->createDriverFactoryLocator());
    }

    public function testRegistersDriverFactoryOnMasterFactory()
    {
        /** @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject $mockMasterFactory */
        $mockMasterFactory = $this->createMock(MasterFactory::class);
        $mockMasterFactory->expects($this->once())->method('register')
            ->with($this->isInstanceOf(AmqpDriverFactory::class));

        $this->amqpFactory->factoryRegistrationCallback($mockMasterFactory);
    }
}
