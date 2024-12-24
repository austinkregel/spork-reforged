<?php

declare(strict_types=1);

namespace App\Actions\Spork;

use App\Contracts\ActionInterface;
use App\Jobs\FetchResourcesFromCredential;
use App\Models\Credential;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

class SyncDataFromCredential extends CustomAction implements ActionInterface
{
    public function __construct($name = 'Sync Data From Credential', $slug = 'sync-data-from-credential')
    {
        parent::__construct($name, $slug, models: [Credential::class]);
    }

    public function __invoke(Dispatcher $dispatcher, Request $request)
    {
        $credentials = Credential::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('id', $request->get('items'))
            ->whereNotIn('type', [
                // These aren't managed by a syncing php process.
                'matrix',
                'ssh',
            ])
            ->get();

        foreach ($credentials as $credential) {
            Bus::batch([new FetchResourcesFromCredential($credential)])
                ->dispatch();
        }
    }
}
