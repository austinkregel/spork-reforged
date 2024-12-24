<?php

namespace App\Features\Automatic;

use Illuminate\Support\Lottery;

class ServerLinking
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): mixed
    {
        return false;
    }
}
