<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\ScopeQSearch;
use App\Models\Traits\ScopeRelativeSearch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Asset extends Model implements Crud
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;
    use ScopeQSearch;
    use ScopeRelativeSearch;

    protected $fillable = [
        'id',
        'name',
        'type',
        'group',
        'location',
        'description',
        'acquired_at',
        'order_id',
        'shipped_at',
        'delivered_at',
        'returned_at',
        'return_tracking_number',
        'tracking_number',
        'status',
        'condition',
        'meta',
        'owner_id',
        'owner_type',
    ];

    protected $casts = [
        'acquired_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'returned_at' => 'datetime',
        'meta' => 'array',
    ];

    public function owner(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->dontSubmitEmptyLogs()
            ->logOnly(['name', 'type', 'location', 'description', 'acquired_at', 'order_id', 'shipped_at', 'delivered_at', 'returned_at', 'return_tracking_number', 'tracking_number', 'status', 'condition', 'meta']);
    }
}
