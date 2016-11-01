<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Queue\Amqp\Driver;

use LizardsAndPumpkins\Messaging\Queue;

interface AmqpReader
{
    const CONSUMER_CONTINUE = null;
    const CONSUMER_CANCEL = false;
    
    public function countMessages() : int;

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
