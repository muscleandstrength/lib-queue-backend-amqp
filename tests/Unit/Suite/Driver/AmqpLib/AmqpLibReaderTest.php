<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpLib;

use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpReader;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpLib\AmqpLibReader
 */
class AmqpLibReaderTest extends \PHPUnit_Framework_TestCase
{
    private $testQueueName = 'foo';

    /**
     * @var AmqpLibReader
     */
    private $reader;

    /**
     * @var AMQPChannel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockChannel;

    /**
     * @var AmqpLibQueueDeclaration|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubQueueDeclaration;

    protected function setUp()
    {
        $this->mockChannel = $this->createMock(AMQPChannel::class);
        $this->stubQueueDeclaration = $this->createMock(AmqpLibQueueDeclaration::class);
        $this->reader = new AmqpLibReader($this->mockChannel, $this->testQueueName, $this->stubQueueDeclaration);
    }

    public function testImplementsReaderInterface()
    {
        $this->assertInstanceOf(AmqpReader::class, $this->reader);
    }

    public function testReturnsCountOfMessagesInQueue()
    {
        $this->stubQueueDeclaration->method('declare')->willReturn(222);
        $this->assertSame(222, $this->reader->countMessages());
    }

    public function testAddsConsumeCallbackAndCallsWaitOnChannel()
    {
        /** @var AMQPMessage|\PHPUnit_Framework_MockObject_MockObject $stubAMQPMessage */
        $stubAMQPMessage = $this->createMock(AMQPMessage::class);
        $stubAMQPMessage->body = 'foo';
        $stubAMQPMessage->delivery_info = [
            'channel' => $this->mockChannel,
            'delivery_tag' => 333,
        ];

        $callback = function ($queue, $consumer_tag, $no_local, $no_ack, $exclusive, $nowait, $callback) use (
            $stubAMQPMessage
        ) {
            $callback($stubAMQPMessage);
        };
        $this->mockChannel->expects($this->once())->method('basic_consume')->willReturnCallback($callback);
        $this->mockChannel->expects($this->once())->method('wait');

        $transport = null;
        $this->reader->consume(function ($messageBody) use (&$transport) {
            $transport = $messageBody;
        });

        $this->assertSame($stubAMQPMessage->body, $transport);
    }

    public function testCallsCancelIfTheCallbackReturnsCancelConsumerFlag()
    {
        /** @var AMQPMessage|\PHPUnit_Framework_MockObject_MockObject $stubAMQPMessage */
        $stubAMQPMessage = $this->createMock(AMQPMessage::class);
        $stubAMQPMessage->body = 'foo';
        $stubAMQPMessage->delivery_info = [
            'channel' => $this->mockChannel,
            'delivery_tag' => 333,
        ];

        $callback = function ($queue, $consumer_tag, $no_local, $no_ack, $exclusive, $nowait, $callback) use (
            $stubAMQPMessage
        ) {
            $callback($stubAMQPMessage);
        };
        $this->mockChannel->method('basic_consume')->willReturnCallback($callback);
        $this->mockChannel->expects($this->once())->method('basic_cancel');

        $this->reader->consume(function () {
            return AmqpReader::CANCEL_CONSUME;
        });
    }

    public function testDoesNotCallsCancelIfTheCallbackNotReturnsCancelConsumerFlag()
    {
        /** @var AMQPMessage|\PHPUnit_Framework_MockObject_MockObject $stubAMQPMessage */
        $stubAMQPMessage = $this->createMock(AMQPMessage::class);
        $stubAMQPMessage->body = 'foo';
        $stubAMQPMessage->delivery_info = [
            'channel' => $this->mockChannel,
            'delivery_tag' => 333,
        ];

        $callback = function ($queue, $consumer_tag, $no_local, $no_ack, $exclusive, $nowait, $callback) use (
            $stubAMQPMessage
        ) {
            $callback($stubAMQPMessage);
        };
        $this->mockChannel->method('basic_consume')->willReturnCallback($callback);
        $this->mockChannel->expects($this->never())->method('basic_cancel');

        $this->reader->consume(function () {
            return AmqpReader::CONTINUE_CONSUME;
        });
    }

    public function testDelegatesCancelingTheConsumerToChannel()
    {
        $this->mockChannel->expects($this->once())->method('basic_cancel');
        $this->reader->cancel();
    }

    public function testDelegatesPurgingTheQueueToChannel()
    {
        $this->mockChannel->expects($this->once())->method('queue_purge')->with($this->testQueueName );
        $this->reader->purgeQueue();
    }

    public function testDelegatesDeletingTheQueueToChannel()
    {
        $this->mockChannel->expects($this->once())->method('queue_delete')->with($this->testQueueName);
        $this->reader->deleteQueue();
    }
}
