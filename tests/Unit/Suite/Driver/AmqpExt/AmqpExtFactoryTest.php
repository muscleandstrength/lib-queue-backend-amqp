<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt;

use LizardsAndPumpkins\Util\Factory\Factory;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt\AmqpExtFactory
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt\AmpqExtQueueFactory
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt\AmqpExtReader
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt\AmqpExtWriter
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpConfig
 */
class AmqpExtFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $testQueueName = 'foo';

    /**
     * @var AmqpExtFactory
     */
    private $factory;

    protected function setUp()
    {
        if (!extension_loaded("amqp")) {
            $this->markTestSkipped('PHP extension amqp not found');
        }
        require_once __DIR__ . '/AMQPUnitTestMasterFactory.php';
        $this->factory = new AmqpExtFactory();
        (new AMQPUnitTestMasterFactory($this))->register($this->factory);
    }
    
    protected function tearDown()
    {
        $this->factory->createAmqpReader($this->testQueueName)->deleteQueue();
    }

    public function testImplementsFactoryInterface()
    {
        $this->assertInstanceOf(Factory::class, $this->factory);
    }

    public function testReturnsPhpAmqpReader()
    {
        $reader = $this->factory->createAmqpReader($this->testQueueName);
        $this->assertInstanceOf(AmqpExtReader::class, $reader);
    }

    public function testReturnsAMQPConnection()
    {
        $result = $this->factory->createAMQPConnection();
        $this->assertInstanceOf(\AMQPConnection::class, $result);
    }

    public function testReturnsPhpAmqpWriter()
    {
        $result = $this->factory->createAmqpWriter('bar');
        $this->assertInstanceOf(AmqpExtWriter::class, $result);
    }

    public function testReturnsTheSameAMQPConnectionInstance()
    {
        $result1 = $this->factory->getAMQPConnection();
        $result2 = $this->factory->getAMQPConnection();
        $this->assertSame($result1, $result2);
    }
}
