<?php

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

    /**
     * @param AMQPChannel $AMQPChannel
     * @param string $queueName
     * @param AmqpLibQueueDeclaration $amqpLibQueueDeclaration
     */
    public function __construct(AMQPChannel $AMQPChannel, $queueName, AmqpLibQueueDeclaration $amqpLibQueueDeclaration)
    {
        $this->queueName = $queueName;
        $this->AMQPChannel = $AMQPChannel;
        $this->amqpLibQueueDeclaration = $amqpLibQueueDeclaration;
    }

    /**
     * @return int
     */
    public function countMessages()
    {
        return $this->amqpLibQueueDeclaration->declare($this->queueName);
    }

    /**
     * @param callable $callback
     */
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
                if ($result === AmqpReader::CANCEL_CONSUME) {
                    $this->cancel();
                }
                if ($noAck === false) {
                    $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
                }
            }
        );
        do {
            $this->AMQPChannel->wait();
        }
        while (count($this->AMQPChannel->callbacks));
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
