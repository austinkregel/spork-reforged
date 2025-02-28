<?php

declare(strict_types=1);

namespace App\Services\Condition;

use Carbon\Carbon;

class LessThanOperator extends AbstractLogicalOperator
{
    public function compute(mixed $valueFromCondition, mixed $valueFromParameter): bool
    {
        if (is_array($valueFromCondition) || is_array($valueFromParameter)) {
            return false;
        }

        if (is_string($valueFromCondition) && is_string($valueFromParameter)) {
            if (strtotime($valueFromCondition) && strtotime($valueFromParameter)) {
                // we're dealing with a date, or date time.
                return Carbon::parse($valueFromCondition)->isBefore(Carbon::parse($valueFromParameter));
            }

            return strlen($valueFromCondition ?? '') < strlen($valueFromParameter ?? '');
        }

        if (is_null($valueFromCondition)) {
            dd($valueFromCondition, $valueFromParameter);
        }

        if (! is_numeric($valueFromCondition)) {
            // At the time of writing, I'm not sure what could end up here other than maybe objects/arrays?
            $valueFromCondition = strlen($valueFromCondition ?? '');
        }

        if (is_null($valueFromParameter)) {
            dd($valueFromCondition, $valueFromParameter);
        }

        if (! is_numeric($valueFromParameter)) {
            $valueFromParameter = strlen($valueFromParameter);
        }

        return $valueFromCondition < $valueFromParameter;
    }
}
