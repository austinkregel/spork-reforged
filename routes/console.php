<?php

declare(strict_types=1);

use App\Jobs\FetchResourcesFromCredentials;
use App\Jobs\News\UpdateAllFeeds;
use App\Jobs\Notifications\BuildSummaryNotificationJob;
use App\Jobs\SyncJiraTicketsJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(SyncJiraTicketsJob::class)->daily();
Schedule::job(UpdateAllFeeds::class)->everyFifteenMinutes();
Schedule::job(FetchResourcesFromCredentials::class)->hourly();
Schedule::job(BuildSummaryNotificationJob::class)->dailyAt('13:00');
Schedule::command('operations:queue')->everyFiveMinutes();

Artisan::command('update:all-feeds', function() {
    UpdateAllFeeds::dispatch();
})->describe('Update all feeds');
Artisan::command('fetch:resources', function() {
    FetchResourcesFromCredentials::dispatch();
})->describe('Fetch resources from credentials');
Artisan::command('build:summary-notification', function() {
    BuildSummaryNotificationJob::dispatch();
})->describe('Build summary notification');
Artisan::command('sync:jira-tickets', function() {
    SyncJiraTicketsJob::dispatch();
})->describe('Sync Jira tickets');

Artisan::command('test', function () {
    dispatch_sync(new \App\Jobs\Finance\SyncPlaidTransactionsJob(\App\Models\Credential::find(17)));
});

// Petoskey Site,
// Consumes RSS Feed https://www.youtube.com/feeds/videos.xml?channel_id=UCL0k-RfN5JoL8Eenhoqe7eg
// Then we can use the YouTube API to get the videos and their details
// Transcribe the videos, and summarize them with an LLM

