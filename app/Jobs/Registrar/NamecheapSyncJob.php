<?php

declare(strict_types=1);

namespace App\Jobs\Registrar;

use App\Models\Domain;
use Illuminate\Support\Str;

class NamecheapSyncJob extends AbstractSyncRegistrarResourceJob
{
    // Sync credentials of a registrar, so we're ultimately getting a list of domains
    public function sync(): void
    {
        $page = 1;
        do {
            $domains = $this->service->getDomains(100, $page++);

            foreach ($domains as $domain) {
                $localDomain = Domain::query()
                    ->withoutGlobalScope('active')
                    ->where('credential_id', $this->credential->id)
                    ->where('name', $domain['domain'])
                    ->first();

                $data = [
                    'name' => $domain['domain'],
                    'domain_id' => $domain['id'],
                    'registered_at' => $domain['created_at'],
                    'expires_at' => $domain['expires_at'],
                ];

                if (empty($localDomain)) {
                    $localDomain = new Domain;
                    $localDomain->credential_id = $this->credential->id;
                    $localDomain->verification_key = 'reforged_'.Str::random(48);

                }

                foreach ($data as $key => $value) {
                    if ($value !== $localDomain->$key) {
                        // Only set the new value if its different
                        $localDomain->$key = $value;
                    }
                }

                if ($localDomain->isDirty() || ! $localDomain->exists()) {
                    $localDomain->save();
                }
            }
        } while ($domains->hasMorePages());
    }
}
