<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp;

use LizardsAndPumpkins\Messaging\MessageReceiver;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpReader;
use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpWriter;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\Amqp\AmqpQueue
 */
class AmqpQueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AmqpQueue
     */
    private $queue;

    /**
     * @var AmqpReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockAmqpReader;

    /**
     * @var AmqpWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockAmqpWriter;

    /**
     * @var Message
     */
    private $testMessage;

    protected function setUp()
    {
        $this->mockAmqpReader = $this->createMock(AmqpReader::class);
        $this->mockAmqpWriter = $this->createMock(AmqpWriter::class);
        $this->queue = new AmqpQueue($this->mockAmqpReader, $this->mockAmqpWriter);
        $this->testMessage = Message::withCurrentTime('foo', [], []);
    }

    public function testImplementsQueueInterface()
    {
        $this->assertInstanceOf(Queue::class, $this->queue);
    }

    public function testDelegatesToReaderToFetchTheMessageCount()
    {
        $this->mockAmqpReader->expects($this->once())->method('countMessages')->willReturn(42);
        $this->assertSame(42, $this->queue->count());
    }
    
    public function testDelegatesToWriterWhenAddingNewMessage()
    {
        $this->mockAmqpWriter->expects($this->once())->method('addMessage')->with($this->testMessage->serialize());
        $this->queue->add($this->testMessage);
    }

    public function testCallsMessageReceiverWithMessage()
    {
        $this->mockAmqpReader->method('consume')->willReturnCallback(function (callable $callback) {
            $callback($this->testMessage->serialize());
        });
        /** @var MessageReceiver|\PHPUnit_Framework_MockObject_MockObject $stubReceiver */
        $stubReceiver = $this->createMock(MessageReceiver::class);
        $stubReceiver->expects($this->once())->method('receive')->with($this->isInstanceOf(Message::class));
        
        $this->queue->consume($stubReceiver, 1);
    }

    public function testReturnsCancelConsumeFlagAfterGivenNumberOfMessages()
    {
        $this->mockAmqpReader->method('consume')->willReturnCallback(function (callable $callback) {
            $this->assertSame(AmqpReader::CANCEL_CONSUME, $callback($this->testMessage->serialize()));
        });
        /** @var MessageReceiver|\PHPUnit_Framework_MockObject_MockObject $stubReceiver */
        $stubReceiver = $this->createMock(MessageReceiver::class);

        $this->queue->consume($stubReceiver, 1);
    }

    public function testDoesNotReturnsCancelConsumeFlagBeforeGivenNumberOfMessages()
    {
        $this->mockAmqpReader->method('consume')->willReturnCallback(function (callable $callback) {
            $this->assertNotSame(AmqpReader::CANCEL_CONSUME, $callback($this->testMessage->serialize()));
        });
        
        /** @var MessageReceiver|\PHPUnit_Framework_MockObject_MockObject $stubReceiver */
        $stubReceiver = $this->createMock(MessageReceiver::class);
        $this->mockAmqpReader->expects($this->never())->method('cancel');

        $this->queue->consume($stubReceiver, 2);
    }

    public function testPurgesTheQueueWhenClearIsCalled()
    {
        $this->mockAmqpReader->expects($this->once())->method('purgeQueue');
        $this->queue->clear();
    }
}

