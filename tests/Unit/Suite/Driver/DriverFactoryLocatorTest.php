<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver;

use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt\AmqpExtFactory;
use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpLib\AmqpLibFactory;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\DriverFactoryLocator
 */
class DriverFactoryLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var bool
     */
    private static $extensionLoadedReturnValue;

    /**
     * @var DriverFactoryLocator
     */
    private $factoryLocator;

    /**
     * @param string $ext
     * @return bool
     */
    public static function extensionLoadedCall($ext)
    {
        return (bool) self::$extensionLoadedReturnValue;
    }

    protected function setUp()
    {
        $this->factoryLocator = new DriverFactoryLocator();
    }

    public function testReturnsAmqpExtIfExtensionIsLoaded()
    {
        self::$extensionLoadedReturnValue = true;
        $this->assertInstanceOf(AmqpExtFactory::class, $this->factoryLocator->getDriverFactory());
    }

    public function testReturnsAmqpExtIfExtensionIsNotLoaded()
    {
        self::$extensionLoadedReturnValue = false;
        $this->assertInstanceOf(AmqpLibFactory::class, $this->factoryLocator->getDriverFactory());
    }
}

/**
 * @param string $ext
 * @return bool
 */
function extension_loaded($ext)
{
    return DriverFactoryLocatorTest::extensionLoadedCall($ext);
}
