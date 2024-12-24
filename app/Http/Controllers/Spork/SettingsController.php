<?php

declare(strict_types=1);

namespace App\Http\Controllers\Spork;

use Inertia\Inertia;
use PhpParser\Lexer\Emulative;
use PhpParser\ParserFactory;
use Winter\LaravelConfigWriter\ArrayFile;
class SettingsController
{
    public function __invoke()
    {
        $path = base_path(request('path'));
        $configs = array_values(array_filter(scandir(config_path()), fn ($value) => str_ends_with($value, '.php')));
        $path = config_path('app.php');
        // settings are things that can be configured in between requests.
        // They cannot be changed at run time, and might even require a restart of the servers.
        // They are editable here as a convenience, but should be committed to version control
        // and use the respective environment variables and not the actual secrets.
        return Inertia::render('Settings/Index', [
            'title' => 'Settings',
            'settings' => new class([
                'activitylog' => [
                    'enabled' => config('activitylog.enabled'),
                    'delete_records_older_than_days' => config('activitylog.delete_records_older_than_days'),
                    'default_log_name' => config('activitylog.default_log_name'),
                ],
                'app' => [
                    'name' => config('app.name'),
                    'env' => config('app.env'),
                    'debug' => config('app.debug'),
                    'url' => config('app.url'),
                    'timezone' => config('app.timezone'),
                    'locale' => config('app.locale'),
                    'fallback_locale' => config('app.fallback_locale'),
                ],
                'broadcasting' => [
                    'default' => config('broadcasting.default'),
                ],
                'database' => [
                    'default' => config('database.default'),
                ],
                'filesystems' => [
                    'default' => config('filesystems.default'),
                    'disks' => config('filesystems.disks'),
                ],

                'horizon' => array_merge(
                    config('horizon.defaults.'.array_keys(config('horizon.environments.'.config('app.env')))[0]),
                    config('horizon.environments.'.config('app.env'))
                ),
                'laravel-flight' => [
                    'prefix' => config('laravel-flight.prefix'),
                    'login_redirect' => config('laravel-flight.login_redirect'),
                    'driver' => config('laravel-flight.driver'),
                    'registration' => config('laravel-flight.registration'),
                    'post_login_redirect' => config('laravel-flight.post_login_redirect'),
                ],
                'mail' => config('mail.default'),
                'mailer' => config('mail.mailers.'.config('mail.default')),

                'pulse' => config('pulse.enabled'),

                'spork' => config('spork'),

                'telescope' => config('telescope.enabled'),
            ])
            {
                public function __construct(
                    public array $configs
                )
                {
                }
            },
            'config' => config('spork')
        ]);
    }
}
