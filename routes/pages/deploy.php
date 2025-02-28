<?php

declare(strict_types=1);

use App\Models\Credential;

Route::middleware(['web', 'auth:sanctum'])->get('/register-device', function () {
    $ssh = request()->user()->credentials()->where('type', 'ssh')->firstOrFail();

    return response()->view('basement-scripts.link-server', [
        'credential' => $ssh,
    ], 200, [
        'Content-type' => 'text/text',
    ]);
})->name('register-device');

Route::get('/link/{identifier}', function ($identifier) {
    $ssh = Credential::query()
        ->where('api_key', $identifier)
        ->firstOrFail();

    return response()->view('basement-scripts.link-server', [
        'credential' => $ssh,
    ], 200, [
        'Content-type' => 'text/text',
    ]);
})->middleware('throttle:api')->name('link-device');

Route::middleware([
    'throttle:api',
    \App\Http\Middleware\ServerAccessable::class,
])->put('/.server/update/{identifier}', function () {
    /** @var \Laravel\Sanctum\PersonalAccessToken $token */
    $server = request()->get('server');
    $descriptor = new \App\Services\Development\DescribeTableService();

    $description = $descriptor->describe($server);

    $input = array_filter(request()->all($description['fillable']));

    \DB::transaction(fn () => $server->update($input));

    return response()->json($server->refresh(), 200, [
        'Content-type' => 'application/json',
    ]);
})->middleware('throttle:api')->name('server.update');

Route::middleware([
    'throttle:api',
    \App\Http\Middleware\ServerAccessable::class,
])->post('.server/services/{identifier}', function () {
    request()->validate([
        'service' => 'required',
    ]);
    /** @var \Laravel\Sanctum\PersonalAccessToken $token */
    $server = request()->get('server');

    $server->services()->create([
        'service' => request()->get('service'),
    ]);

    return response()->json($server->refresh(), 200, [
        'Content-type' => 'application/json',
    ]);
})->name('server.service');

Route::post('/api/servers', [
    App\Http\Controllers\Api\ServerController::class,
    'store'
])->name('server.create');

Route::post('register-device', function () {
    $data = request()->validate([
        'name' => 'required|string',
        'threads' => 'required|int',
        'memory' => 'required|int',
        'is_hypervisor' => 'boolean',
        'collect_metrics' => 'boolean',
        'uses_client' => 'boolean',
        'has_backup' => 'boolean',
        'is_powered_on' => 'boolean',
        'ip6_address' => 'string',
        'kernel' => 'string',
        'distro' => 'string',
        'boot_disk' => 'string',
        'cost_in_cents' => 'integer',
        'short_code' => 'required|exists:short_codes,short_code',
    ]);

    $code = \App\Models\ShortCode::query()
        ->with('user')
        ->where('short_code', request()->get('short_code'))
        ->where('is_enabled', true)
        ->firstOrFail();

    $ssh = \App\Services\SshService::factory(request()->ip(), request()->user());

    $server = \App\Models\Server::create(array_merge($data, [
        'credential_id' => $ssh->id,
        'server_id' => \Illuminate\Support\Str::random(),
    ]));

    $server->refresh();

    $ssh->load('user');

    $server->setAttribute('access_token', $ssh->user->createToken($server->name.' Access Token', []));
    $server->setAttribute('ssh_key_public', $ssh->getPublicKey());

    return $server;
})->name('create-device');
