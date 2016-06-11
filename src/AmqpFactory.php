<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp;

use LizardsAndPumpkins\Messaging\MessageQueueFactory;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpDriverFactory;
use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\DriverFactoryLocator;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Util\Factory\RegistersDelegateFactory;

/**
 * @method MasterFactory|AmqpDriverFactory getMasterFactory()
 */
class AmqpFactory implements Factory, MessageQueueFactory, RegistersDelegateFactory
{
    use FactoryTrait;

    public function registerDelegateFactories(MasterFactory $masterFactory)
    {
        $masterFactory->register($this->createDriverFactoryLocator()->getDriverFactory());
    }

    /**
     * @return DriverFactoryLocator
     */
    public function createDriverFactoryLocator()
    {
        return new DriverFactoryLocator();
    }

    /**
     * @param string $name
     * @return AmqpQueue
     */
    public function createAmqpQueue($name)
    {
        return new AmqpQueue(
            $this->getMasterFactory()->createAmqpReader($name),
            $this->getMasterFactory()->createAmqpWriter($name)
        );
    }

    /**
     * @return Queue
     */
    public function createEventMessageQueue()
    {
        return $this->createAmqpQueue($this->getDomainEventQueueNameConfig());
    }

    /**
     * @return Queue
     */
    public function createCommandMessageQueue()
    {
        return $this->createAmqpQueue($this->getCommandQueueNameConfig());
    }

    /**
     * @return Driver\AmqpConfig
     */
    public function createAmqpConfig()
    {
        return new Driver\AmqpConfig($this->getMasterFactory()->createConfigReader());
    }

    /**
     * @return string
     */
    private function getDomainEventQueueNameConfig()
    {
        return $this->getMasterFactory()->createAmqpConfig()->getDomainEventQueueName();
    }

    /**
     * @return string
     */
    private function getCommandQueueNameConfig()
    {
        return $this->getMasterFactory()->createAmqpConfig()->getCommandQueueName();
    }
}
