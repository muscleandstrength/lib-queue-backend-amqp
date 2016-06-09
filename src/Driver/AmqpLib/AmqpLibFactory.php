<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpLib;

use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpConfig;
use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpDriverFactory;
use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpReader;
use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpWriter;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * @method AmqpLibFactory|MasterFactory getMasterFactory()
 */
class AmqpLibFactory implements Factory, AmqpDriverFactory
{
    use FactoryTrait;

    /**
     * @var AbstractConnection
     */
    private $connection;

    /**
     * @param string $exchangeName
     * @return AmqpReader
     */
    public function createAmqpReader($exchangeName)
    {
        $AMQPChannel = $this->createAMQPChannelWithExchange($exchangeName);
        $amqpLibQueueDeclaration = $this->createAmqpLibQueueDeclaration($AMQPChannel);
        $queueName = $exchangeName . '-queue';
        $amqpLibQueueDeclaration->declareQueue($queueName);
        $AMQPChannel->queue_bind($queueName, $exchangeName);
        return new AmqpLibReader($AMQPChannel, $queueName, $amqpLibQueueDeclaration);
    }

    /**
     * @param AMQPChannel $AMQPChannel
     * @return AmqpLibQueueDeclaration
     */
    private function createAmqpLibQueueDeclaration(AMQPChannel $AMQPChannel)
    {
        return new AmqpLibQueueDeclaration($AMQPChannel);
    }

    /**
     * @param string $exchangeName
     * @return AmqpWriter
     */
    public function createAmqpWriter($exchangeName)
    {
        return new AmqpLibWriter($this->createAMQPChannelWithExchange($exchangeName), $exchangeName);
    }

    /**
     * @param string $exchangeName
     * @return AMQPChannel
     */
    private function createAMQPChannelWithExchange($exchangeName)
    {
        $connection = $this->getAMQPConnection();
        $channel = $connection->channel();
        $channel->exchange_declare(
            $exchange = $exchangeName,
            $type = 'direct',
            $passive = false,
            $durable = true,
            $autoDelete = false
        );
        $channel->basic_qos($prefetchSite = null, $prefetchCount = 1, $aGlobal = null);
        return $channel;
    }

    /**
     * @return AbstractConnection|AMQPStreamConnection
     */
    public function getAMQPConnection()
    {
        if (!isset($this->connection)) {
            $this->connection = new AMQPStreamConnection(
                $this->getAmqpHostConfig(),
                $this->getAmqpPortConfig(),
                $this->getAmqpUsernameConfig(),
                $this->getAmqpPasswordConfig(),
                $this->getAmqpVhostConfig()
            );
        }
        return $this->connection;
    }

    /**
     * @return AmqpConfig
     */
    public function createAmqpConfig()
    {
        return new AmqpConfig($this->getMasterFactory()->createConfigReader());
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
