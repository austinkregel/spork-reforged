<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\ModelQuery;
use App\Events\Models\Server\ServerCreated;
use App\Events\Models\Server\ServerCreating;
use App\Events\Models\Server\ServerDeleted;
use App\Events\Models\Server\ServerDeleting;
use App\Events\Models\Server\ServerUpdated;
use App\Events\Models\Server\ServerUpdating;
use App\Models\Traits\HasOwner;
use App\Models\Traits\ScopeQSearch;
use App\Models\Traits\ScopeRelativeSearch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Tags\HasTags;

class Server extends Model implements ModelQuery, Taggable, Crud
{
    use HasApiTokens;
    use HasFactory;
    use HasTags;
    use HasOwner;
    use LogsActivity;
    use ScopeQSearch;
    use ScopeRelativeSearch;

    public $fillable = [
        'server_id',
        'name',
        'vcpu',
        'memory',
        'status',
        'disk',
        'cost_per_hour',
        'ip_address',
        'ip_address_v6',
        'internal_ip_address',
        'internal_ip_address_v6',
        'internal_url',
        'last_ping_at',
        'booted_at',
        'turned_off_at',
        'os',
    ];

    public $dispatchesEvents = [
        'created' => ServerCreated::class,
        'creating' => ServerCreating::class,
        'deleting' => ServerDeleting::class,
        'deleted' => ServerDeleted::class,
        'updating' => ServerUpdating::class,
        'updated' => ServerUpdated::class,
    ];

    public $casts = [
        'turned_off_at' => 'datetime',
        'booted_at' => 'datetime',
    ];

    public function credential(): BelongsTo
    {
        return $this->belongsTo(Credential::class);
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
            ->logOnly(['name', 'vcpu', 'memory', 'disk', 'cost_per_hour', 'internal_url', 'last_ping_at', 'booted_at', 'turned_off_at', 'os'])
            ->useLogName('server')
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }

    public function services(): HasMany
    {
        return $this->hasMany(ServerService::class);
    }
}
