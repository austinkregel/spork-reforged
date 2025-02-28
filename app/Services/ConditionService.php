<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Conditionable;
use App\Models\Condition;
use App\Models\Navigation;
use App\Models\Tag;
use App\Services\Condition\AbstractLogicalOperator;
use App\Services\Condition\ArrayContainsValueOperator;
use App\Services\Condition\ContainsValueOperator;
use App\Services\Condition\ContainsValueStrictOperator;
use App\Services\Condition\DoesntContainValueOperator;
use App\Services\Condition\DoesntEqualValueOperator;
use App\Services\Condition\EndsWithOperator;
use App\Services\Condition\EqualsValueOperator;
use App\Services\Condition\GreaterThanOperator;
use App\Services\Condition\GreaterThanOrEqualToOperator;
use App\Services\Condition\HasRoleOperator;
use App\Services\Condition\LessThanOperator;
use App\Services\Condition\LessThanOrEqualToOperator;
use App\Services\Condition\StartsWithOperator;
use Illuminate\Support\Arr;

class ConditionService
{
    public const AVAILABLE_CONDITIONS = [
        // strings, numbers, arrays, etc..
        Condition::COMPARATOR_NOT_EQUAL => DoesntEqualValueOperator::class,
        Condition::COMPARATOR_EQUALS => EqualsValueOperator::class,

        // *strings* or arrays,
        Condition::COMPARATOR_IN => ArrayContainsValueOperator::class,
        Condition::COMPARATOR_LIKE => ContainsValueOperator::class,
        Condition::COMPARATOR_LIKE_STRICT => ContainsValueStrictOperator::class,
        Condition::COMPARATOR_NOT_LIKE => DoesntContainValueOperator::class,

        // Numbers
        Condition::COMPARATOR_GREATER_THAN => GreaterThanOperator::class,
        Condition::COMPARATOR_GREATER_THAN_EQUAL => GreaterThanOrEqualToOperator::class,
        Condition::COMPARATOR_LESS_THAN => LessThanOperator::class,
        Condition::COMPARATOR_LESS_THAN_EQUAL => LessThanOrEqualToOperator::class,

        // Strings
        Condition::COMPARATOR_STARTS_WITH => StartsWithOperator::class,
        Condition::COMPARATOR_ENDS_WITH => EndsWithOperator::class,

        'HAS_ROLE' => HasRoleOperator::class,
    ];

    public function navigation()
    {
        $parsedUrl = parse_url(request()->getRequestUri());

        // So we want to filter out any nav items
        $navItems = Navigation::query()
            ->with(['conditions', 'children' => function ($builder) {
                $builder->orderBy('order');
            }])
            ->where('authentication_required', auth()->check())
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get()
            ->map(function (Navigation $item) use ($parsedUrl) {
                $item->current = $item->href === request()->getRequestUri() || (
                    $item->children->isNotEmpty() &&
                    // We don't want to use the query param in the comparison
                    $item->children->filter(fn ($item) => $item->href === $parsedUrl['path'])
                        ->count() > 0
                );

                if ($item->children->isNotEmpty()) {
                    $item->children->sortDesc();
                }

                return $item;
            });

        return $navItems->filter(fn (Navigation $item) => $this->process($item))
            ->groupBy('group')
            ->sortKeys();
    }

    public function process(Conditionable $item, array $additionalValueData = [])
    {
        if ($item->conditions->count() === 0) {
            return true;
        }

        $returnedValue = true;
        /** @var Tag $condition */
        foreach ($item->conditions as $condition) {
            $comparator = static::AVAILABLE_CONDITIONS[$condition->comparator];
            /** @var AbstractLogicalOperator $instance */
            $instance = new $comparator;

            $passesCondition = $instance->compute(
                // Looking for the condition value
                $condition->value,
                // inside the parameter's interpolated value.
                $this->processParameter($condition->parameter, $additionalValueData),
            );

            if ($passesCondition && ! $item->must_all_conditions_pass) {
                return true;
            }

            if (! $passesCondition) {
                $returnedValue = false;
            }
        }

        return $returnedValue;
    }

    protected function processParameter(string $parameter, array $additionalData)
    {
        // This might be some thing like config:app.env to return a config value
        if (str_contains($parameter, ':')) {
            [$primaryKey, $field] = explode(':', $parameter);

            $valueExtractionFunction = $this->matchCustomPrimaryKeyFunctions($primaryKey, $field);

            return $valueExtractionFunction($field);
        }

        if ($parameter === 'user') {
            return auth()->user();
        }

        // This can be an single dimensions, or a multidimensional array
        // Access via dot notation.
        return Arr::get($additionalData, $parameter);
    }

    protected function matchCustomPrimaryKeyFunctions(string $key, ?string $parameter)
    {
        return match ($key) {
            'config' => fn ($field) => config($field),
            default => dd($key, $parameter),
        };
    }
}
