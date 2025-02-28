<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\ModelQuery;
use App\Events\Models\User\UserCreated;
use App\Events\Models\User\UserCreating;
use App\Events\Models\User\UserDeleted;
use App\Events\Models\User\UserDeleting;
use App\Events\Models\User\UserUpdated;
use App\Events\Models\User\UserUpdating;
use App\Models\Finance\Account;
use App\Models\Traits\HasProjectResource;
use App\Models\Traits\ScopeQSearch;
use App\Models\Traits\ScopeRelativeSearch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Tags\HasTags;

class User extends Authenticatable implements ModelQuery, Taggable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasProjectResource;
    use HasRoles;
    use HasPermissions;
    use HasTags;
    use LogsActivity;
    use Notifiable;
    use ScopeQSearch;
    use ScopeRelativeSearch;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['password', 'remember_token', 'two_factor_recovery_codes', 'two_factor_secret'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['profile_photo_url'];

    public $dispatchesEvents = [
        'created' => UserCreated::class,
        'creating' => UserCreating::class,
        'deleting' => UserDeleting::class,
        'deleted' => UserDeleted::class,
        'updating' => UserUpdating::class,
        'updated' => UserUpdated::class,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }
    public function personalProjects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email'])
            ->useLogName('user')
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }

    public function shortCodes(): HasMany
    {
        return $this->hasMany(ShortCode::class);
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(Credential::class);
    }

    public function domains(): HasManyThrough
    {
        return $this->hasManyThrough(Domain::class, Credential::class);
    }

    public function accounts(): HasManyThrough
    {
        return $this->hasManyThrough(Account::class, Credential::class);
    }

    public function messages(): HasManyThrough
    {
        return $this->hasManyThrough(Message::class, Credential::class)->orderByDesc('originated_at');
    }

    public function emails(): HasManyThrough
    {
        return $this->hasManyThrough(Email::class, Credential::class)->orderByDesc('originated_at');
    }

    public function servers(): HasManyThrough
    {
        return $this->hasManyThrough(Server::class, Credential::class);
    }

    public function externalRssFeeds(): MorphMany
    {
        return $this->morphMany(ExternalRssFeed::class, 'owner');
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'email', 'primary_email');
    }

    public function budgets()
    {
        return $this->hasMany(Finance\Budget::class);
    }
}
