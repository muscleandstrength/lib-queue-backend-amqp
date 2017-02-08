<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver;

use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt\AmqpExtFactory;
use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpLib\AmqpLibFactory;
use LizardsAndPumpkins\Messaging\Queue\Amqp\IntegrationTestFactory;
use LizardsAndPumpkins\Messaging\Queue\Amqp\IntegrationTestMasterFactory;
use PHPUnit\Framework\TestCase;

class CrossDriverTest extends TestCase
{
    private $exchangeName = 'test';

    /**
     * @param AmqpDriverFactory $driverFactory
     * @return IntegrationTestMasterFactory|AmqpDriverFactory
     */
    private function createMasterFactoryWithGivenDriver(AmqpDriverFactory $driverFactory)
    {
        $masterFactory = new IntegrationTestMasterFactory();
        $masterFactory->register($driverFactory);
        $masterFactory->register(new IntegrationTestFactory());
        return $masterFactory;
    }

    /**
     * @return AmqpDriverFactory|IntegrationTestMasterFactory
     */
    private function createAmqpLibMasterFactory()
    {
        return $this->createMasterFactoryWithGivenDriver(new AmqpLibFactory());
    }

    /**
     * @return AmqpDriverFactory|IntegrationTestMasterFactory
     */
    private function createAmqpExtMasterFactory()
    {
        return $this->createMasterFactoryWithGivenDriver(new AmqpExtFactory());
    }

    private function assertNextMessageSame(string $expected, AmqpReader $reader)
    {
        $reader->consume(function ($message) use ($expected) {
            $this->assertSame($expected, $message);
            return AmqpReader::CONSUMER_CANCEL;
        });
    }

    /**
     * @param AmqpReader $reader
     * @param int $numberOfMessagesToRead
     * @return string[]
     */
    private function readNumberOfMessages(AmqpReader $reader, int $numberOfMessagesToRead) : array
    {
        $receivedMessages = [];
        $reader->consume(function ($message) use (&$receivedMessages, $numberOfMessagesToRead) {
            $receivedMessages[] = $message;
            return count($receivedMessages) === $numberOfMessagesToRead ?
                AmqpReader::CONSUMER_CANCEL :
                AmqpReader::CONSUMER_CONTINUE;
        });
        return $receivedMessages;
    }

    private function writeWithOneReadWithTwo(AmqpWriter $writer, AmqpReader $reader1, AmqpReader $reader2)
    {
        $messagesToAdd = ['foo', 'bar', 'baz', 'qux'];
        array_map(function ($messageToAdd) use ($writer) {
            $writer->addMessage($messageToAdd);
        }, $messagesToAdd);

        $readMessages = array_merge(
            $this->readNumberOfMessages($reader1, 2),
            $this->readNumberOfMessages($reader2, 2)
        );

        array_map(function ($messageToAssert) use ($readMessages) {
            $this->assertContains($messageToAssert, $readMessages);
        }, $messagesToAdd);
    }

    protected function setUp()
    {
        try {
            $amqpExtFactory = $this->createAmqpExtMasterFactory();
            $amqpExtFactory->createAmqpReader($this->exchangeName)->purgeQueue();
        } catch (\Exception $exception) {
            $this->markTestSkipped(sprintf("Unable to connect to RabbitMQ: %s", $exception->getMessage()));
        }
    }
    
    protected function tearDown()
    {
        $factory = $this->createAmqpLibMasterFactory();
        $reader = $factory->createAmqpReader($this->exchangeName);
        $reader->deleteQueue();
    }

    public function testWriteWithLibReadWithExt()
    {
        $amqpLibFactory = $this->createAmqpLibMasterFactory();
        $amqpExtFactory = $this->createAmqpExtMasterFactory();

        $libWriter = $amqpLibFactory->createAmqpWriter($this->exchangeName);
        $extReader = $amqpExtFactory->createAmqpReader($this->exchangeName);

        $libWriter->addMessage('foo');
        $this->assertNextMessageSame('foo', $extReader);
    }

    public function testWriteWithExrReadWithLib()
    {
        $amqpLibFactory = $this->createAmqpLibMasterFactory();
        $amqpExtFactory = $this->createAmqpExtMasterFactory();

        $extWriter = $amqpExtFactory->createAmqpWriter($this->exchangeName);
        $libReader = $amqpLibFactory->createAmqpReader($this->exchangeName);

        $extWriter->addMessage('bar');
        $this->assertNextMessageSame('bar', $libReader);
    }

    public function testWriteWithAmqlLibReadWithBoth()
    {
        $amqpLibFactory = $this->createAmqpLibMasterFactory();
        $amqpExtFactory = $this->createAmqpExtMasterFactory();

        $this->writeWithOneReadWithTwo(
            $amqpLibFactory->createAmqpWriter($this->exchangeName),
            $amqpLibFactory->createAmqpReader($this->exchangeName),
            $amqpExtFactory->createAmqpReader($this->exchangeName)
        );
    }

    public function testWriteWithAmqlExtReadWithBoth()
    {
        $amqpLibFactory = $this->createAmqpLibMasterFactory();
        $amqpExtFactory = $this->createAmqpExtMasterFactory();

        $this->writeWithOneReadWithTwo(
            $amqpExtFactory->createAmqpWriter($this->exchangeName),
            $amqpLibFactory->createAmqpReader($this->exchangeName),
            $amqpExtFactory->createAmqpReader($this->exchangeName)
        );
    }
}
