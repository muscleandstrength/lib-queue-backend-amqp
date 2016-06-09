<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp;

class IntegrationTestAmqpConfig implements AmqpConfig
{
    /**
     * @return string
     */
    public function getAmqpHost()
    {
        return isset($_ENV['TEST_AMQP_HOST']) ?
            $_ENV['TEST_AMQP_HOST'] :
            'localhost';
    }

    /**
     * @return string
     */
    public function getAmqpPort()
    {
        return isset($_ENV['TEST_AMQP_PORT']) ?
            $_ENV['TEST_AMQP_PORT'] :
            '5672';
    }

    /**
     * @return string
     */
    public function getAmqpUsername()
    {
        return isset($_ENV['TEST_AMQP_USERNAME']) ?
            $_ENV['TEST_AMQP_USERNAME'] :
            'guest';
    }

    /**
     * @return string
     */
    public function getAmqpPassword()
    {
        return isset($_ENV['TEST_AMQP_PASSWORD']) ?
            $_ENV['TEST_AMQP_PASSWORD'] :
            'guest';
    }

    /**
     * @return string
     */
    public function getAmqpExchangeName()
    {
        return 'integration-test';
    }

    /**
     * @return string
     */
    public function getAmqpVhost()
    {
        return isset($_ENV['TEST_AMQP_VHOST']) ?
            $_ENV['TEST_AMQP_VHOST'] :
            '/';
    }
}
