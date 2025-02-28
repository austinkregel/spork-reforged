<?php

declare(strict_types=1);

namespace App\Jobs\Domains;

use App\Events\Domains\NameServerRecordVerified;
use App\Models\User;
use App\Notifications\NameServerVerificationSuccessful;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NameServerVerificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $host,
        public array $expectedServers,
        public bool $relaunchDnsCheckAfterDelay = false,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $value = array_map(fn (array $record) => $record['target'], dns_get_record($this->host, DNS_NS));

        $diff1 = array_diff($value, $this->expectedServers);
        $diff2 = array_diff($this->expectedServers, $value);

        // The Name Infrastructure should be whatever cloudflare tells us they should be.
        // We want to ensure that only the records from cloudflare are used.
        if ($this->relaunchDnsCheckAfterDelay && (! empty($diff1) || ! empty($diff2))) {
            dispatch(new static($this->host, $this->expectedServers))->delay(5 * 60);

            return;
        }

        event(new NameServerRecordVerified(
            $this->host,
            $this->expectedServers
        ));

        User::first()->notify(new NameServerVerificationSuccessful(
            $this->host,
            $this->expectedServers,
        ));
    }
}
