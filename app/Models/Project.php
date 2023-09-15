<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\ModelQuery;
use App\Services\SshKeyGeneratorService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Tags\HasTags;

class Project extends Model implements ModelQuery
{
    use HasFactory, HasTags, LogsActivity;

    public $guarded = [];

    protected $casts = ['settings' => 'json'];

    public function scopeQ(Builder $query, string $string): void
    {
        $query->where('name', 'like', '%'.$string.'%');
    }

    public function domains(): MorphToMany
    {
        return $this->morphedByMany(
            Domain::class,
            'resource',
            'project_resources'
        );
    }

    public function servers(): MorphToMany
    {
        return $this->morphedByMany(
            Server::class,
            'resource',
            'project_resources'
        );
    }

    public function research(): MorphToMany
    {
        return $this->morphedByMany(
            Research::class,
            'resource',
            'project_resources'
        );
    }

    public function pages(): MorphToMany
    {
        return $this->morphedByMany(
            Page::class,
            'resource',
            'project_resources'
        );
    }

    public function credentials(): MorphToMany
    {
        return $this->morphedByMany(
            Credential::class,
            'resource',
            'project_resources'
        );
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function credentialFor(string $service): ?Credential
    {
        $credential = $this->credentials()->where('service', $service)->first();

        if (! $credential) {
            if ($service === Credential::TYPE_SSH) {
                $randomName = Str::random(16);

                $generatorService = new SshKeyGeneratorService(
                    privateKeyFile: $privateKeyFile = storage_path('app/keys/'.$randomName.'.key'),
                    publicKeyFile: $publicKeyFile = storage_path('app/keys/'.$randomName.'.pub'),
                    passKey: $passKey = ''//''tr::random(16),
                );

                $credential = $this->credentials()->create([
                    'service' => Credential::TYPE_SSH,
                    'type' => Credential::TYPE_SSH,
                    'name' => 'Forge',
                    'user_id' => 1,
                    'settings' => [
                        'pub_key' => $generatorService->getPublicKey(),
                        'pub_key_file' => $publicKeyFile,
                        'private_key' => $generatorService->getPrivateKey(),
                        'private_key_file' => $privateKeyFile,
                        'pass_key' => encrypt($passKey),
                    ],
                ]);

                return $credential;
            }

            throw new \Exception('No credential found for '.$service);
        }

        return $credential;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'team_id'])
            ->useLogName('project')
            ->logOnlyDirty();
    }
}
