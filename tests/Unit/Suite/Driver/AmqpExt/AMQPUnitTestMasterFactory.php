<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt;

use LizardsAndPumpkins\Util\Config\ConfigReader;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactoryTrait;

class AMQPUnitTestMasterFactory implements MasterFactory
{
    use MasterFactoryTrait;

    /**
     * @var \PHPUnit_Framework_TestCase
     */
    private $testCase;

    public function __construct(\PHPUnit_Framework_TestCase $testCase)
    {
        $this->testCase = $testCase;
    }

    /**
     * @param string $class
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createMock($class)
    {
        return (new \PHPUnit_Framework_MockObject_MockBuilder($this->testCase, $class))
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();
    }

    /**
     * @return ConfigReader|
     */
    public function createConfigReader()
    {
        return $this->createMock(ConfigReader::class);
    }

    /**
     * @return \AMQPConnection
     */
    public function createAMQPConnection()
    {
        $credentials = [];
        return new \AMQPConnection($credentials);
    }
}
