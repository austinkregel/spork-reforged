<?php

declare(strict_types=1);

namespace App\Models\Finance;

use App\Events\Models\Budget\BudgetCreated;
use App\Events\Models\Budget\BudgetCreating;
use App\Events\Models\Budget\BudgetDeleted;
use App\Events\Models\Budget\BudgetDeleting;
use App\Events\Models\Budget\BudgetUpdated;
use App\Events\Models\Budget\BudgetUpdating;
use App\Models\Crud;
use App\Models\Tag;
use App\Models\Taggable;
use App\Models\Traits\HasConditions;
use App\Models\Traits\ScopeQSearch;
use App\Models\Traits\ScopeRelativeSearch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Tags\HasTags;

class Budget extends Model implements Crud, Taggable
{
    use HasFactory;
    use HasTags;
    use ScopeQSearch;
    use ScopeRelativeSearch;

    protected $fillable = [
        'name',
        'amount',
        'user_id',
        'started_at',
        'frequency',
        'interval',
        'count',
        'breached_at',
    ];

    public $dispatchesEvents = [
        'created' => BudgetCreated::class,
        'creating' => BudgetCreating::class,
        'deleting' => BudgetDeleting::class,
        'deleted' => BudgetDeleted::class,
        'updating' => BudgetUpdating::class,
        'updated' => BudgetUpdated::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
