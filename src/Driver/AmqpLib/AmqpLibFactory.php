<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpLib;

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

    public function createAmqpReader(string $exchangeName) : AmqpReader
    {
        $AMQPChannel = $this->createAMQPChannelWithExchange($exchangeName);
        $amqpLibQueueDeclaration = $this->createAmqpLibQueueDeclaration($AMQPChannel);
        $queueName = $exchangeName . '-queue';
        $amqpLibQueueDeclaration->declareQueue($queueName);
        $AMQPChannel->queue_bind($queueName, $exchangeName);
        return new AmqpLibReader($AMQPChannel, $queueName, $amqpLibQueueDeclaration);
    }

    private function createAmqpLibQueueDeclaration(AMQPChannel $AMQPChannel) : AmqpLibQueueDeclaration
    {
        return new AmqpLibQueueDeclaration($AMQPChannel);
    }

    public function createAmqpWriter(string $exchangeName) : AmqpWriter
    {
        return new AmqpLibWriter($this->createAMQPChannelWithExchange($exchangeName), $exchangeName);
    }

    private function createAMQPChannelWithExchange(string $exchangeName) : AMQPChannel
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
