<?php

namespace LizardsAndPumpkins\Messaging\Queue\Amqp;

use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactoryTrait;

class IntegrationTestMasterFactory implements MasterFactory
{
    use MasterFactoryTrait;
}
