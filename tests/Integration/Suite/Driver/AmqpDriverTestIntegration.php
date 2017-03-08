<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver;

use LizardsAndPumpkins\Messaging\Queue\Amqp\IntegrationTestFactory;
use LizardsAndPumpkins\Messaging\Queue\Amqp\IntegrationTestMasterFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use PHPUnit\Framework\TestCase;

abstract class AmqpDriverTestIntegration extends TestCase
{
    /**
     * @var AmqpDriverFactory|IntegrationTestMasterFactory
     */
    private $masterFactory;

    /**
     * @var bool
     */
    private static $skipTearDownAfterClass = false;

    /**
     * @param MasterFactory $masterFactory
     * @return void
     */
    abstract protected function closeConnection(MasterFactory $masterFactory);

    abstract protected static function createMasterFactoryWithAmqpDriver();

    abstract protected static function getQueueName() : string;

    /**
     * @param AmqpDriverFactory $driverFactory
     * @return IntegrationTestMasterFactory|AmqpDriverFactory
     */
    final protected static function createTestMasterFactory(AmqpDriverFactory $driverFactory)
    {
        $masterFactory = new IntegrationTestMasterFactory();
        $masterFactory->register($driverFactory);
        $masterFactory->register(new IntegrationTestFactory());
        return $masterFactory;
    }

    private function assertNextConsumedMessageSame(string $expected, AmqpReader $reader)
    {
        $reader->consume(function ($messageBody) use ($expected) {
            $this->assertSame($expected, $messageBody);
            return AmqpReader::CONSUMER_CANCEL;
        });
    }

    /**
     * @return AmqpDriverFactory|IntegrationTestMasterFactory
     */
    final protected function getMasterFactoryInstance()
    {
        return $this->masterFactory;
    }

    protected function setUp()
    {
        try {
            $this->masterFactory = static::createMasterFactoryWithAmqpDriver();
            $reader = $this->masterFactory->createAmqpReader(static::getQueueName());
            $reader->purgeQueue();
        } catch (\Exception $exception) {
            self::$skipTearDownAfterClass = true;
            $this->markTestSkipped(sprintf("Unable to connect to RabbitMQ: %s", $exception->getMessage()));
        }
    }

    protected function tearDown()
    {
        $reader = $this->masterFactory->createAmqpReader(static::getQueueName());
        $reader->purgeQueue();
    }

    public static function tearDownAfterClass()
    {
        if (! self::$skipTearDownAfterClass) {
            $reader = static::createMasterFactoryWithAmqpDriver()->createAmqpReader(static::getQueueName());
            $reader->deleteQueue();
        }
    }

    public function testCanCountMessagesInQueue()
    {
        $reader = $this->masterFactory->createAmqpReader(static::getQueueName());
        $writer = $this->masterFactory->createAmqpWriter(static::getQueueName());

        $this->assertEquals(0, $reader->countMessages(), 'Failed asserting fresh queue starts empty');
        $writer->addMessage('foo');
        usleep(5000);
        $this->assertEquals(1, $reader->countMessages(), 'Failed asserting adding 1 message brings count to 1');

        $writer->addMessage('bar');
        usleep(5000);
        $this->assertEquals(2, $reader->countMessages(), 'Failed asserting adding 2 messages brings count to 2');

        $reader->consume(function () {
            return AmqpReader::CONSUMER_CANCEL;
        });
        usleep(5000);
        $this->assertEquals(1, $reader->countMessages(), 'Failed asserting consuming 1 of 2 brings count to 1');

        $reader->consume(function () {
            return AmqpReader::CONSUMER_CANCEL;
        });
        usleep(5000);
        $this->assertEquals(0, $reader->countMessages(), 'Failed asserting consuming 1 of 1 brings count to 0');
    }

    public function testCanSendAndReceiveMessages()
    {
        $reader = $this->masterFactory->createAmqpReader(static::getQueueName());
        $writer = $this->masterFactory->createAmqpWriter(static::getQueueName());

        $writer->addMessage('foo bar buz');
        $transport = null;
        $reader->consume(function ($messageBody) use (&$transport) {
            $transport = $messageBody;
            return AmqpReader::CONSUMER_CANCEL;
        });
        $this->assertSame('foo bar buz', $transport);
    }

    public function testReceivesMultipleMessagesOneAtATime()
    {
        $reader = $this->masterFactory->createAmqpReader(static::getQueueName());
        $writer = $this->masterFactory->createAmqpWriter(static::getQueueName());

        $writer->addMessage('One');
        $writer->addMessage('Two');
        $writer->addMessage('Three');

        $receivedMessages = [];
        for ($i = 0; $i < 3; $i++) {
            $reader->consume(function ($messageBody) use (&$receivedMessages) {
                $receivedMessages[] = $messageBody;
                return AmqpReader::CONSUMER_CANCEL;
            });
        }
        $this->assertSame(['One', 'Two', 'Three'], $receivedMessages);
    }

    public function testReceivesMultipleMessagesWithOneConsumer()
    {
        $reader = $this->masterFactory->createAmqpReader(static::getQueueName());
        $writer = $this->masterFactory->createAmqpWriter(static::getQueueName());

        $writer->addMessage('One');
        $writer->addMessage('Two');
        $writer->addMessage('Three');

        $receivedMessages = [];
        $reader->consume(function ($messageBody) use (&$receivedMessages) {
            $receivedMessages[] = $messageBody;
            return count($receivedMessages) === 3 ?
                AmqpReader::CONSUMER_CANCEL :
                AmqpReader::CONSUMER_CONTINUE;
        });
        $this->assertSame(['One', 'Two', 'Three'], $receivedMessages);
    }

    public function testConsumesMessagesWithMultipleReaders()
    {
        $reader1 = $this->masterFactory->createAmqpReader(static::getQueueName());
        $reader2 = $this->masterFactory->createAmqpReader(static::getQueueName());

        $writer = $this->masterFactory->createAmqpWriter(static::getQueueName());
        $writer->addMessage('foo');
        $writer->addMessage('bar');
        $writer->addMessage('baz');
        $writer->addMessage('qux');

        $this->assertNextConsumedMessageSame('foo', $reader1);
        $this->assertNextConsumedMessageSame('bar', $reader2);
        $this->assertNextConsumedMessageSame('baz', $reader1);
        $this->assertNextConsumedMessageSame('qux', $reader2);
    }

    public function testCanReadMessagesWrittenViaAnotherConnection()
    {
        $masterFactoryWriteConnection = static::createMasterFactoryWithAmqpDriver();
        $writer = $masterFactoryWriteConnection->createAmqpWriter(static::getQueueName());
        $writer->addMessage('foo');
        $this->closeConnection($masterFactoryWriteConnection);

        $masterFactoryReadConnection = static::createMasterFactoryWithAmqpDriver();
        $reader = $masterFactoryReadConnection->createAmqpReader(static::getQueueName());
        $this->assertNextConsumedMessageSame('foo', $reader);
    }
}
