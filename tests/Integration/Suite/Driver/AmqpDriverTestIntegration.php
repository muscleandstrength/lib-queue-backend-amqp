<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver;

use LizardsAndPumpkins\Messaging\Queue\Amqp\IntegrationTestFactory;
use LizardsAndPumpkins\Messaging\Queue\Amqp\IntegrationTestMasterFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

abstract class AmqpDriverTestIntegration extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AmqpDriverFactory|IntegrationTestMasterFactory
     */
    private $masterFactory;

    /**
     * @param MasterFactory $masterFactory
     * @return void
     */
    abstract protected function closeConnection(MasterFactory $masterFactory);

    /**
     * @return AmqpDriverFactory|MasterFactory
     */
    protected static function createMasterFactoryWithAmqpDriver()
    {
        throw new \LogicException(sprintf('Override %s in concrete implementation', __FUNCTION__));
    }

    protected static function getQueueName()
    {
        throw new \LogicException(sprintf('Override %s in concrete implementation', __FUNCTION__));
    }

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

    /**
     * @param string $expected
     * @param AmqpReader $reader
     */
    private function assertNextConsumedMessageSame($expected, AmqpReader $reader)
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
            $this->markTestSkipped(sprintf(
                "Unable to connect to RabbitMQ: %s\n%s",
                $exception->getMessage(),
                $exception->getTraceAsString()
            ));
        }
    }

    protected function tearDown()
    {
        $reader = $this->masterFactory->createAmqpReader(static::getQueueName());
        $reader->purgeQueue();
    }

    public static function tearDownAfterClass()
    {
        $reader = static::createMasterFactoryWithAmqpDriver()->createAmqpReader(static::getQueueName());
        $reader->deleteQueue();
    }

    public function testCanCountMessagesInQueue()
    {
        $reader = $this->masterFactory->createAmqpReader(static::getQueueName());
        $writer = $this->masterFactory->createAmqpWriter(static::getQueueName());

        $this->assertEquals(0, $reader->countMessages(), 'Failed asserting fresh queue starts empty');
        $writer->addMessage('foo');
        usleep(10000);
        $this->assertEquals(1, $reader->countMessages(), 'Failed asserting adding 1 message brings count to 1');

        $writer->addMessage('bar');
        usleep(10000);
        $this->assertEquals(2, $reader->countMessages(), 'Failed asserting adding 2 messages brings count to 2');

        $reader->consume(function () {
            return AmqpReader::CONSUMER_CANCEL;
        });
        usleep(10000);
        $this->assertEquals(1, $reader->countMessages(), 'Failed asserting consuming 1 of 2 brings count to 1');

        $reader->consume(function () {
            return AmqpReader::CONSUMER_CANCEL;
        });
        usleep(10000);
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