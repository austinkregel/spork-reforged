<?php

declare(strict_types=1);

use Laravel\Pennant\Feature;

Route::redirect('/login', '/flight/login');

Route::domain('pending.download')
//    ->middleware('throttle:5')
    ->withoutMiddleware(['web'])
    ->group(base_path('routes/pages/deploy.php'));


Route::prefix('api')
    ->domain(config('app.env') == 'production' ? 'spork.zone' : 'spork.localhost')
    ->middleware(config('jetstream.middleware', ['web']))
    ->group(base_path('routes/crud.php'));

Route::middleware('web')
    ->domain(config('app.env') == 'production' ? 'petoskey.today' : 'petoskey.localhost')
    ->group(base_path('routes/pages/petoskey.php'));

Route::middleware('web')
    ->domain(config('app.env') == 'production' ? 'spork.zone' : 'spork.localhost')
    ->group(base_path('routes/pages/spork.php'));

if (!empty($linkShorteningDomain = env('LINK_SHORTENING_DOMAIN', ''))) {
    Route::domain($linkShorteningDomain)
        ->group(base_path('routes/pages/link-shortening.php'));
}

Route::domain(
    config('app.env') == 'production'
        ? 'starting.host'
        : 'domains.localhost'
)
    ->group(base_path('routes/pages/domains.php'));
