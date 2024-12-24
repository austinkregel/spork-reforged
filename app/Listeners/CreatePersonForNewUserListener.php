<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\Models\User\UserCreated;
use App\Models\Person;

class CreatePersonForNewUserListener
{
    public function handle(UserCreated $userEvent): void
    {
        $user = $userEvent->model;
        $person = new Person;
        $person->user_id = $user->id;
        $person->name = $user->name;
        $person->emails = [$user->email];
        $person->names = [$user->name];
        $person->save();
    }
}
