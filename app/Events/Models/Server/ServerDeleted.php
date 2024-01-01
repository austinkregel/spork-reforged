<?php

declare(strict_types=1);

namespace App\Events\Models\Server;

use App\Events\AbstractLogicalEvent;
use App\Models\Server;

class ServerDeleted extends AbstractLogicalEvent
{
    public function __construct(
        public Server $model,
    ) {
    }
}
