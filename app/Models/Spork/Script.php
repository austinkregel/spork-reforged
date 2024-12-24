<?php

declare(strict_types=1);

namespace App\Models\Spork;

use App\Events\Models\Script\ScriptCreated;
use App\Events\Models\Script\ScriptCreating;
use App\Events\Models\Script\ScriptDeleted;
use App\Events\Models\Script\ScriptDeleting;
use App\Events\Models\Script\ScriptUpdated;
use App\Events\Models\Script\ScriptUpdating;
use App\Models\Crud;
use App\Models\Traits\ScopeQSearch;
use App\Models\Traits\ScopeRelativeSearch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Script extends Model implements Crud
{
    use HasFactory;
    use ScopeQSearch;
    use ScopeRelativeSearch;

    protected $fillable = [
        'name',
        'language',
        'script',
        'user_id',
    ];

    public $dispatchesEvents = [
        'created' => ScriptCreated::class,
        'creating' => ScriptCreating::class,
        'deleting' => ScriptDeleting::class,
        'deleted' => ScriptDeleted::class,
        'updating' => ScriptUpdating::class,
        'updated' => ScriptUpdated::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
