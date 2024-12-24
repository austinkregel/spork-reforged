<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\ModelQuery;
use App\Models\Traits\ScopeQSearch;
use App\Models\Traits\ScopeRelativeSearch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServerService extends Model implements ModelQuery
{
    use HasFactory;
    use ScopeQSearch;
    use ScopeRelativeSearch;

    protected $fillable = [
        'service',
        'server_id',
        'version',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];
}
