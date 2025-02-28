<?php

declare(strict_types=1);

namespace App\Models\Finance;

use App\Events\Models\Account\AccountCreated;
use App\Events\Models\Account\AccountCreating;
use App\Events\Models\Account\AccountDeleted;
use App\Events\Models\Account\AccountDeleting;
use App\Events\Models\Account\AccountUpdated;
use App\Events\Models\Account\AccountUpdating;
use App\Models\Credential;
use App\Models\Crud;
use App\Models\Traits\ScopeQSearch;
use App\Models\Traits\ScopeRelativeSearch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Account extends Model implements Crud
{
    use HasFactory;
    use LogsActivity;
    use ScopeQSearch;
    use ScopeRelativeSearch;
    use Searchable;

    protected $fillable = [
        'account_id',
        'mask',
        'name',
        'official_name',
        'balance',
        'available',
        'subtype',
        'type',
        'access_token_id',
    ];

    public $dispatchesEvents = [
        'created' => AccountCreated::class,
        'creating' => AccountCreating::class,
        'deleting' => AccountDeleting::class,
        'deleted' => AccountDeleted::class,
        'updating' => AccountUpdating::class,
        'updated' => AccountUpdated::class,
    ];

    public function credential(): BelongsTo
    {
        return $this->belongsTo(Credential::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('account')
            ->logFillable()
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }
}
