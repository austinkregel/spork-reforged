<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Jobs\Finance\SyncPlaidTransactionsJob;
use App\Jobs\Servers\LaravelForgeServersSyncJob;
use App\Models\Credential;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchResourcesFromCredential implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Credential $credential
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(Dispatcher $dispatcher): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $nextJob = match ($this->credential->type) {
            Credential::TYPE_REGISTRAR => new FetchRegistrarForCredential($this->credential),
            Credential::TYPE_DOMAIN => new FetchDomainsForCredential($this->credential),
            Credential::TYPE_SERVER => new FetchServersForCredential($this->credential),
            Credential::TYPE_DEVELOPMENT, 'forge' => new LaravelForgeServersSyncJob($this->credential),
            Credential::TYPE_FINANCE => new SyncPlaidTransactionsJob($this->credential, now()->subWeek(), now(), false),
            Credential::TYPE_EMAIL => new SyncMailboxIfCredentialsAreSet($this->credential),
            default => Log::error(sprintf('Found unsupported credential type for FetchResourcesFromCredentialsJob: %s', $this->credential->type), []),
        };

        if ($this->batch()) {
            $this->batch()->add([
                $nextJob,
            ]);
        } else {
            dispatch($nextJob);
        }
    }
}
