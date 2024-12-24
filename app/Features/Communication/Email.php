<?php

namespace App\Features\Communication;

use Illuminate\Support\Lottery;

class Email
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): mixed
    {
        return true;
    }
}
