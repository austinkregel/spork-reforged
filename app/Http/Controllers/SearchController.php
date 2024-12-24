<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Code;
use App\Services\Development\DescribeTableService;
use Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Scout\Searchable;
use Meilisearch\Client;
use Meilisearch\Contracts\SearchQuery;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $client = new Client(
            config('scout.meilisearch.host'),
            config('scout.meilisearch.key'),
        );

        $searchableModels = Code::instancesOf(Searchable::class)->getClasses();

        $result = $client->multiSearch(array_map(function ($model) {
            return (new SearchQuery())
                ->setQuery(request('query'))
                ->setLimit(4)
                ->setIndexUid((new $model)->searchableAs());
        }, $searchableModels));

        return Inertia::render('Search/Search', [
            'results' => array_values(array_filter($result['results'], fn ($r) => count($r['hits']) > 0)),
        ]);
    }

    public function show(Request $request, $index)
    {
        $searchableModel = Arr::first(array_filter(
            Code::instancesOf(Searchable::class)->getClasses(),
            fn ($model) => (new $model)->searchableAs() === $index,
        ));

        abort_if(empty($searchableModel), 404, 'Index not found');

        $result = $searchableModel::search(request('query'))
            ->paginate(10);
        $tableName = (new $searchableModel)->getTable();
        $description = (new DescribeTableService)->describeTable($tableName);

        return Inertia::render('Search/Show', [
            'data' => $result->items(),
            'paginator' => $result->toArray(),
            'model' => $searchableModel,
            'table' => $tableName,
            'plural' => Str::title($tableName),
            'description' => $description,
        ]);
    }
}
