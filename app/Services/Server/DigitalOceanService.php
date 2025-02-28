<?php

declare(strict_types=1);

namespace App\Services\Server;

use App\Contracts\Services\DigitalOceanServiceContract;
use App\Models\Credential;
use App\Services\Filters\DigitalOceanServerFilter;
use DigitalOceanV2\Client;
use DigitalOceanV2\Entity\Domain;
use DigitalOceanV2\Entity\DomainRecord;
use DigitalOceanV2\Entity\Droplet as DigitalOceanServer;
use DigitalOceanV2\Entity\Key;
use DigitalOceanV2\Entity\Region;
use DigitalOceanV2\Entity\Region as DigitalOceanRegion;
use DigitalOceanV2\Entity\Size as DigitalOceanSize;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DigitalOceanService implements DigitalOceanServiceContract
{
    protected Client $digitalOcean;

    protected DigitalOceanServerFilter $serverFilter;

    public function __construct(Credential $credential)
    {
        $this->digitalOcean = app(Client::class);

        $this->digitalOcean->authenticate($credential->api_key);

        $this->serverFilter = new DigitalOceanServerFilter();
    }

    public function createServer(array $config): array
    {
        return [];
    }

    public function findAllRegions(): array
    {
        $regions = $this->digitalOcean->region()->getAll();

        return array_map(function (DigitalOceanRegion $region) {
            return new Region($region->toArray());
        }, $regions);
    }

    public function findAllSizes(): array
    {
        $sizes = $this->digitalOcean->size()->getAll();

        return array_map(function (DigitalOceanSize $size) {
            return $size->toArray();
        }, $sizes);
    }

    public function findAllServers(): array
    {
        $servers = $this->digitalOcean->droplet()->getAll();

        return array_map(function (DigitalOceanServer $server) {
            return $this->serverFilter->filter($server);
        }, $servers);
    }

    public function removeServerKey($identifier): void
    {
        //        $this->digitalOcean->key()->delete($identifier);
    }

    public function deleteServer(int|string $identifier): void
    {
        //        $this->digitalOcean->droplet()->delete($identifier);
    }

    public function powerOnServer(int|string $identifier): void
    {
        $this->digitalOcean->droplet()->powerOn($identifier);
    }

    public function powerOffServer(int|string $identifier): void
    {
        $this->digitalOcean->droplet()->powerOff($identifier);
    }

    public function shutdownServer(int|string $identifier): void
    {
        $this->digitalOcean->droplet()->shutdown($identifier);
    }

    public function rebootServer(int|string $identifier): void
    {
        $this->digitalOcean->droplet()->reboot($identifier);
    }

    public function findAllSshkeys(): array
    {
        $keys = $this->digitalOcean->key()->getAll();

        return array_map(function (Key $key): Key {
            return $key->toArray();
        }, $keys);
    }

    public function getDomains(int $limit = 10, int $page = 1): LengthAwarePaginator
    {
        $items = $this->digitalOcean->domain()->getAll();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            array_map(function (Domain $domain) {
                return $domain->toArray();
            }, $items),
            count($items),
            $limit,
            $page,
        );
    }

    public function getDns(string $domain, string $type = '', int $limit = 10, int $page = 1): LengthAwarePaginator
    {
        $items = $this->digitalOcean->domainRecord()->getAll($domain);

        $domains = array_map(function (DomainRecord $domain) {
            return $domain->toArray();
        }, $items);

        if (!empty($type)) {
            $domains = array_filter($domains, function ($domain) use ($type) {
                return $domain['type'] === $type;
            });
        }

        return new \Illuminate\Pagination\LengthAwarePaginator(
            array_values($domains),
            count($domains),
            $limit,
            $page,
        );
    }

    public function deleteDnsRecord(string $domain, string $dnsRecordId): void
    {
        // TODO: Implement deleteDnsRecord() method.
    }

    public function createDnsRecord(string $domain, array $dnsRecordArray): void
    {
        // TODO: Implement createDnsRecord() method.
    }

    public function getDomainNs(string $domain): array
    {
        $this->getDns($domain, 'NS');
    }

    public function updateDomainNs(string $domain, array $nameservers): array
    {
        // TODO: Implement updateDomainNs() method.
    }

    public function createDomain(string $domain): array
    {
        // TODO: Implement createDomain() method.
    }
}
