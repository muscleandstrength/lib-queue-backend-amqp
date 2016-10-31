<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt;

use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpReader;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt\AmqpExtReader
 */
class AmqpExtReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \AMQPQueue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockQueue;

    /**
     * @var AmqpExtReader
     */
    private $reader;

    protected function setUp()
    {
        if (!extension_loaded("amqp")) {
            $this->markTestSkipped('PHP extension amqp not found');
        }

        $this->mockQueue = $this->createMock(\AMQPQueue::class);
        $this->reader = new AmqpExtReader($this->mockQueue);
    }

    public function testImplementsAmqpReaderInterface()
    {
        $this->assertInstanceOf(AmqpReader::class, $this->reader);
    }

    public function testReturnsTheNumberOfMessagesOnTheQueue()
    {
        $this->mockQueue->method('declareQueue')->willReturn(222);
        $this->assertSame(222, $this->reader->countMessages());
    }

    public function testCallsMessageReceiver()
    {
        /** @var \AMQPEnvelope|\PHPUnit_Framework_MockObject_MockObject $stubEnvelope */
        $stubEnvelope = $this->createMock(\AMQPEnvelope::class);
        $stubEnvelope->method('getBody')->willReturn('foo');
        $stubEnvelope->method('getDeliveryTag')->willReturn(333);

        $this->mockQueue->expects($this->once())->method('consume')
            ->willReturnCallback(function (callable $callback) use ($stubEnvelope) {
                $callback($stubEnvelope);
            });

        $transport = null;
        $this->reader->consume(function ($messageBody) use (&$transport) {
            $transport = $messageBody;
        });
        $this->assertSame($stubEnvelope->getBody(), $transport);
    }

    public function testReturnsFalseFromConsumeCallbackIfSuppliedClosureReturnsCancelConsumeFlag()
    {
        /** @var \AMQPEnvelope|\PHPUnit_Framework_MockObject_MockObject $stubEnvelope */
        $stubEnvelope = $this->createMock(\AMQPEnvelope::class);
        $stubEnvelope->method('getBody')->willReturn('foo');
        $stubEnvelope->method('getDeliveryTag')->willReturn(333);

        $this->mockQueue->expects($this->once())->method('consume')
            ->willReturnCallback(function (callable $callback) use ($stubEnvelope) {
                $this->assertFalse($callback($stubEnvelope));
            });

        $this->reader->consume(function () {
            return AmqpReader::CONSUMER_CANCEL;
        });
    }

    public function testReturnsNotFalseFromConsumeCallbackIfSuppliedClosureNotReturnsCancelConsumeFlag()
    {
        /** @var \AMQPEnvelope|\PHPUnit_Framework_MockObject_MockObject $stubEnvelope */
        $stubEnvelope = $this->createMock(\AMQPEnvelope::class);
        $stubEnvelope->method('getBody')->willReturn('foo');
        $stubEnvelope->method('getDeliveryTag')->willReturn(333);

        $this->mockQueue->expects($this->once())->method('consume')
            ->willReturnCallback(function (callable $callback) use ($stubEnvelope) {
                $this->assertNotFalse($callback($stubEnvelope));
            });

        $this->reader->consume(function () {
            return AmqpReader::CONSUMER_CONTINUE;
        });
    }

    public function testCancellingAConsumerDelegatesToTheQueue()
    {
        $this->mockQueue->expects($this->once())->method('cancel');
        $this->reader->cancel();
    }

    public function testDelegatesPurgingTheQueue()
    {
        $this->mockQueue->expects($this->once())->method('purge');
        $this->reader->purgeQueue();
    }

    public function testDelegatesDeletingTheQueue()
    {
        $this->mockQueue->expects($this->once())->method('delete');
        $this->reader->deleteQueue();
    }
}
