<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt;

use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpDriverFactory;
use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpReader;
use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpWriter;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

/**
 * @method AmqpExtFactory|MasterFactory getMasterFactory()
 */
class AmqpExtFactory implements Factory, AmqpDriverFactory
{
    use FactoryTrait;

    /**
     * @var \AMQPConnection
     */
    private $connection;

    /**
     * @var \AMQPChannel
     */
    private $channel;
    
    public function createAmqpReader(string $exchangeName) : AmqpReader
    {
        $channel = $this->getAMQPChannel();
        $AMQPQueueFactory = $this->createAMQPQueueFactory($channel);
        return new AmqpExtReader($this->createAmqpQueue($exchangeName, $AMQPQueueFactory));
    }

    public function createAmqpWriter(string $exchangeName) : AmqpWriter
    {
        return new AmqpExtWriter($this->createExchange($exchangeName));
    }

    private function createAMQPQueueFactory(\AMQPChannel $channel) : AmpqExtQueueFactory
    {
        return new AmpqExtQueueFactory($channel);
    }

    private function getAMQPChannel() : \AMQPChannel
    {
        if (! isset($this->channel)) {
            $this->channel = $this->createAMQPChannel();
        }
        return $this->channel;
    }

    private function createAMQPChannel() : \AMQPChannel
    {
        $AMQPChannel = new \AMQPChannel($this->getAMQPConnection());
        $AMQPChannel->setPrefetchCount(1);
        return $AMQPChannel;
    }

    private function createExchange(string $exchangeName) : \AMQPExchange
    {
        $AMQPExchange = new \AMQPExchange($this->getAMQPChannel());
        $AMQPExchange->setName($exchangeName);
        $AMQPExchange->setType(\AMQP_EX_TYPE_DIRECT);
        $AMQPExchange->setFlags(\AMQP_DURABLE);
        $AMQPExchange->declareExchange();
        return $AMQPExchange;
    }

    private function createAmqpQueue(string $exchangeName, AmpqExtQueueFactory $queueFactory) : \AMQPQueue
    {
        $AMQPQueue = $queueFactory->create();
        $AMQPQueue->setName($exchangeName . '-queue');
        $AMQPQueue->setFlags(\AMQP_DURABLE);
        $AMQPQueue->declareQueue();
        $AMQPQueue->bind($this->createExchange($exchangeName)->getName());
        return $AMQPQueue;
    }

    public function getAMQPConnection() : \AMQPConnection
    {
        if (!isset($this->connection)) {
            $this->connection = $this->getMasterFactory()->createAMQPConnection();
            $this->connection->connect();
        }
        return $this->connection;
    }

    public function createAMQPConnection() : \AMQPConnection
    {
        $AMQPConnection = new \AMQPConnection([
            'host'     => $this->getAmqpHostConfig(),
            'port'     => $this->getAmqpPortConfig(),
            'login'    => $this->getAmqpUsernameConfig(),
            'password' => $this->getAmqpPasswordConfig(),
            'vhost'    => $this->getAmqpVhostConfig(),
        ]);
        return $AMQPConnection;
    }

    private function getAmqpHostConfig() : string
    {
        return $this->getMasterFactory()->createAmqpConfig()->getAmqpHost();
    }

    private function getAmqpPortConfig() : string
    {
        return $this->getMasterFactory()->createAmqpConfig()->getAmqpPort();
    }

    private function getAmqpUsernameConfig() : string
    {
        return $this->getMasterFactory()->createAmqpConfig()->getAmqpUsername();
    }

    private function getAmqpPasswordConfig() : string
    {
        return $this->getMasterFactory()->createAmqpConfig()->getAmqpPassword();
    }

    private function getAmqpVhostConfig() : string
    {
        return $this->getMasterFactory()->createAmqpConfig()->getAmqpVhost();
    }
}
