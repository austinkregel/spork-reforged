<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Jobs\Servers\DigitalOceanSyncJob;
use App\Models\Credential;
use App\Models\User;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Nette\NotImplementedException;

class FetchServersForCredential implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Credential $credential,
        public ?User $user = null,
    ) {
        $this->user = $user ?? auth()->user();
    }

    /**
     * Execute the job.
     */
    public function handle(Dispatcher $dispatcher): void
    {

        if ($this->batch()?->cancelled()) {
            return;
        }
        if ($this->credential->type !== Credential::TYPE_SERVER) {
            return;
        }

        $this->batch()->add([match ($this->credential->service) {
            Credential::DIGITAL_OCEAN => new DigitalOceanSyncJob($this->credential, $this->user),
            default => throw new NotImplementedException('Service not implemented: '. $this.$this->credential->service),
        }]);
    }
}
