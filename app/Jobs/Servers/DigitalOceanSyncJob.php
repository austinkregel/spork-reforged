<?php

declare(strict_types=1);

namespace App\Jobs\Servers;

use App\Models\Server;

class DigitalOceanSyncJob extends AbstractSyncServerResourceJob
{
    public function sync(): void
    {
        // this means all servers need to respond with the keys.
        $servers = $this->service->findAllServers();


        foreach ($servers as $server) {
            $localServer = Server::query()
                ->where('server_id', $server['id'])
                ->first();

            if (empty($localServer)) {
                $localServer = new Server([
                    'server_id' => $server['id'],
                    'name' => $server['name'],
                    'ip_address' => $server['networks']['public_v4'] ?? null,
                    'ip_address_v6' => $server['networks']['public_v6'] ?? null,
                    'internal_ip_address' => $server['networks']['private_v4'] ?? null,
                    'internal_ip_address_v6' => $server['networks']['private_v6'] ?? null,
                    'os' => $server['image'],
                    'vcpu' => $server['cpu'],
                    'memory' => $server['memory'],
                    'disk' => $server['disk'],
                    'cost_per_hour' => $server['cost'],
                    'credential_id' => $this->credential->id,
                ]);
//                $localServer->ownable_type = get_class($this->credential);
//                $localServer->ownable_id = $this->credential->id;
                $this->credential->servers()->create($localServer->toArray());
            } else {
                $data = [
                    'name' => $server['name'],
                    'ip_address' => $server['networks']['public_v4'] ?? null,
                    'ip_address_v6' => $server['networks']['public_v6'] ?? null,
                    'internal_ip_address' => $server['networks']['private_v4'] ?? null,
                    'internal_ip_address_v6' => $server['networks']['private_v6'] ?? null,
                    'os' => $server['image'],
                    'vcpu' => $server['cpu'],
                    'memory' => $server['memory'],
                    'disk' => $server['disk'],
                    'cost_per_hour' => $server['cost'],
                    'status' => $server['status'],
                    'credential_id' => $this->credential->id,
                ];

                foreach ($data as $key => $value) {
                    if ($value !== $localServer->$key) {
                        // Only set the new value if its different
                        $localServer->$key = $value;
                    }
                }
            }

            if ($localServer->isDirty() || ! $localServer->exists()) {
                $localServer->save();
            }
        }
    }
}
