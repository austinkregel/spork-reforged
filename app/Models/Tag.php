<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\Conditionable;
use App\Contracts\ModelQuery;
use App\Events\Models\Tag\TagCreated;
use App\Events\Models\Tag\TagCreating;
use App\Events\Models\Tag\TagDeleted;
use App\Events\Models\Tag\TagDeleting;
use App\Events\Models\Tag\TagUpdated;
use App\Events\Models\Tag\TagUpdating;
use App\Models\Finance\Account;
use App\Models\Finance\Budget;
use App\Models\Finance\Transaction;
use App\Models\Traits\HasConditions;
use App\Models\Traits\ScopeQSearch;
use App\Models\Traits\ScopeRelativeSearch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * @property bool $must_all_conditions_pass
 */
class Tag extends \Spatie\Tags\Tag implements Conditionable, Crud, ModelQuery
{
    use HasConditions;
    use HasFactory;
    use ScopeQSearch;
    use ScopeRelativeSearch;

    public $fillable = ['name', 'slug', 'must_all_conditions_pass', 'type', 'order_column'];

    public $casts = ['name' => 'json'];

    public $dispatchesEvents = [
        'created' => TagCreated::class,
        'creating' => TagCreating::class,
        'deleting' => TagDeleting::class,
        'deleted' => TagDeleted::class,
        'updating' => TagUpdating::class,
        'updated' => TagUpdated::class,
    ];

    // all related models, regardless of type
    public function tagged(): Builder
    {
        return DB::table('taggables')
            ->where('tag_id', $this->id);
    }

    public function articles(): MorphToMany
    {
        return $this->morphedByMany(Article::class, 'taggable');
    }

    public function feeds(): MorphToMany
    {
        return $this->morphedByMany(ExternalRssFeed::class, 'taggable');
    }

    public function servers(): MorphToMany
    {
        return $this->morphedByMany(Server::class, 'taggable');
    }

    public function transactions(): MorphToMany
    {
        return $this->morphedByMany(Transaction::class, 'taggable');
    }

    public function projects(): MorphToMany
    {
        return $this->morphedByMany(Project::class, 'taggable');
    }

    public function budgets(): MorphToMany
    {
        return $this->morphedByMany(Budget::class, 'taggable');
    }

    public function accounts(): MorphToMany
    {
        return $this->morphedByMany(Account::class, 'taggable');
    }

    public function domains(): MorphToMany
    {
        return $this->morphedByMany(Domain::class, 'taggable');
    }

    public function people(): MorphToMany
    {
        return $this->morphedByMany(Person::class, 'taggable');
    }

    public function messages(): MorphToMany
    {
        return $this->morphedByMany(Message::class, 'taggable');
    }
}
