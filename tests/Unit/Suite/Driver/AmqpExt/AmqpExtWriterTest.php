<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt;

use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpWriter;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt\AmqpExtWriter
 */
class AmqpExtWriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \AMQPExchange|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockExchange;

    /**
     * @var AmqpExtWriter
     */
    private $writer;

    protected function setUp()
    {
        if (!extension_loaded("amqp")) {
            $this->markTestSkipped('PHP extension amqp not found');
        }

        $this->mockExchange = $this->createMock(\AMQPExchange::class);
        $this->writer = new AmqpExtWriter($this->mockExchange);
    }

    public function testImplementsAmqpWriterInterface()
    {
        $this->assertInstanceOf(AmqpWriter::class, $this->writer);
    }

    public function testEmitsAnAMQPMessage()
    {
        $this->mockExchange->expects($this->once())->method('publish')->with('foo');
        $this->writer->addMessage('foo');
    }
}
