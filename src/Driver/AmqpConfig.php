<?php

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

    /**
     * @return string
     */
    public function getAmqpHost()
    {
        return $this->getHostWithoutPort($this->configReader->get(self::$hostConfigKey));
    }

    /**
     * @param string $host
     * @return string
     */
    private function getHostWithoutPort($host)
    {
        return false !== ($pos = strpos($host, ':')) ?
            substr($host, 0, $pos) :
            $host;
    }

    /**
     * @return string
     */
    public function getAmqpPort()
    {
        return $this->getPort($this->configReader->get(self::$hostConfigKey));
    }

    /**
     * @param string $host
     * @return string
     */
    private function getPort($host)
    {
        return false !== ($pos = strpos($host, ':')) ?
            substr($host, $pos + 1) :
            self::$defaultPort;
    }

    /**
     * @return string
     */
    public function getAmqpUsername()
    {
        return $this->configReader->get(self::$userConfigKey);
    }

    /**
     * @return string
     */
    public function getAmqpPassword()
    {
        return $this->configReader->get(self::$passwordConfigKey);
    }

    /**
     * @return string
     */
    public function getAmqpVhost()
    {
        return $this->configReader->get(self::$vhostConfigKey);
    }
}
