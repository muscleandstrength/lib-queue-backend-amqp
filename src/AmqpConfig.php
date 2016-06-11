<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp;

interface AmqpConfig
{
    /**
     * @return string
     */
    public function getAmqpHost();

    /**
     * @return string
     */
    public function getAmqpPort();

    /**
     * @return string
     */
    public function getAmqpUsername();

    /**
     * @return string
     */
    public function getAmqpPassword();

    /**
     * @return string
     */
    public function getAmqpVhost();

    /**
     * @return string
     */
    public function getCommandQueueName();

    /**
     * @return string
     */
    public function getDomainEventQueueName();
}
