<?php

declare(strict_types=1);

namespace App\Events\Models\Person;

use App\Events\AbstractLogicalEvent;
use App\Models\Person;

class PersonCreating extends AbstractLogicalEvent
{
    public function __construct(
        public Person $model,
    ) {}
}
