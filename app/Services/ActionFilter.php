<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Arr;
use Spatie\QueryBuilder\QueryBuilder;

class ActionFilter
{
    public const WHITELISTED_ACTIONS = [
        'first',
        'paginate',
        'get',
        'count',
        'sum',
        'avg',
        'groupBy',
        'orderBy',
    ];

    protected const DEFAULT_ACTION = 'paginate';

    /**
     * @var string
     */
    protected $actionString;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * @var string
     */
    protected $orderByField;

    /**
     * @var string
     */
    protected $groupByField;

    /**
     * @var bool
     */
    protected $hasArguments = false;

    public function __construct(string $actionString)
    {
        $this->actionString = $actionString;
        $this->parseAction();
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function hasArguments(): bool
    {
        return $this->hasArguments;
    }

    protected function parseAction()
    {
        $parts = explode(':', $this->actionString);

        $this->method = $this->validateAction(Arr::first($parts));

        if (count($parts) === 1) {
            $this->hasArguments = false;

            return;
        }

        $this->arguments = array_filter(explode(',', end($parts)), function ($bit) {
            return ! empty($bit);
        });

        $this->hasArguments = count($this->arguments) > 0;
    }

    protected function validateAction(string $action): string
    {
        if (! in_array($action, static::WHITELISTED_ACTIONS)) {
            return static::DEFAULT_ACTION;
        }

        return $action;
    }

    public function orderBy($field)
    {
        if (empty($field)) {
            return $this;
        }

        $this->orderByField = $field;

        return $this;
    }

    public function groupBy($field)
    {
        if (empty($field)) {
            return $this;
        }

        $this->groupByField = $field;

        return $this;
    }

    public function execute(QueryBuilder $query)
    {
        $method = $this->getMethod();
        $arguments = $this->getArguments();

        if ($method === 'orderBy') {
            $query->orderBy(...$this->getArguments());
            $method = 'get';
            $arguments = [];
        }

        if (isset($this->orderByField)) {
            $query->groupBy($this->orderByField);
        }

        if ($method === 'count') {
            return cache()->remember($query->toRawSql(), now()->addHour(), fn () => $query->count());
        }

        if (count($arguments) > 0) {
            $result = $query->{$method}(...$arguments);
        } else {
            $result = $query->{$method}();
        }

        if ($result instanceof QueryBuilder) {
            return cache()->remember($result->toRawSql(), now()->addHour(), fn () => $result->first()->count);
        }

        return $result;
    }
}
