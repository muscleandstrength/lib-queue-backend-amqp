<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp;

class IntegrationTestAmqpConfig implements AmqpConfig
{
    public function getAmqpHost() : string
    {
        return isset($_ENV['TEST_AMQP_HOST']) ?
            $_ENV['TEST_AMQP_HOST'] :
            'localhost';
    }

    public function getAmqpPort() : string
    {
        return isset($_ENV['TEST_AMQP_PORT']) ?
            $_ENV['TEST_AMQP_PORT'] :
            '5672';
    }

    public function getAmqpUsername() : string
    {
        return isset($_ENV['TEST_AMQP_USERNAME']) ?
            $_ENV['TEST_AMQP_USERNAME'] :
            'guest';
    }

    public function getAmqpPassword() : string
    {
        return isset($_ENV['TEST_AMQP_PASSWORD']) ?
            $_ENV['TEST_AMQP_PASSWORD'] :
            'guest';
    }

    public function getAmqpExchangeName() : string
    {
        return 'integration-test';
    }

    public function getAmqpVhost() : string
    {
        return isset($_ENV['TEST_AMQP_VHOST']) ?
            $_ENV['TEST_AMQP_VHOST'] :
            '/';
    }

    public function getCommandQueueName() : string
    {
        return 'integration-test-command';
    }

    public function getDomainEventQueueName() : string
    {
        return 'integration-test-event';
    }
}
