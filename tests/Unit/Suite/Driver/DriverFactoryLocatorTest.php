<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver;

use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpExt\AmqpExtFactory;
use LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\AmqpLib\AmqpLibFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\Amqp\Driver\DriverFactoryLocator
 */
class DriverFactoryLocatorTest extends TestCase
{
    /**
     * @var bool
     */
    private static $extensionLoadedReturnValue;

    /**
     * @var DriverFactoryLocator
     */
    private $factoryLocator;

    public static function extensionLoadedCall(string $ext) : bool
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

function extension_loaded(string $ext) : bool
{
    return DriverFactoryLocatorTest::extensionLoadedCall($ext);
}
