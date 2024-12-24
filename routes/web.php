<?php

declare(strict_types=1);

use Laravel\Pennant\Feature;

Route::redirect('/login', '/flight/login');

if (Feature::driver('array')->active(\App\Features\Automatic\GeneratedPages::class)) {
    \App\Models\Deployment::query()
        ->with([
            'project',
            'domains',
            'servers',
        ])
        ->get()
        ->map(function (\App\Models\Deployment $deployment) {
            foreach ($deployment->domains as $domain) {
                Route::middleware('web')
                    ->domain($domain->domain)
                    ->get('{part?}/{part2?}/{part3?}/{part4?}', function () use ($deployment) {
                        return view('deploy', [
                            'deployment' => $deployment,
                        ]);
                    });
            }
        });
}

if (Feature::driver('array')->active(\App\Features\Automatic\ServerLinking::class)) {
    Route::domain('pending.download')
//    ->middleware('throttle:5')
        ->withoutMiddleware(['web'])
        ->group(base_path('routes/pages/deploy.php'));
}

if (Feature::driver('array')->active(\App\Features\Automatic\Crud::class)) {
    Route::prefix('api')
        ->domain(config('app.env') == 'production' ? 'spork.zone' : 'spork.localhost')
        ->middleware(config('jetstream.middleware', ['web']))
        ->group(base_path('routes/crud.php'));
}

if (Feature::driver('array')->active(\App\Features\PetoskeyToday::class)) {
    Route::middleware('web')
        ->domain(config('app.env') == 'production' ? 'petoskey.today' : 'petoskey.localhost')
        ->group(base_path('routes/pages/petoskey.php'));
}

if (Feature::driver('array')->active(\App\Features\SporkApp::class)) {
    Route::middleware('web')
        ->domain(config('app.env') == 'production' ? 'spork.zone' : 'spork.localhost')
        ->group(base_path('routes/pages/spork.php'));
}

if (Feature::driver('array')->active(\App\Features\LinkShortening::class)) {
    if (!empty($linkShorteningDomain = env('LINK_SHORTENING_DOMAIN', ''))) {
        Route::domain($linkShorteningDomain)
            ->group(base_path('routes/pages/link-shortening.php'));
    }
}

if (Feature::driver('array')->active(\App\Features\Domains::class)) {
    Route::domain(
        config('app.env') == 'production'
            ? 'starting.host'
            : 'domains.localhost'
    )
        ->group(base_path('routes/pages/domains.php'));
}
