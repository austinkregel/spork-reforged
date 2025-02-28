<?php

declare(strict_types=1);

namespace App\Actions\Spork\Domains;

use App\Actions\Spork\CustomAction;
use App\Contracts\ActionInterface;
use App\Models\Credential;
use App\Models\Domain;
use App\Services\Factories\DomainServiceFactory;
use App\Services\Factories\RegistrarServiceFactory;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\Request;

class AddDomainToCloudflareAction extends CustomAction implements ActionInterface
{
    public function __construct($name = 'Add domain to cloudflare', $slug = 'add-domain-to-cloudflare')
    {
        parent::__construct($name, $slug, models: [Domain::class]);
    }

    public function __invoke(Dispatcher $dispatcher, Request $request)
    {
        $request->validate([
            'items' => 'required|array',
        ]);
        $credentials = $request->user()->credentials()->get();

        $domains = Domain::query()
            ->with('credential')
            ->whereIn('credential_id', $credentials->pluck('id'))
            ->whereIn('id', $request->input('items'))
            ->get();

        $cloudflare = Credential::query()
            ->where('user_id', $request->user()->id)
            ->where('service', 'cloudflare')
            ->where('type', Credential::TYPE_DOMAIN)
            ->first();

        $registrarFactory = new RegistrarServiceFactory;
        $domainFactory = new DomainServiceFactory;
        $cloudflareService = $domainFactory->make($cloudflare);

        foreach ($domains as $domain) {

            $cfDns = $cloudflareService->createDomain($domain->name);

            $registrarService = $registrarFactory->make($domain->credential);

            $registrarService->updateDomainNs($domain->name, $cfDns);
        }
    }
}
