<?php

declare(strict_types=1);

namespace App\Http\Controllers\Spork;

use App\Models\Crud;
use App\Models\User;
use App\Services\Code;
use App\Services\Development\DescribeTableService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class ManageController
{
    public function index()
    {
        Inertia::share('subnavigation', $navigation = $this->navigation());

        $user = request()->user();

        return Inertia::render('Manage/Index', [
            'title' => 'Dynamic CRUD',
            'activity' => Activity::latest()
                ->paginate(
                    request('limit', 15),
                    ['*'],
                    'manage_page',
                    request('manage_page', 1)
                ),
            'metrics' => [
                'Projects' => $user->personalProjects()->count(),
                'Short codes' => $user->shortCodes()->count(),
                'Credentials' => $user->credentials()->count(),
                'Domains' => $user->domains()->count(),
                'Accounts' => $user->accounts()->count(),
                'Messages' => $user->messages()->count(),
                'Emails' => $user->emails()->count(),
                'Servers' => $user->servers()->count(),
                'External Rss Feeds' => $user->externalRssFeeds()->count(),
                'Person' => $user->person()->count(),
                'budgets' => $user->budgets()->count(),
                'Tags' => $user->tags()->count(),
            ],
        ]);
    }

    public function show($model)
    {
        Inertia::share('subnavigation', $navigation = $this->navigation());
        $model = $navigation->firstWhere('slug', $model)['class'];

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

    protected function navigation(): Collection
    {
        $crudModels = Collection::make(Code::instancesOf(Crud::class)
            ->getClasses());

        return $crudModels->map(function ($class) {
            $tableName = (new $class)->getTable();
            return [
                'name' => Str::ucfirst(str_replace('_', ' ', Str::ascii($tableName, 'en'))),
                'href' => '/-/manage/'.$slug = Str::slug(Str::singular($tableName)),
                'icon' => ucfirst(Str::singular(Str::camel($tableName))).'Icon',
                'slug' => $slug,
                'class' => $class,
            ];
        });
    }
}
