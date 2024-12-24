<?php

declare(strict_types=1);

namespace App\Http\Controllers\Spork;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Models\Project;
use App\Models\Research;
use App\Models\Task;
use App\Services\Development\DescribeTableService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectsController extends Controller
{
    public function index()
    {
        $model = \App\Models\Project::class;
        /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
        $paginator = auth()->user()->personalProjects()
            ->paginate(request('limit', 15), ['*'], 'page', request('page', 1));

        $data = $paginator->items();
        $paginator = $paginator->toArray();

        unset($paginator['data']);

        return Inertia::render('Projects/Index', [
            'data' => $data,
            'paginator' => $paginator,
        ]);
    }

    public function show(Project $project)
    {
        $project->load([
            'pages.domain',
            'research',
            'credentials',
            'deployments.domain',
            'deployments.server',
            'deployments.domains',
            'deployments.servers',
        ]);

        return Inertia::render('Projects/Project', [
            'project' => $project,
            'daily_tasks' => $project->tasks()
                ->where('status', '!=', 'done')
                ->whereIn('project_id', auth()->user()->projects()->pluck('project_id'))
                ->where('type', 'daily')
                ->get(),
            'today_tasks' => $project->tasks()
                ->where('status', '!=', 'done')
                ->where('status', '!=', 'Blocked')

                ->whereIn('project_id', auth()->user()->projects()->pluck('project_id'))
                ->where(function ($query) {
                    $query->where('start_date', '<=', now())
                        ->orWhere('end_date', '>=', now())
                        ->orWhereNull('end_date');
                })
                ->get(),
            'future_tasks' => $project->tasks()
                ->where('status', '=', 'Blocked')
                ->whereIn('project_id', auth()->user()->projects()->pluck('project_id'))
                ->get(),
        ]);
    }

    public function deploy(Project $project)
    {
        $project->load([
            'servers.tags', 'domains',
        ]);

        $forgeCredential = $project->credentials()->where('service', 'forge')->first();
        $cloudflareCredential = $project->credentials()->where('service', 'cloudflare')->first();
        $namecheapCredential = $project->credentials()->where('service', 'namecheap')->first();

        /** @var \App\Models\Server $server */
        foreach ($project->servers as $server) {
            $tags = array_map(fn ($tag) => $tag->name->en, $server->tags);
            if (in_array('loadbalancer', $tags)) {
                // Link the load balancer's network to all the other servers, only add servers that are labeled `web`
            }
            foreach ($project->domains as $domain) {
                if (in_array('loadbalancer', $tags)) {
                    // Each one of these jobs should look to see if the configuration is already where we want it.
                    dispatch_sync(new \App\Jobs\Deployment\Steps\SetupCloudflareDns($domain, $cloudflareCredential, $namecheapCredential));
                    dispatch_sync(new \App\Jobs\Deployment\Steps\SetupLoadBalancerJob($server, $domain, $project));
                    dispatch_sync(new \App\Jobs\Deployment\Steps\SetupLoadBalancerDnsRecordJob($server, $domain, $project));
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

    public function attach(Project $project)
    {
        //    request()
        request()->validate([
            'resource_type' => \Illuminate\Validation\Rule::in([
                \App\Models\Credential::class,
                \App\Models\Page::class,
                Research::class,
                Task::class,
            ]),
        ]);

        if (\DB::table('project_resources')->where([
            'resource_type' => request()->get('resource_type'),
            'resource_id' => request()->get('resource_id'),
            'project_id' => $project->id,
        ])->exists()) {
            return response([
                'message' => 'Already exists',
            ], 422);
        }

        \DB::table('project_resources')->insert([
            'resource_type' => request()->get('resource_type'),
            'resource_id' => request()->get('resource_id'),
            'project_id' => $project->id,
            'settings' => '{}',
        ]);
    }

    public function detach(Project $project)
    {
        //    request()
        request()->validate([
            'resource_type' => \Illuminate\Validation\Rule::in([
                \App\Models\Credential::class,
                \App\Models\Page::class,
                Research::class,
                Task::class,
            ]),
        ]);

        \DB::table('project_resources')->where([
            'resource_type' => request()->get('resource_type'),
            'resource_id' => request()->get('resource_id'),
            'project_id' => $project->id,
        ])->delete();
    }

    public function create()
    {
        $description = (new DescribeTableService)->describe(new Project());

        return Inertia::render('Projects/Create', [
            'description' => $description,
        ]);
    }

    public function store(StoreProjectRequest $request)
    {
        $project = new Project;
        $project->forceFill($request->all());
        $project->save();

        return redirect()->route('projects.show', $project);
    }
}
