<?php

declare(strict_types=1);

namespace App\Events\Models\User;

use App\Events\AbstractLogicalEvent;
use App\Models\User;

class UserDeleting extends AbstractLogicalEvent
{
    public function __construct(
        public User $model,
    ) {}
}
