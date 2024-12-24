<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\CredentialRepositoryContract;
use App\Contracts\Services\Documents\PdfParserServiceContract;
use App\Contracts\Services\ImapServiceContract;
use App\Contracts\Services\JiraServiceContract;
use App\Contracts\Services\NamecheapServiceContract;
use App\Contracts\Services\News\NewsServiceContract;
use App\Contracts\Services\PlaidServiceContract;
use App\Contracts\Services\WeatherServiceContract;
use App\Features;
use App\Models\Credential;
use App\Models\Domain;
use App\Models\Navigation;
use App\Models\Page;
use App\Models\Person;
use App\Models\Project;
use App\Models\Research;
use App\Models\Server;
use App\Models\Spork\Script;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use App\Observers\ApplyCredentialsObserver;
use App\Operations\Operator;
use App\Repositories\CredentialRepository;
use App\Services\Code;
use App\Services\Documents\PdfParserService;
use App\Services\Finance\PlaidService;
use App\Services\JiraService;
use App\Services\Messaging\ImapCredentialService;
use App\Services\News\NewsService;
use App\Services\Registrar\NamecheapService;
use App\Services\Weather\OpenWeatherService;
use App\Spork;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Pennant\Feature;
use Spatie\Permission\Models\Role;
use Symfony\Component\Finder\SplFileInfo;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/-/dashboard';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        Credential::observe(ApplyCredentialsObserver::class);
        Domain::observe(ApplyCredentialsObserver::class);
        Navigation::observe(ApplyCredentialsObserver::class);
        Page::observe(ApplyCredentialsObserver::class);
        Person::observe(ApplyCredentialsObserver::class);
        Project::observe(ApplyCredentialsObserver::class);
        Research::observe(ApplyCredentialsObserver::class);
        Role::observe(ApplyCredentialsObserver::class);
        Script::observe(ApplyCredentialsObserver::class);
        Server::observe(ApplyCredentialsObserver::class);
        Task::observe(ApplyCredentialsObserver::class);
        Team::observe(ApplyCredentialsObserver::class);
        User::observe(ApplyCredentialsObserver::class);

        $this->app->bind(NewsServiceContract::class, NewsService::class);
        $this->app->bind(NamecheapServiceContract::class, NamecheapService::class);
        $this->app->bind(PlaidServiceContract::class, PlaidService::class);
        $this->app->bind(CredentialRepositoryContract::class, CredentialRepository::class);
        $this->app->bind(ImapServiceContract::class, ImapCredentialService::class);
        $this->app->bind(WeatherServiceContract::class, OpenWeatherService::class);
        $this->app->bind(JiraServiceContract::class, JiraService::class);
        $this->app->alias(Operator::class, 'operator');
        $this->app->bind(PdfParserServiceContract::class, PdfParserService::class);
        $this->app->singleton(Spork::class, fn () => new Spork());
    }

    public function boot(): void
    {
        foreach (config('spork.features') as $feature => $activate) {
            if ($activate) {
                Feature::for(auth()->user())->load($feature);
            }
        }
        $this->bootRoute();
    }

    public function bootRoute(): void
    {
        Route::macro('domains', function (array $domains, $callback) {
            foreach ($domains as $domain) {
                Route::domain($domain)
                    ->name($domain)
                    ->group($callback);
            }
            return $this;
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
        Route::bind('abstract_model', function ($tableName) {
            return collect(app(Filesystem::class)->allFiles(app_path('Models')))->filter(fn (SplFileInfo $file) => ! str_contains(strtolower($file->getRealPath()), 'trait'))->map(function (SplFileInfo $file) {
                $modelClass = ucfirst(str_replace('/', '\\', str_replace('.php', '', substr(str_replace(base_path(), '', $file->getRealPath()), 1))));

                return new $modelClass;
            })->filter(function ($model) use ($tableName) {
                return $model->getTable() === $tableName;
            })->first();
        });

        Route::bind('abstract_model_id', function ($value) {
            $class = request()->route('abstract_model');

            $model = new $class;

            return $model::find($value);
        });

        Route::bind('link', function ($value) {
            $model = Arr::first(array_values(array_filter(
                Code::instancesOf(Model::class)
                    ->getClasses(),
                function ($class) use ($value) {
                    try {
                        return (new $class)->getTable() === Str::slug($value, '_');
                    } catch (\Throwable $e) {
                        return false;
                    }
                })));

            abort_unless(isset($model), 404);

            return $model;
        });

    }
}
