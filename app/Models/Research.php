<?php

declare(strict_types=1);

namespace App\Models;

use App\Events\Models\Research\ResearchCreated;
use App\Events\Models\Research\ResearchCreating;
use App\Events\Models\Research\ResearchDeleted;
use App\Events\Models\Research\ResearchDeleting;
use App\Events\Models\Research\ResearchUpdated;
use App\Events\Models\Research\ResearchUpdating;
use App\Models\Traits\ScopeQSearch;
use App\Models\Traits\ScopeRelativeSearch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Research extends Model implements Crud
{
    use HasFactory;
    use LogsActivity;
    use ScopeQSearch;
    use ScopeRelativeSearch;
    use Searchable;

    public $fillable = ['topic', 'notes', 'sources'];

    public $dispatchesEvents = [
        'created' => ResearchCreated::class,
        'creating' => ResearchCreating::class,
        'deleting' => ResearchDeleting::class,
        'deleted' => ResearchDeleted::class,
        'updating' => ResearchUpdating::class,
        'updated' => ResearchUpdated::class,
    ];

    protected function casts(): array
    {
        return [
            'sources' => 'json',
        ];
    }

    public function projects(): MorphToMany
    {
        return $this->morphToMany(
            Project::class,
            'resource',
            'project_resources'
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['topic', 'notes', 'sources'])
            ->useLogName('research')
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }
}
