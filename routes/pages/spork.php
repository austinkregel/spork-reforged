<?php

declare(strict_types=1);

use App\Actions\Spork\CustomAction;
use App\Http\Controllers;
use App\Services\Programming\LaravelProgrammingStyle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    $instances = LaravelProgrammingStyle::instancesOf(CustomAction::class);

    foreach ($instances->constructorProperty('slug') as $file => $classAndSlug) {
        foreach ($classAndSlug as $class => $slugWithQuote) {
            Route::post('/api/actions/'.trim($slugWithQuote, '\''), $class);
        }
    }

    Route::post('/api/mail/mark-as-read', Controllers\Api\Mail\MarkAsReadController::class);
    Route::post('/api/mail/mark-as-unread', Controllers\Api\Mail\MarkAsUnreadController::class);
    Route::post('/api/mail/mark-as-spam', Controllers\Api\Mail\MarkAsSpamAndMoveController::class);
    Route::post('/api/mail/reply', Controllers\Api\Mail\ReplyController::class);
    Route::post('/api/mail/reply-all', Controllers\Api\Mail\ReplyAllController::class);
    Route::post('/api/mail/forward', Controllers\Api\Mail\ForwardMessageController::class);
    Route::post('/api/mail/destroy', Controllers\Api\Mail\DestroyMailController::class);

    Route::post('/api/message/reply', Controllers\Api\Message\ReplyController::class);

    Route::post('/api/plaid/create-link-token', Controllers\Api\Plaid\CreateLinkTokenController::class);
    Route::post('/api/plaid/exchange-token', Controllers\Api\Plaid\ExchangeTokenController::class);

    Route::post('/api/projects/{project}/tasks', Controllers\Api\Projects\CreateTaskController::class);
    Route::post('/api/credentials', [Controllers\Api\CredentialController::class, 'store']);

    Route::get('/user/api-query', Controllers\User\ApiQueryController::class)->middleware(\Illuminate\Auth\Middleware\Authenticate::class)->name('user.api-query');
});

Route::redirect('/', '/flight/login');

Route::prefix('-')->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/dashboard', Controllers\Spork\DashboardController::class)->name('dashboard');
    Route::get('/search', [Controllers\SearchController::class, 'index'])->name('search');
    Route::get('/search/{index}', [Controllers\SearchController::class, 'show'])->name('search.show');
    Route::get('/notifications', fn () => Inertia::render('Notifications'))->name('notifications');
    Route::put('/notifications/{notification:uuid}/mark-as-read', function (Request $request, $notification) {
        $notification = $request->user()->notifications()->where('id', $notification)->first();
        $notification->markAsRead();
        return response('', 204);
    });


    Route::get('/rss-feeds', fn () => Inertia::render('RssFeeds/Index', [
        'feeds' => \App\Models\Article::query()->latest('last_modified')
            ->with('author.tags')
            ->paginate()
            ->items(),
        'pagination' => \App\Models\Article::query()->latest('last_modified')
            ->with('author.tags')
            ->paginate(),
    ]));
    Route::get('/batch-jobs', [Controllers\Spork\BatchJobController::class, 'index'])->name('batch-jobs.index');
    Route::get('/batch-jobs/{batch_job}', [Controllers\Spork\BatchJobController::class, 'show'])->name('batch-jobs.show');

    Route::get('/projects', [Controllers\Spork\ProjectsController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [Controllers\Spork\ProjectsController::class, 'create'])->name('projects.create');
    Route::post('projects', [Controllers\Spork\ProjectsController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [Controllers\Spork\ProjectsController::class, 'show'])->name('projects.show');
    Route::post('project/{project}/attach', [Controllers\Spork\ProjectsController::class, 'attach'])->name('project.attach');
    Route::post('project/{project}/detach', [Controllers\Spork\ProjectsController::class, 'detach'])->name('project.detach');

    Route::get('/pages/create', [Controllers\Spork\PagesController::class, 'create'])->name('pages');

    Route::get('/servers', [Controllers\Spork\ServersController::class, 'index'])->name('servers.show');
    Route::get('/servers/{server}', [Controllers\Spork\ServersController::class, 'show'])->name('servers.show');
    Route::get('/domains', [Controllers\Spork\DomainsController::class, '__invoke'])->name('domains.show');
    Route::get('/domains/{domain}', [Controllers\Spork\DomainsController::class, 'show'])->name('domains.show');

    Route::post('deployment/{deployment}/detach', [Controllers\Spork\DeploymentController::class, 'detach'])->name('deployment.detach');
    Route::post('deployment/{deployment}/attach', [Controllers\Spork\DeploymentController::class, 'attach'])->name('deployment.attach');
    Route::post('deployment/{project}/deploy', [Controllers\Spork\DeploymentController::class, 'deploy'])->name('project.deploy');


    Route::get('/banking', Controllers\Spork\BankingController::class)->name('banking.index');
    Route::get('/banking/budgets', [Controllers\Spork\BankingController::class, 'budgets'])->name('banking.budgets');

    Route::get('/file-manager', Controllers\Spork\FileManagerController::class)->name('file-manager.index');
    Route::post('/file-manager/default', [Controllers\Spork\FileManagerController::class, 'updateFileManager'])->name('file-manager.update-default');

    Route::get('kvm', function () {
        $vmName = 'ubuntu22.04';
        $libvirt = libvirt_connect('qemu:///system', false);
        $domain = libvirt_domain_lookup_by_name($libvirt, $vmName);
        $data = libvirt_domain_get_screenshot_api($domain, 0);
        $filePath = $data['file'];
        $img = new \Imagick($filePath);
        $img->readImage($filePath);

        $img->setImageFormat('jpeg');
        $img->setImageCompressionQuality(90);
        $img->writeImageFile(fopen($filePath.'.jpg', 'w'));
        try {
            return response(file_get_contents($filePath.'.jpg'), 200, [
                'Content-Type' => 'image/jpeg',
            ]);
        } finally {
            unlink($filePath);
            unlink($filePath.'.jpg');
        }
    });

    Route::get('/inbox', [Controllers\Spork\MessageController::class, 'index'])->name('inbox');
    Route::get('/inbox/{message}', [Controllers\Spork\MessageController::class, 'show'])->name('inbox.show');
    Route::get('/postal', [Controllers\Spork\InboxController::class, 'index'])->name('postal.index');
    Route::get('/postal/{email}', [Controllers\Spork\InboxController::class, 'show'])->name('postal.show');

    Route::get('/manage/{slug}', [Controllers\Spork\ManageController::class, 'show'])->name('manage.show');
    Route::get('/manage', [Controllers\Spork\ManageController::class, 'index'])->name('manage.index');

    Route::get('/settings', Controllers\Spork\SettingsController::class);
    Route::get('/tag-manager', Controllers\Spork\TagManagerController::class);
    Route::get('/tag-manager/{tag}', [Controllers\Spork\TagManagerController::class, 'show']);


    Route::get('/research', [Controllers\Spork\ResearchController::class, 'index'])->name('research.index');
    Route::get('/research/{research}', [Controllers\Spork\ResearchController::class, 'show'])->name('research.show');

    Route::get('/assets', [Controllers\Spork\AssetController::class, 'index'])->name('assets.index');
    Route::get('/labels', [Controllers\Spork\AssetController::class, 'labels'])->name('assets.labels');
});
Route::middleware([
    'web',
    config('jetstream.auth_session'),
    'verified',
    App\Http\Middleware\OnlyHost::class,
    \App\Http\Middleware\OnlyInDevelopment::class,
])->group(function () {
    Route::post('/api/install', Controllers\InstallNewProvider::class);
    Route::post('/api/uninstall', Controllers\UninstallNewProvider::class);
    Route::post('/api/enable', Controllers\EnableProviderController::class);
    Route::post('/api/disable', Controllers\DisableProviderController::class);
    Route::get('/-/development', [Controllers\Spork\DevelopmentController::class, 'index'])->name('development.index');

    Route::get('/-/logic', Controllers\Spork\LogicController::class);
    Route::post('/api/logic/add-listener-for-event', Controllers\Logic\AddListenerForEventController::class);
    Route::post('/api/logic/remove-listener-for-event', Controllers\Logic\RemoveListenerForEventController::class);
    Route::get('/api/files/{basepath}', [Controllers\Spork\FileManagerController::class, 'show'])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\TrimStrings::class]);
    Route::put('/api/files/{basepath}', [Controllers\Spork\FileManagerController::class, 'update'])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\TrimStrings::class]);

    Route::get('/api/uuid', function () {
        return response()->json(['uuid' => \Ramsey\Uuid\Uuid::uuid4()->toString()]);
    })->name('spork.uuid');

    Route::get('/locations', function () {
        return view('locations.index', [
            'locations' => \App\Models\Location::query()->get(),
        ]);
    });
});
