<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp;

use LizardsAndPumpkins\Messaging\MessageQueueFactory;
use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpDriverFactory;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryWithCallback;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\Amqp\AmqpFactory
 */
class AmqpFactoryTest extends TestCase
{
    /**
     * @var AmqpFactory
     */
    private $amqpFactory;

    protected function setUp()
    {
        $this->amqpFactory = new AmqpFactory();
        
        $masterFactory = new CatalogMasterFactory();
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

    public function testRegistersDriverFactoryOnMasterFactory()
    {
        /** @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject $mockMasterFactory */
        $mockMasterFactory = $this->createMock(MasterFactory::class);
        $mockMasterFactory->expects($this->once())->method('register')
            ->with($this->isInstanceOf(AmqpDriverFactory::class));

        $this->amqpFactory->factoryRegistrationCallback($mockMasterFactory);
    }
}
