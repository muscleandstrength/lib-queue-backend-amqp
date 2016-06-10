<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp;

use LizardsAndPumpkins\Messaging\MessageReceiver;
use LizardsAndPumpkins\Messaging\Queue\Message;

class End2EndTest extends \PHPUnit_Framework_TestCase implements MessageReceiver
{
    /**
     * @var Message[]
     */
    protected $receivedMessages = [];

    public function receive(Message $message)
    {
        $this->receivedMessages[] = $message;
    }

    /**
     * @param string $expectedName
     * @param Message[] $messages
     */
    private function assertArrayContainsMessageWithName($expectedName, array $messages)
    {
        $hasMessage = array_reduce($this->receivedMessages, function ($carry, Message $message) use ($expectedName) {
            return $carry || $message->getName() === $expectedName;
        }, false);
        $this->assertTrue($hasMessage);
    }
    
    public function testAmqpQueueWithDriverBackend()
    {
        /** @var IntegrationTestMasterFactory|AmqpFactory $masterFactory */
        $masterFactory = new IntegrationTestMasterFactory();
        $masterFactory->register(new AmqpFactory());
        $masterFactory->register(new IntegrationTestFactory());
        
        $commandMessageQueue = $masterFactory->createCommandMessageQueue();
        $commandMessageQueue->add(Message::withCurrentTime('foo', ['bar' => 'nvm'], ['moo' => 'baa']));
        $commandMessageQueue->add(Message::withCurrentTime('baz', ['qux' => 'wtf'], ['7x7' => 42]));
        $commandMessageQueue->consume($this, 2);

        $this->assertCount(2, $this->receivedMessages);
        $this->assertArrayContainsMessageWithName('foo', $this->receivedMessages);
        $this->assertArrayContainsMessageWithName('baz', $this->receivedMessages);
    }
}
