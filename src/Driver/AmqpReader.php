<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver;

use LizardsAndPumpkins\Messaging\Queue;

interface AmqpReader
{
    const CONSUMER_CONTINUE = null;
    const CONSUMER_CANCEL = false;
    
    /**
     * @return int
     */
    public function countMessages();

    /**
     * @param callable $callback
     * @return void
     */
    public function consume(callable $callback);

    /**
     * @return void
     */
    public function cancel();

    /**
     * @return void
     */
    public function purgeQueue();

    /**
     * @return void
     */
    public function deleteQueue();
}
