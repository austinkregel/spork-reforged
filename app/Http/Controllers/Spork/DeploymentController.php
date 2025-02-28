<?php

namespace App\Http\Controllers\Spork;

use App\Http\Controllers\Controller;
use App\Models\Credential;
use App\Models\Deployment;
use App\Models\Domain;
use App\Models\Project;
use App\Models\Research;
use App\Models\Server;
use App\Models\Taggable;
use App\Models\Task;
use Illuminate\Http\Request;

class DeploymentController extends Controller
{
    public function deploy(Deployment $deployment)
    {
        $deployment->load([
            'servers.tags', 'domains',
        ]);

        $forgeCredential = $deployment->credentials()->where('service', 'forge')->first();
        $cloudflareCredential = $deployment->credentials()->where('service', 'cloudflare')->first();
        $namecheapCredential = $deployment->credentials()->where('service', 'namecheap')->first();

        /** @var \App\Models\Server $server */
        foreach ($deployment->servers as $server) {
            $tags = array_map(fn ($tag) => $tag->name->en, $server->tags);
            if (in_array('loadbalancer', $tags)) {
                // Link the load balancer's network to all the other servers, only add servers that are labeled `web`
            }
            foreach ($deployment->domains as $domain) {
                if (in_array('loadbalancer', $tags)) {
                    // Each one of these jobs should look to see if the configuration is already where we want it.
                    dispatch_sync(new \App\Jobs\Deployment\Steps\SetupCloudflareDns($domain, $cloudflareCredential, $namecheapCredential));
                    dispatch_sync(new \App\Jobs\Deployment\Steps\SetupLoadBalancerJob($server, $domain, $deployment));
                    dispatch_sync(new \App\Jobs\Deployment\Steps\SetupLoadBalancerDnsRecordJob($server, $domain, $deployment));
                    dispatch_sync(new \App\Jobs\Deployment\Steps\DeploySslCertificateJob($server, $domain, $forgeCredential));
                }
                if (in_array('web', $tags)) {
                    // Setup domain on server
                    // Setup project for server (setup git, setup deployment webhook, etc etc...)
                    // Update the environment variables with share values.
                    // Configure jobs/queues for server
                    // configure cron schedules/daemons
                }
                // Basically a queue worker, or a project with a prod env that isn't _the_ prod server.
                if (in_array('app', $tags)) {
                    // Queue workers are not setup to handle traffic from the load balancer
                    // Setup domain on server
                    // Setup project for server (setup git, setup deployment webhook, etc etc...)
                    // Update the environment variables with share values.
                    // Configure jobs/queues for server
                    // configure cron schedules/daemons
                }
            }
        }

    }

    public function attach(Deployment $deployment)
    {
        //    request()
        request()->validate([
            'resource_type' => \Illuminate\Validation\Rule::in([
                \App\Models\Credential::class,
                Domain::class,
                Server::class,
            ]),
        ]);

        if (\DB::table('deployment_resources')->where([
            'resource_type' => request()->get('resource_type'),
            'resource_id' => request()->get('resource_id'),
            'deployment_id' => $deployment->id,
        ])->exists()) {
            return response([
                'message' => 'Already exists',
            ], 422);
        }

        \DB::table('deployment_resources')->insert([
            'resource_type' => request()->get('resource_type'),
            'resource_id' => request()->get('resource_id'),
            'deployment_id' => $deployment->id,
            'settings' => $this->filterSettingsForResource(request()->get('resource_type'), request()->get('resource_id')),
        ]);


    }

    public function detach(Project $deployment)
    {
        //    request()
        request()->validate([
            'resource_type' => \Illuminate\Validation\Rule::in([
                \App\Models\Credential::class,
                Domain::class,
                Server::class,

            ]),
        ]);

        \DB::table('deployment_resources')->where([
            'resource_type' => request()->get('resource_type'),
            'resource_id' => request()->get('resource_id'),
            'deployment_id' => $deployment->id,
        ])->delete();
    }

    protected function filterSettingsForResource(string $classType, $id)
    {
        $settings = [];
        $implementedInterfaces = class_implements($classType);
        $relations = [];

        if (in_array(\App\Models\Taggable::class, $implementedInterfaces)) {
            $relations[] = 'tags';
        }

        /** @var Server $instance */
        $instance = $classType::query()
            ->with($relations)
            ->find($id);

        $tags = $instance->tags;

        if ($tags->map->name->map->en->contains('loadbalancer')) {
            $settings['loadbalancer'] = true;
        }

        return $settings;
    }
}
