<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpLib;

use PhpAmqpLib\Channel\AMQPChannel;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpLib\AmqpLibQueueDeclaration
 */
class AmqpLibQueueDeclarationTest extends \PHPUnit_Framework_TestCase
{
    public function testDeclaresQueueOnChannelAndReturnsTheMessageCount()
    {
        /** @var AMQPChannel|\PHPUnit_Framework_MockObject_MockObject $mockChannel */
        $mockChannel = $this->createMock(AMQPChannel::class);
        $mockChannel->expects($this->once())->method('queue_declare')->willReturn([0, 123, false]);
        
        $queueDeclaration = new AmqpLibQueueDeclaration($mockChannel);
        $this->assertSame(123, $queueDeclaration->declareQueue('foo'));
    }
}
