<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Staudenmeir\EloquentJsonRelations\HasJsonRelationships;

class Email extends Model
{
    use HasFactory, HasJsonRelationships;

    protected $fillable = [
        'email_id',
        'to',
        'to_email',
        'from',
        'from_email',
        'credential_id',
        'subject',
        'sent_at',
        'seen',
        'spam',
        'answered',
        'message',
    ];
    protected function casts(): array
    {
        return [
            'seen' => 'boolean',
            'spam' => 'boolean',
            'answered' => 'boolean',
            'message' => 'boolean',
        ];
    }

    public function fromPerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'from');
    }

    public function toPerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'to');
    }

    public function from()
    {
        return $this->hasManyJson(Person::class, 'emails', 'from_email');
    }

    public function to()
    {
        return $this->hasManyJson(Person::class, 'emails', 'to_email');
    }

    public function credential()
    {
        return $this->belongsTo(Credential::class, 'credential_id');
    }
}
