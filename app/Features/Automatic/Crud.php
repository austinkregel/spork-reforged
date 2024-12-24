<?php

namespace App\Features\Automatic;

use Illuminate\Support\Lottery;

class Crud
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): mixed
    {
        return true;
    }
}
