<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp;

use LizardsAndPumpkins\Messaging\MessageReceiver;
use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpDriverFactory;
use LizardsAndPumpkins\Messaging\Queue\Message;

class End2EndTest extends \PHPUnit_Framework_TestCase implements MessageReceiver
{
    /**
     * @var Message[]
     */
    private $receivedMessages = [];

    /**
     * @var bool
     */
    private static $skipTearDownAfterClass = false;

    public function receive(Message $message)
    {
        $this->receivedMessages[] = $message;
    }

    /**
     * @return AmqpFactory|AmqpDriverFactory|IntegrationTestMasterFactory
     */
    private static function createFactory()
    {
        $masterFactory = new IntegrationTestMasterFactory();
        $masterFactory->register(new AmqpFactory());
        $masterFactory->register(new IntegrationTestFactory());
        return $masterFactory;
    }

    /**
     * @param string $expectedName
     * @param Message[] $messages
     */
    private function assertArrayContainsMessageWithName($expectedName, array $messages)
    {
        $hasMessage = array_reduce($messages, function ($carry, Message $message) use ($expectedName) {
            return $carry || $message->getName() === $expectedName;
        }, false);
        $this->assertTrue($hasMessage);
    }

    protected function setUp()
    {
        try {
            $factory = self::createFactory();
            $factory->createAmqpReader($factory->createAmqpConfig()->getCommandQueueName());
        } catch (\Exception $exception) {
            self::$skipTearDownAfterClass = true;
            $this->markTestSkipped(sprintf(
                "Unable to connect to RabbitMQ: %s",
                $exception->getMessage()
            ));
        }
    }

    public static function tearDownAfterClass()
    {
        if (! self::$skipTearDownAfterClass) {
            $factory = self::createFactory();
            $reader = $factory->createAmqpReader($factory->createAmqpConfig()->getCommandQueueName());
            $reader->deleteQueue();
        }
    }

    public function testAmqpQueueWithDriverBackend()
    {
        $masterFactory = self::createFactory();
        
        $commandMessageQueue = $masterFactory->createCommandMessageQueue();
        $commandMessageQueue->add(Message::withCurrentTime('foo', ['bar' => 'nvm'], ['moo' => 'baa']));
        $commandMessageQueue->add(Message::withCurrentTime('baz', ['qux' => 'wtf'], ['7x7' => 42]));
        $commandMessageQueue->consume($this, 2);

        $this->assertCount(2, $this->receivedMessages);
        $this->assertArrayContainsMessageWithName('foo', $this->receivedMessages);
        $this->assertArrayContainsMessageWithName('baz', $this->receivedMessages);
    }
}
