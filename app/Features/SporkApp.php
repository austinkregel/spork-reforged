<?php

namespace App\Features;

use Illuminate\Support\Lottery;

class SporkApp
{
    /**
     * Resolve the feature's initial value.
     */
    public function resolve(mixed $scope): mixed
    {
        return true;
    }

    public function permissions(): array
    {
        return [
            'create_credentials'
        ];
    }
}
