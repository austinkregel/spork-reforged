<?php

declare(strict_types=1);

namespace App\Jobs\Domains;

use App\Models\Credential;
use App\Models\Domain;
use App\Services\Factories\DomainServiceFactory;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class CloudflareSyncAndPurgeJob extends AbstractSyncDomainResource
{
    public function handle(DomainServiceFactory $serviceFactory): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }
        $credentials = Credential::where('service', 'cloudflare')->get();
        foreach ($credentials as $credential) {
            try {
                $this->service = $serviceFactory->make($credential);
                $this->credential = $credential;
                $this->sync();
            } catch (UnauthorizedHttpException $e) {
                // There are some times when we have a different credential for the same uuid.
                // this would be when things are duplicated, the uuid stays the same, but the credentials themselves and what they access
                // change. So we need to try all our credentials of a uuid to ensure that we're syncing everything available.
            }
        }
    }

    public function sync(): void
    {
        $page = 1;
        do {
            $domains = $this->service->getDomains(100, $page++);

            foreach ($domains as $domain) {
                // In order for domain jobs to be able to run, we need the domain to exist from a registrar.
                $localDomain = Domain::where('name', $domain['domain'])->first();

                if (empty($localDomain)) {
                    info('No local domain for the provided credentials', [
                        'domain' => $domain['domain'],
                        'credential' => $localDomain,
                    ]);

                    // If we don't have the domain in question synced via registrars we don't want to touch it.
                    continue;
                }

                if (empty($localDomain->cloudflare_id)) {
                    $localDomain->cloudflare_id = $domain['id'];
                }

                if ($localDomain->isDirty()) {
                    $localDomain->save();
                }

                $localDomain->records()->delete();
                $dnsResults = $this->service->getDns($localDomain->cloudflare_id);

                foreach ($dnsResults as $dnsRecord) {
                    $localDomain->records()->firstOrCreate([
                        'record_id' => $dnsRecord['id'],
                        'type' => $dnsRecord['type'],
                        'name' => $dnsRecord['name'],
                        'ttl' => $dnsRecord['ttl'],
                        'value' => $dnsRecord['content'],
                    ], [
                        'priority' => $dnsRecord['priority'],
                        'proxied_through_cloudflare' => $dnsRecord['proxied_through_cloudflare'],
                    ]);
                }

            }
        } while ($domains->hasMorePages());
    }
}
