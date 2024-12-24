<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\Services\JiraServiceContract;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use JiraRestApi\Issue\Issue;

class SyncJiraTicketsJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function handle(JiraServiceContract $jiraService): void
    {
        $projects = Project::query()
            ->whereNotNull('settings->jira_id')
            ->get();

        foreach ($projects as $project) {
            $page = 1;

            do {
                /** @var LengthAwarePaginator $tickets */
                $tickets = $jiraService->findAllTickets($project->settings['jira_id'], now()->subDays(3), 100, $page++);

                /** @var Issue $ticket */
                foreach ($tickets->items() as $ticket) {
                    $task = Task::query()
                        ->where('service_identifier', $ticket->id)
                        ->first();

                    if (! empty($task)) {
                        $task->update([
                            'name' => $ticket->key.' - '.$ticket->fields->summary,
                            'notes' => $ticket->fields->description,
                            'status' => $ticket->fields->status->name,
                            'type' => strtoupper($ticket->fields->issuetype->name),
                        ]);
                    } else {
                        $project->tasks()->create([
                            'service_identifier' => $ticket->id,
                            'name' => $ticket->key.' - '.$ticket->fields->summary,
                            'notes' => $ticket->fields->description,
                            'status' => $ticket->fields->status->name,
                            'type' => strtoupper($ticket->fields->issuetype->name),
                        ]);
                    }
                }
            } while ($tickets->hasMorePages());
        }
    }
}
