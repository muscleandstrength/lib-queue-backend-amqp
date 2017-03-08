<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp;

use LizardsAndPumpkins\Messaging\MessageQueueFactory;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\DriverFactoryLocator;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;
use LizardsAndPumpkins\Util\Factory\FactoryWithCallback;
use LizardsAndPumpkins\Util\Factory\FactoryWithCallbackTrait;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class AmqpFactory implements MessageQueueFactory, FactoryWithCallback
{
    use FactoryWithCallbackTrait;

    public function factoryRegistrationCallback(MasterFactory $masterFactory)
    {
        $masterFactory->register($this->createDriverFactoryLocator()->getDriverFactory());
    }

    public function createDriverFactoryLocator() : DriverFactoryLocator
    {
        return new DriverFactoryLocator();
    }

    public function createAmqpQueue(string $name) : AmqpQueue
    {
        return new AmqpQueue(
            $this->getMasterFactory()->createAmqpReader($name),
            $this->getMasterFactory()->createAmqpWriter($name)
        );
    }

    public function createEventMessageQueue() : Queue
    {
        return $this->createAmqpQueue($this->getDomainEventQueueNameConfig());
    }

    public function createCommandMessageQueue() : Queue
    {
        return $this->createAmqpQueue($this->getCommandQueueNameConfig());
    }

    public function createAmqpConfig() : AmqpConfig
    {
        return new Driver\AmqpConfig($this->getMasterFactory()->createConfigReader());
    }

    private function getDomainEventQueueNameConfig() : string
    {
        return $this->getMasterFactory()->createAmqpConfig()->getDomainEventQueueName();
    }

    private function getCommandQueueNameConfig() : string
    {
        return $this->getMasterFactory()->createAmqpConfig()->getCommandQueueName();
    }
}
