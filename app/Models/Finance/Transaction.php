<?php

declare(strict_types=1);

namespace App\Models\Finance;

use App\Contracts\ModelQuery;
use App\Events\Models\Transaction\TransactionCreated;
use App\Events\Models\Transaction\TransactionCreating;
use App\Events\Models\Transaction\TransactionDeleted;
use App\Events\Models\Transaction\TransactionDeleting;
use App\Events\Models\Transaction\TransactionUpdated;
use App\Events\Models\Transaction\TransactionUpdating;
use App\Models\Credential;
use App\Models\Crud;
use App\Models\Taggable;
use App\Models\Traits\ScopeQSearch;
use App\Models\Traits\ScopeRelativeSearch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Laravel\Scout\Searchable;
use Spatie\Tags\HasTags;

class Transaction extends Model implements Crud, ModelQuery, Taggable
{
    use HasFactory;
    use HasTags;
    use ScopeQSearch;
    use ScopeRelativeSearch;
    use Searchable;

    public $fillable = [
        'name',
        'amount',
        'account_id',
        'date',
        'pending',
        'category_id',
        'transaction_id',
        'transaction_type',
        'personal_finance_category',
        'personal_finance_category_detailed',
        'personal_finance_icon',
        'seller_icon',
        'data',
    ];

    public $dispatchesEvents = [
        'created' => TransactionCreated::class,
        'creating' => TransactionCreating::class,
        'deleting' => TransactionDeleting::class,
        'deleted' => TransactionDeleted::class,
        'updating' => TransactionUpdating::class,
        'updated' => TransactionUpdated::class,
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'date' => 'datetime',
            'pending' => 'boolean',
            'data' => 'json',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id', 'account_id');
    }

    public function credentials(): HasManyThrough
    {
        return $this->hasManyThrough(
            Credential::class,
            Account::class,
            'credential_id',
            'id',
            'account_id',
            'account_id'
        );
    }
}
