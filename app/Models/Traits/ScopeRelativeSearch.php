<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Finance\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends \Eloquent
 */
trait ScopeRelativeSearch
{
    public function scopeRelative(Builder $query, string $string): void
    {
        switch ($string) {
            case 'user':
                if (auth()->check()) {
                    if (get_class($this) === Transaction::class) {
                        $query->whereHas('account', function (Builder $query) {
                            $query->whereHas('credential', function (Builder $query) {
                                $query->where('user_id', auth()->id());
                            });
                        });
                    } elseif (method_exists($this, 'credential')) {
                        $query->whereHas('credential', function (Builder $query) {
                            $query->where('user_id', auth()->id());
                        });
                    } elseif (method_exists($this, 'users')) {
                        $query->whereHas('users', function (Builder $query) {
                            $query->where('id', auth()->id());
                        });
                    } elseif (method_exists($this, 'user')) {
                        $query->where('user_id', auth()->id());
                    } elseif (method_exists($this, 'team')) {
                        $query->whereHas('team', function (Builder $query) {
                            $query->where('user_id', auth()->id());
                        });
                    } elseif (method_exists($this, 'owner')) {
                        $query->where('owner_id', auth()->id())
                            ->where('owner_type', User::class);
                    } elseif (method_exists($this, 'participants')) {
                        $query->whereHas('participants', function (Builder $query) {
                            $query->where('person_id', auth()->user()->person()->id);
                        });
                    } elseif (method_exists($this, 'projects')) {
                        $query->whereHas('projects.team', function (Builder $query) {
                            $query->where('user_id', auth()->id());
                        });
                    } else {
                        abort(400, 'No user relation found for model');
                    }
                }

                break;
            default:
                abort(404, 'Not Found');
        }
    }
}
