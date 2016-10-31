<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp;

use LizardsAndPumpkins\Messaging\MessageReceiver;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpReader;
use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpWriter;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Util\Storage\Clearable;

class AmqpQueue implements Queue, Clearable
{
    /**
     * @var AmqpReader
     */
    private $amqpReader;

    /**
     * @var AmqpWriter
     */
    private $amqpWriter;

    /**
     * @var int
     */
    private $remainingNumberOfMessagesToConsume;

    public function __construct(AmqpReader $amqpReader, AmqpWriter $amqpWriter)
    {
        $this->amqpReader = $amqpReader;
        $this->amqpWriter = $amqpWriter;
    }
    
    public function count() : int
    {
        return $this->amqpReader->countMessages();
    }

    public function add(Message $message)
    {
        $this->amqpWriter->addMessage($message->serialize());
    }

    public function consume(MessageReceiver $messageReceiver, int $maxNumberOfMessagesToConsume)
    {
        $this->remainingNumberOfMessagesToConsume = $maxNumberOfMessagesToConsume;
        $this->amqpReader->consume(function ($rawMessage) use ($messageReceiver) {
            $messageReceiver->receive(Message::rehydrate($rawMessage));
            return 0 < --$this->remainingNumberOfMessagesToConsume ?
                AmqpReader::CONSUMER_CONTINUE :
                AmqpReader::CONSUMER_CANCEL;
        });
    }

    public function clear()
    {
        $this->amqpReader->purgeQueue();
    }
}
