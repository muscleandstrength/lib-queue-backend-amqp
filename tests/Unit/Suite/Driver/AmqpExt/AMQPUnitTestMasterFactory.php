<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt;

use LizardsAndPumpkins\Messaging\Queue\Amqp\AmqpConfig;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactoryTrait;
use PHPUnit\Framework\TestCase;

class AMQPUnitTestMasterFactory implements MasterFactory
{
    use MasterFactoryTrait;

    /**
     * @var TestCase
     */
    private $testCase;

    public function __construct(TestCase $testCase)
    {
        $this->testCase = $testCase;
    }

    private function createMock(string $class) : \PHPUnit_Framework_MockObject_MockObject
    {
        return (new \PHPUnit_Framework_MockObject_MockBuilder($this->testCase, $class))
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();
    }

    public function createAMQPConnection() : \AMQPConnection
    {
        $credentials = [];
        return new \AMQPConnection($credentials);
    }

    public function createAmqpConfig() : AmqpConfig
    {
        return $this->createMock(AmqpConfig::class);
    }
}
