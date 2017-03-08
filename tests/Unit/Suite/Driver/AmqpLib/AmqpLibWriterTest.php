<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpLib;

use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpWriter;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpLib\AmqpLibWriter
 */
class AmqpLibWriterTest extends TestCase
{
    /**
     * @var \PhpAmqpLib\Channel\AMQPChannel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockChannel;

    private $testExchangeName = 'foo';

    /**
     * @var AmqpLibWriter
     */
    private $writer;

    public function setUp()
    {
        $this->mockChannel = $this->createMock(\PhpAmqpLib\Channel\AMQPChannel::class);
        $this->writer = new AmqpLibWriter($this->mockChannel, $this->testExchangeName);
    }

    public function testImplementsAmpqWriter()
    {
        $this->assertInstanceOf(AmqpWriter::class, $this->writer);
    }

    public function testPublishesMessageToExchange()
    {
        $testMessage = 'foo bar';
        $this->mockChannel->expects($this->once())->method('basic_publish')
            ->willReturnCallback(function (AMQPMessage $message, $exchangeName) use ($testMessage) {
                $this->assertSame($testMessage, $message->getBody());
                $this->assertSame($this->testExchangeName, $exchangeName);
            });
        $this->writer->addMessage($testMessage);
    }
}
