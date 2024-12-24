<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\ModelQuery;
use App\Events\Models\Project\ProjectCreated;
use App\Events\Models\Project\ProjectCreating;
use App\Events\Models\Project\ProjectDeleted;
use App\Events\Models\Project\ProjectDeleting;
use App\Events\Models\Project\ProjectUpdated;
use App\Events\Models\Project\ProjectUpdating;
use App\Models\Traits\ScopeQSearch;
use App\Models\Traits\ScopeRelativeSearch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Tags\HasTags;

class Project extends Model implements Crud, ModelQuery, Taggable
{
    use HasFactory;
    use HasTags;
    use LogsActivity;
    use ScopeQSearch;
    use ScopeRelativeSearch;

    public $guarded = [];

    public $dispatchesEvents = [
        'created' => ProjectCreated::class,
        'creating' => ProjectCreating::class,
        'deleting' => ProjectDeleting::class,
        'deleted' => ProjectDeleted::class,
        'updating' => ProjectUpdating::class,
        'updated' => ProjectUpdated::class,
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'json',
        ];
    }

    public function research(): MorphToMany
    {
        return $this->morphedByMany(
            Research::class,
            'resource',
            'project_resources'
        );
    }

    public function pages(): MorphToMany
    {
        return $this->morphedByMany(
            Page::class,
            'resource',
            'project_resources'
        );
    }

    /**
     * These are credentials that are explicitly assigned to the project.
     */
    public function credentials(): MorphToMany
    {
        return $this->morphedByMany(
            Credential::class,
            'resource',
            'project_resources'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function credentialFor(string $service): ?Credential
    {
        $credential = $this->credentials()->where('service', $service)->first();

        return $credential;
    }

    public function owner(): MorphMany
    {
        return $this->morphMany(User::class, 'owner');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'team_id'])
            ->useLogName('project')
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }
}
