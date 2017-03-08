<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpLib;

use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpReader;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpLibReader implements AmqpReader
{
    /**
     * @var AMQPChannel
     */
    private $AMQPChannel;

    /**
     * @var string
     */
    private $queueName;

    /**
     * @var AmqpLibQueueDeclaration
     */
    private $amqpLibQueueDeclaration;

    /**
     * @var string
     */
    private $consumerTag;

    public function __construct(
        AMQPChannel $AMQPChannel,
        string $queueName,
        AmqpLibQueueDeclaration $amqpLibQueueDeclaration
    ) {
        $this->queueName = $queueName;
        $this->AMQPChannel = $AMQPChannel;
        $this->amqpLibQueueDeclaration = $amqpLibQueueDeclaration;
    }

    public function countMessages() : int
    {
        return $this->amqpLibQueueDeclaration->declareQueue($this->queueName);
    }

    public function consume(callable $callback)
    {
        $this->consumerTag = $this->AMQPChannel->basic_consume(
            $this->queueName,
            $consumerTag = '',
            $noLocal = false,
            $noAck = false,
            $exclusive = false,
            $noWait = false,
            function (AMQPMessage $message) use ($noAck, $callback) {
                $result = $callback($message->body);
                if ($result === AmqpReader::CONSUMER_CANCEL) {
                    $this->cancel();
                }
                if ($noAck === false) {
                    $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
                }
            }
        );
        do {
            $this->AMQPChannel->wait();
        } while (count($this->AMQPChannel->callbacks));
    }

    public function cancel()
    {
        $this->AMQPChannel->basic_cancel($this->consumerTag);
    }

    public function purgeQueue()
    {
        $this->AMQPChannel->queue_purge($this->queueName);
    }

    public function deleteQueue()
    {
        $this->AMQPChannel->queue_delete($this->queueName);
    }
}
