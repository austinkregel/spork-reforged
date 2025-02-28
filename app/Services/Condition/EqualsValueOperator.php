<?php

declare(strict_types=1);

namespace App\Services\Condition;

class EqualsValueOperator extends AbstractLogicalOperator
{
    public function compute(mixed $needle, mixed $haystack): bool
    {
        return $needle == $haystack;
    }
}
