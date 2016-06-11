<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt;

use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpDriverFactory;
use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpReader;
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
    
    /**
     * @param string $exchangeName
     * @return AmqpReader
     */
    public function createAmqpReader($exchangeName)
    {
        $channel = $this->getAMQPChannel();
        $AMQPQueueFactory = $this->createAMQPQueueFactory($channel);
        return new AmqpExtReader($this->createAmqpQueue($exchangeName, $AMQPQueueFactory));
    }

    /**
     * @param string $exchangeName
     * @return AmqpExtWriter
     */
    public function createAmqpWriter($exchangeName)
    {
        return new AmqpExtWriter($this->createExchange($exchangeName));
    }

    /**
     * @param \AMQPChannel $channel
     * @return AmpqExtQueueFactory
     */
    private function createAMQPQueueFactory(\AMQPChannel $channel)
    {
        return new AmpqExtQueueFactory($channel);
    }

    /**
     * @return \AMQPChannel
     */
    private function getAMQPChannel()
    {
        if (! isset($this->channel)) {
            $this->channel = $this->createAMQPChannel();
        }
        return $this->channel;
    }

    /**
     * @return \AMQPChannel
     */
    private function createAMQPChannel()
    {
        $AMQPChannel = new \AMQPChannel($this->getAMQPConnection());
        $AMQPChannel->setPrefetchCount(1);
        return $AMQPChannel;
    }

    /**
     * @param string $exchangeName
     * @return \AMQPExchange
     */
    private function createExchange($exchangeName)
    {
        $AMQPExchange = new \AMQPExchange($this->getAMQPChannel());
        $AMQPExchange->setName($exchangeName);
        $AMQPExchange->setType(\AMQP_EX_TYPE_DIRECT);
        $AMQPExchange->setFlags(\AMQP_DURABLE);
        $AMQPExchange->declareExchange();
        return $AMQPExchange;
    }

    /**
     * @param string $exchangeName
     * @param AmpqExtQueueFactory $queueFactory
     * @return \AMQPQueue
     */
    private function createAmqpQueue($exchangeName, AmpqExtQueueFactory $queueFactory)
    {
        $AMQPQueue = $queueFactory->create();
        $AMQPQueue->setName($exchangeName . '-queue');
        $AMQPQueue->setFlags(\AMQP_DURABLE);
        $AMQPQueue->declareQueue();
        $AMQPQueue->bind($this->createExchange($exchangeName)->getName());
        return $AMQPQueue;
    }

    /**
     * @return \AMQPConnection
     */
    public function getAMQPConnection()
    {
        if (!isset($this->connection)) {
            $this->connection = $this->getMasterFactory()->createAMQPConnection();
            $this->connection->connect();
        }
        return $this->connection;
    }

    /**
     * @return \AMQPConnection
     */
    public function createAMQPConnection()
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

    /**
     * @return string
     */
    private function getAmqpHostConfig()
    {
        return $this->getMasterFactory()->createAmqpConfig()->getAmqpHost();
    }

    /**
     * @return string
     */
    private function getAmqpPortConfig()
    {
        return $this->getMasterFactory()->createAmqpConfig()->getAmqpPort();
    }

    /**
     * @return string
     */
    private function getAmqpUsernameConfig()
    {
        return $this->getMasterFactory()->createAmqpConfig()->getAmqpUsername();
    }

    /**
     * @return string
     */
    private function getAmqpPasswordConfig()
    {
        return $this->getMasterFactory()->createAmqpConfig()->getAmqpPassword();
    }

    /**
     * @return string
     */
    private function getAmqpVhostConfig()
    {
        return $this->getMasterFactory()->createAmqpConfig()->getAmqpVhost();
    }
}
