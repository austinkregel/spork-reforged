<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\ModelQuery;
use App\Events\Models\Person\PersonCreated;
use App\Events\Models\Person\PersonCreating;
use App\Events\Models\Person\PersonDeleted;
use App\Events\Models\Person\PersonDeleting;
use App\Events\Models\Person\PersonUpdated;
use App\Events\Models\Person\PersonUpdating;
use App\Models\Traits\ScopeRelativeSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Person extends Model implements Crud, ModelQuery
{
    use HasFactory;
    use ScopeRelativeSearch;
    use Searchable;

    public $guarded = [];

    public $dispatchesEvents = [
        'created' => PersonCreated::class,
        'creating' => PersonCreating::class,
        'deleting' => PersonDeleting::class,
        'deleted' => PersonDeleted::class,
        'updating' => PersonUpdating::class,
        'updated' => PersonUpdated::class,
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'phone_numbers' => 'array',
            'addresses' => 'array',
            'emails' => 'array',
            'names' => 'array',
            'identifiers' => 'array',
            'locality' => 'array',
            'jobs' => 'array',
            'education' => 'array',
        ];
    }

    public function scopeQ(Builder $query, string $string): void
    {
        $query->where('name', 'like', '%'.$string.'%');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
