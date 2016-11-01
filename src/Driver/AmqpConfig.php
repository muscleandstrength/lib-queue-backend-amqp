<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver;

use LizardsAndPumpkins\Util\Config\ConfigReader;

class AmqpConfig implements \LizardsAndPumpkins\Messaging\Queue\Amqp\AmqpConfig
{
    private static $defaultPort = '5672';

    private static $hostConfigKey = 'amqp_host';

    private static $userConfigKey = 'amqp_username';

    private static $passwordConfigKey = 'amqp_password';
    
    private static $vhostConfigKey = 'amqp_vhost';

    /**
     * @var ConfigReader
     */
    private $configReader;

    public function __construct(ConfigReader $configReader)
    {
        $this->configReader = $configReader;
    }

    public function getAmqpHost() : string
    {
        return $this->getHostWithoutPort();
    }

    private function getHostWithoutPort() : string
    {
        $host = $this->configReader->get(self::$hostConfigKey);
        return false !== ($pos = strpos($host, ':')) ?
            substr($host, 0, $pos) :
            $host;
    }

    public function getAmqpPort() : string
    {
        return $this->getPort();
    }

    private function getPort() : string
    {
        $host = $this->configReader->get(self::$hostConfigKey);
        return false !== ($pos = strpos($host, ':')) ?
            substr($host, $pos + 1) :
            self::$defaultPort;
    }

    public function getAmqpUsername() : string
    {
        return $this->configReader->get(self::$userConfigKey);
    }

    public function getAmqpPassword() : string
    {
        return $this->configReader->get(self::$passwordConfigKey);
    }

    public function getAmqpVhost() : string
    {
        return $this->configReader->get(self::$vhostConfigKey);
    }

    public function getCommandQueueName() : string
    {
        return 'command';
    }

    public function getDomainEventQueueName() : string
    {
        return 'event';
    }
}
