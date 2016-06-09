<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver;

use LizardsAndPumpkins\Messaging\Queue\Amqp\IntegrationTestFactory;
use LizardsAndPumpkins\Messaging\Queue\Amqp\IntegrationTestMasterFactory;

abstract class AmqpDriverTestIntegration extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AmqpDriverFactory|IntegrationTestMasterFactory
     */
    private $masterFactory;
    
    /**
     * @return IntegrationTestMasterFactory|AmqpDriverFactory
     */
    protected static function createMasterFactoryWithAmqpDriver()
    {
        throw new \LogicException(sprintf('Override %s in concrete implementation', __FUNCTION__));
    }

    /**
     * @return string
     */
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
    
    private function assertNextConsumedMessageSame($expected, AmqpReader $reader)
    {
        $reader->consume(function ($messageBody) use ($expected) {
            $this->assertSame($expected, $messageBody);
            return AmqpReader::CANCEL_CONSUME;
        });
    }

    protected function setUp()
    {
        try {
            $this->masterFactory = static::createMasterFactoryWithAmqpDriver();
            $reader = $this->masterFactory->createAmqpReader(static::getQueueName());
            $reader->purgeQueue();
        } catch (\Exception $exception) {
            $this->markTestSkipped(sprintf("Unable to connect to RabbitMQ: %s\n%s", $exception->getMessage(),
                $exception->getTraceAsString()));
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
            return AmqpReader::CANCEL_CONSUME;
        });
        usleep(10000);
        $this->assertEquals(1, $reader->countMessages(), 'Failed asserting consuming 1 of 2 brings count to 1');
        
        $reader->consume(function () {
            return AmqpReader::CANCEL_CONSUME;
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
            return AmqpReader::CANCEL_CONSUME;
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
                return AmqpReader::CANCEL_CONSUME;
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
                AmqpReader::CANCEL_CONSUME :
                AmqpReader::CONTINUE_CONSUME;
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
}
