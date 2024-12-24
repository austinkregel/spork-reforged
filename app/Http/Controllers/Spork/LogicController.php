<?php

declare(strict_types=1);

namespace App\Http\Controllers\Spork;

use Inertia\Inertia;

class LogicController
{
    public function __invoke()
    {
        return Inertia::render('Logic/Index', [
            //            'container_bindings' => \App\Services\Programming\LaravelProgrammingStyle::findContainerBindings(),
            'events' => \App\Services\Programming\LaravelProgrammingStyle::findLogicalEvents(),
            'listeners' => \App\Services\Programming\LaravelProgrammingStyle::findLogicalListeners(),
        ]);
    }
}
