<?php

declare(strict_types=1);

use App\Models\Crud;
use App\Services\Code;
use Illuminate\Support\Facades\Route;

if (! function_exists('developerRoute')) {
    function developerRoute($name, $model)
    {
        cache()->rememberForever($name, fn () => $model);

        Route::prefix('crud')->group(function () use ($name) {
            Route::get($name, [\App\Http\Controllers\Spork\LocalAdminController::class, 'index'])->name($name.'.index');

            Route::post($name.'', [\App\Http\Controllers\Spork\LocalAdminController::class, 'store'])->name($name.'.store');
            Route::get($name.'/fields', [\App\Http\Controllers\Spork\LocalAdminController::class, 'fields'])->name($name.'.fields');
            Route::get($name.'/{'.$name.'}', [\App\Http\Controllers\Spork\LocalAdminController::class, 'show'])->name($name.'.show');
            // Updating
            Route::put($name.'/{'.$name.'}', [\App\Http\Controllers\Spork\LocalAdminController::class, 'update'])->name($name.'.update');
            Route::patch($name.'/{'.$name.'}', [\App\Http\Controllers\Spork\LocalAdminController::class, 'update']);
            // Restoring
            Route::post($name.'/{'.$name.'}/restore', [\App\Http\Controllers\Spork\LocalAdminController::class, 'restore'])->name($name.'.restore');
            // Soft-deleting
            Route::delete($name.'/{'.$name.'}', [\App\Http\Controllers\Spork\LocalAdminController::class, 'destroy'])->name($name.'.destroy');
            Route::delete($name.'/{'.$name.'}/many', [\App\Http\Controllers\Spork\LocalAdminController::class, 'destroyMany'])->name($name.'.destroyMany');
            // Force delete
            Route::delete($name.'/{'.$name.'}/force', [\App\Http\Controllers\Spork\LocalAdminController::class, 'forceDestroy'])->name($name.'.forceDestroy');
            Route::post($name.'/{'.$name.'}/tags', [\App\Http\Controllers\Spork\LocalAdminController::class, 'tag'])->name($name.'.tags');
        });
    }
}

$instances = Code::instancesOf(Crud::class)->getClasses();

foreach ($instances as $class) {
    $model = new $class;
    $name = $model->getTable();

    developerRoute($name, $class);
}
