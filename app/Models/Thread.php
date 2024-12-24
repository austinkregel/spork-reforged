<?php

declare(strict_types=1);

namespace App\Models;

use App\Events\Models\Thread\ThreadCreated;
use App\Events\Models\Thread\ThreadCreating;
use App\Events\Models\Thread\ThreadDeleted;
use App\Events\Models\Thread\ThreadDeleting;
use App\Events\Models\Thread\ThreadUpdated;
use App\Events\Models\Thread\ThreadUpdating;
use App\Models\Traits\ScopeQSearch;
use App\Models\Traits\ScopeRelativeSearch;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Thread extends Model implements Crud
{
    use HasFactory;
    use ScopeQSearch;
    use ScopeRelativeSearch;
    use Searchable;

    public $guarded = [];

    public $casts = ['settings' => 'json', 'origin_server_ts' => 'datetime'];

    public $appends = ['human_timestamp'];

    public $dispatchesEvents = [
        'created' => ThreadCreated::class,
        'creating' => ThreadCreating::class,
        'deleting' => ThreadDeleting::class,
        'deleted' => ThreadDeleted::class,
        'updating' => ThreadUpdating::class,
        'updated' => ThreadUpdated::class,
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'thread_participants');
    }

    public function getHumanTimestampAttribute()
    {
        $date = $this->origin_server_ts;

        return $date ? Carbon::parse($date)->diffForHumans(now(), Carbon::DIFF_RELATIVE_TO_NOW) : null;
    }
}
