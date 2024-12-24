<?php

declare(strict_types=1);

use App\Http\Controllers;

Route::get('/', Controllers\Domains\SearchController::class)->name('domain.search');
