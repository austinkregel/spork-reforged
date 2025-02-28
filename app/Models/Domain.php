<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\ModelQuery;
use App\Events\Models\Domain\DomainCreated;
use App\Events\Models\Domain\DomainCreating;
use App\Events\Models\Domain\DomainDeleted;
use App\Events\Models\Domain\DomainDeleting;
use App\Events\Models\Domain\DomainUpdated;
use App\Events\Models\Domain\DomainUpdating;
use App\Models\Traits\HasProjectResource;
use App\Models\Traits\ScopeQSearch;
use App\Models\Traits\ScopeRelativeSearch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Tags\HasTags;

class Domain extends Model implements ModelQuery, Taggable, Crud
{
    use HasFactory;
    use HasProjectResource;
    use HasTags;
    use LogsActivity;
    use ScopeQSearch;
    use ScopeRelativeSearch;
    use Searchable;

    public $fillable = ['name', 'verification_key', 'cloudflare_id', 'domain_id', 'registered_at'];

    public $dispatchesEvents = [
        'created' => DomainCreated::class,
        'creating' => DomainCreating::class,
        'deleting' => DomainDeleting::class,
        'deleted' => DomainDeleted::class,
        'updating' => DomainUpdating::class,
        'updated' => DomainUpdated::class,
    ];

    // add a global scope to prevent the querying of expired domains
    protected static function booted()
    {
        static::addGlobalScope('active', function ($query) {
            $query->where('expires_at', '>', now());
        });
    }

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime:Y-m-d',
            'expires_at' => 'datetime:Y-m-d',
        ];
    }

    public function records(): HasMany
    {
        return $this->hasMany(DomainRecord::class);
    }

    public function credential(): BelongsTo
    {
        return $this->belongsTo(Credential::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'domain_id', 'registered_at'])
            ->useLogName('domain')
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }
}
