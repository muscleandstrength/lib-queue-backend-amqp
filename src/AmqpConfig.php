<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp;

interface AmqpConfig
{
    public function getAmqpHost() : string;

    public function getAmqpPort() : string;

    public function getAmqpUsername() : string;

    public function getAmqpPassword() : string;

    public function getAmqpVhost() : string;

    public function getCommandQueueName() : string;

    public function getDomainEventQueueName() : string;
}
