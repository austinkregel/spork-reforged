<?php

declare(strict_types=1);

namespace App\Http\Controllers\Spork;

use App\Contracts\ModelQuery;
use App\Http\Requests\Dynamic\CreateRequest;
use App\Http\Requests\Dynamic\DeleteRequest;
use App\Http\Requests\Dynamic\ForceDeleteRequest;
use App\Http\Requests\Dynamic\IndexRequest;
use App\Http\Requests\Dynamic\RestoreRequest;
use App\Http\Requests\Dynamic\UpdateRequest;
use App\Http\Requests\Dynamic\ViewRequest;
use App\Models\Crud;
use App\Models\Taggable;
use App\Services\ActionFilter;
use App\Services\Code;
use App\Services\Development\DescribeTableService;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\QueryBuilder\AllowedFilter as Filter;
use Spatie\QueryBuilder\QueryBuilder;

class LocalAdminController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    public function fields(IndexRequest $request)
    {
        return response()->json((new DescribeTableService)->describe($this->getModel($request)));
    }

    protected function getModel(Request $request)
    {
        $split = array_filter(explode('/', $request->path()), fn ($part) => ! is_numeric($part));

        $tableFromUrl = match (count($split)) {
            3 => $split[2],
            4 => $split[2],

            default => abort(404, 'No model found for path'),
        };

        $models = array_reduce(Code::instancesOf(Crud::class)->getClasses(), fn ($all, $class) => array_merge(
            $all,
            [(new $class)->getTable() => $class]
        ), []);

        return $models[$tableFromUrl];
    }

    /**
     * @throws Exception
     */
    public function index(IndexRequest $request)
    {
        $class = $this->getModel($request);
        $model = new $class;
        $action = new ActionFilter(request()->get('action', 'paginate:14'));

        $description = (new DescribeTableService)->describe($model);

        $query = QueryBuilder::for($class)
            ->allowedFields($description['fields'])
            ->allowedFilters(array_merge([
                Filter::scope('q'),
                Filter::scope('relative'),
            ], $description['filters']))
            ->allowedIncludes($description['includes'])
            ->allowedSorts($description['sorts']);

        return $action->execute($query);
    }

    public function store(CreateRequest $request)
    {
        $model = $this->getModel($request);
        $description = (new DescribeTableService)->describe(new $model);

        $request->validate(array_reduce($description['required'], fn ($all, $field) => array_merge(
            $all,
            [$field => 'required']
        ), []));
        /** @var ModelQuery $resource */
        $resource = new $model;
        $resource->fill($request->all());
        $resource->save();

        return $resource->refresh();
    }

    public function show(ViewRequest $request, $abstractEloquentModel = null)
    {
        $query = QueryBuilder::for($this->getModel($request));

        return $query->find($abstractEloquentModel) ?? response([
            'message' => 'No resource found by that id.',
        ], 414);
    }

    public function tag(UpdateRequest $request, $abstractEloquentModel = null)
    {
        $request->validate([
            'tags' => 'required|array',
            'tags.*' => 'exists:tags,id',
        ]);
        $model = QueryBuilder::for($this->getModel($request))->findOrFail($abstractEloquentModel);

        if (! ($model instanceof Taggable)) {
            abort(414, 'This model does not support tags.');

            return;
        }

        $model->attachTags(
            array_map(fn ($tagId) => \App\Models\Tag::find($tagId), $request->input('tags'))
        );

        return response()->json($model);
    }

    public function update(UpdateRequest $request, $abstractEloquentModel = null)
    {
        $modelClass = $this->getModel($request);
        $abstractEloquentModel = $modelClass::findOrFail($abstractEloquentModel);
        $abstractEloquentModel->update($request->all());

        return $abstractEloquentModel->refresh();
    }

    public function destroy(DeleteRequest $request, $abstractEloquentModel)
    {
        $modelClass = $this->getModel($request);
        $abstractEloquentModel = $modelClass::findOrFail($abstractEloquentModel);
        $abstractEloquentModel->delete();

        return response('', 204);
    }

    public function destroyMany(DeleteRequest $request, $abstractEloquentModel)
    {
        $modelClass = $this->getModel($request);
        $abstractEloquentModel = $modelClass::findOrFail($abstractEloquentModel);
        $abstractEloquentModel->delete();

        return response('', 204);
    }

    public function forceDestroy(ForceDeleteRequest $request, $abstractEloquentModel)
    {
        $modelClass = $this->getModel($request);
        $abstractEloquentModel = $modelClass::findOrFail($abstractEloquentModel);

        $model = new $modelClass;

        if (! $model->usesSoftdeletes()) {
            abort(404, 'You cannot force delete an item of this type.');

            return;
        }

        $abstractEldoquentModel->forceDelete();

        return response('', 204);
    }

    public function restore(RestoreRequest $request, $model, $abstractEloquentModel)
    {
        $modelClass = $this->getModel($request);
        $abstractEloquentModel = $modelClass::findOrFail($abstractEloquentModel);

        if (! $abstractEloquentModel->usesSoftdeletes()) {
            abort(404, 'You cannot restore an item of this type.');

            return;
        }

        $abstractEloquentModel->restore();

        return $abstractEloquentModel->refresh();
    }
}
