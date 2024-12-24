<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\ModelQuery;
use App\Events\Models\DomainRecord\DomainRecordCreated;
use App\Events\Models\DomainRecord\DomainRecordCreating;
use App\Events\Models\DomainRecord\DomainRecordDeleted;
use App\Events\Models\DomainRecord\DomainRecordDeleting;
use App\Events\Models\DomainRecord\DomainRecordUpdated;
use App\Events\Models\DomainRecord\DomainRecordUpdating;
use App\Models\Traits\ScopeQSearch;
use App\Models\Traits\ScopeRelativeSearch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DomainRecord extends Model implements Crud, ModelQuery
{
    use HasFactory;
//    use LogsActivity;
    use ScopeQSearch;
    use ScopeRelativeSearch;
    use Searchable;

    protected $fillable = [
        'name',
        'type',
        'ttl',
        'content',
        'comment',
        'tags',
        'value',
        'timeout',
        'record_id',
        'proxied_through_cloudflare',
        'priority',
    ];

    public $dispatchesEvents = [
        'created' => DomainRecordCreated::class,
        'creating' => DomainRecordCreating::class,
        'deleting' => DomainRecordDeleting::class,
        'deleted' => DomainRecordDeleted::class,
        'updating' => DomainRecordUpdating::class,
        'updated' => DomainRecordUpdated::class,
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('domain-record');
    }
}
