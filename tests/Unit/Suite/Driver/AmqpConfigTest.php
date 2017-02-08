<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver;

use LizardsAndPumpkins\Messaging\Queue\Amqp\AmqpConfig as AmqpConfigInterface;
use LizardsAndPumpkins\Util\Config\ConfigReader;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpConfig
 */
class AmqpConfigTest extends TestCase
{
    /**
     * @var AmqpConfig
     */
    private $config;

    /**
     * @var ConfigReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockConfigReader;

    protected function setUp()
    {
        $this->mockConfigReader = $this->createMock(ConfigReader::class);
        $this->config = new AmqpConfig($this->mockConfigReader);
    }

    public function testImplementsAmqpConfigInterface()
    {
        $this->assertInstanceOf(AmqpConfigInterface::class, $this->config);
    }

    public function testRemovesPortFromHostname()
    {
        $this->mockConfigReader->method('get')->with('amqp_host')->willReturn('amqp-test.example.com:5672');
        $this->assertSame('amqp-test.example.com', $this->config->getAmqpHost());
    }

    public function testReturnsHostnameAsIsIfNoPortIsPresent()
    {
        $this->mockConfigReader->method('get')->with('amqp_host')->willReturn('amqp-test.example.com');
        $this->assertSame('amqp-test.example.com', $this->config->getAmqpHost());
    }

    public function testReturnsPortFromHostnameIfPresent()
    {
        $this->mockConfigReader->method('get')->with('amqp_host')->willReturn('amqp-test.example.com:1234');
        $this->assertSame('1234', $this->config->getAmqpPort());
    }

    public function testReturnsDefaultPortIfNotPresentOnHostname()
    {
        $this->mockConfigReader->method('get')->with('amqp_host')->willReturn('amqp-test.example.com');
        $this->assertSame('5672', $this->config->getAmqpPort());
    }

    public function testReturnsUsernameFromConfigReader()
    {
        $this->mockConfigReader->method('get')->with('amqp_username')->willReturn('foo bar');
        $this->assertSame('foo bar', $this->config->getAmqpUsername());
    }

    public function testReturnsPasswordFomConfigReader()
    {
        $this->mockConfigReader->method('get')->with('amqp_password')->willReturn('baz');
        $this->assertSame('baz', $this->config->getAmqpPassword());
    }

    public function testReturnsVhostFomConfigReader()
    {
        $this->mockConfigReader->method('get')->with('amqp_vhost')->willReturn('qux');
        $this->assertSame('qux', $this->config->getAmqpVhost());
    }

    public function testReturnsCommandQueueName()
    {
        $this->assertSame('command', $this->config->getCommandQueueName());
    }

    public function testReturnsDomainEventQueueName()
    {
        $this->assertSame('event', $this->config->getDomainEventQueueName());
    }
}
