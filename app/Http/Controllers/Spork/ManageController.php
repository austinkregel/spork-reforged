<?php

declare(strict_types=1);

namespace App\Http\Controllers\Spork;

use App\Services\Development\DescribeTableService;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class ManageController
{
    public function index()
    {
        return Inertia::render('Manage/Index', [
            'title' => 'Dynamic CRUD',
            'description' => [
                'fillable' => [],
            ],
            'metrics' => Activity::latest()
                ->paginate(
                    request('limit', 15),
                    ['*'],
                    'manage_page',
                    request('manage_page', 1)
                ),
        ]);
    }

    public function show($model)
    {
        $table = (new $model)->getTable();
        $description = (new DescribeTableService())->describe(new $model);

        /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
        $paginator = $model::query()
            ->with(
                array_filter($description['includes'], fn ($relation) => ! in_array($relation, [
                    'tagsTranslated',
                ]))
            )
            ->paginate(request('limit', 15), ['*'], 'manage_page', request('manage_page', 1));

        $data = $paginator->items();
        $paginator = $paginator->toArray();

        unset($paginator['data']);

        return Inertia::render('Manage/List', [
            'title' => 'CRUD '.Str::ucfirst(str_replace('_', ' ', Str::ascii($table, 'en'))),
            'description' => $description,
            'singular' => Str::singular($table),
            'plural' => Str::plural($table),
            'link' => '/'.$table,
            'apiLink' => '/api/crud/'.$table,
            'data' => $data,
            'paginator' => $paginator,
        ]);
    }
}
