<?php

declare(strict_types=1);

namespace App\Http\Controllers\Spork;

use App\Http\Controllers\Controller;
use App\Models\JobBatch;
use App\Models\User;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $person = auth()->user()->person();
        $batchJobs = JobBatch::query()
            ->orderByDesc('created_at')
            ->paginate(request('job_limit', 10), ['*'], 'job_page', request('job_page', 1));

        $batchJobs->setCollection(
            Collection::make(array_map(function ($batchJob) {
                $batchJob->failed_at = \DB::table('failed_jobs')
                    ->selectRaw('max(failed_at)')
                    ->whereIn('id', $batchJob->failed_job_ids)
                    ->value('failed_at');

                return $batchJob;
            }, $batchJobs->items()))
        );

        try {
            $weatherReport = $person->primary_address ? Arr::first(app(\App\Contracts\Services\WeatherServiceContract::class)->query(
                $person->primary_address,
            )) : null;
        } catch (ConnectException $e) {
            $weatherReport = null;
        }

        return Inertia::render('Dashboard', [
            'accounts' => auth()->user()->accounts()
                ->where('accounts.type', 'checking')
                ->get(),
            'weather' => $weatherReport,
            'news' => (\App\Models\Article::query()
                ->with('externalRssFeed.tags')
                ->whereHas('externalRssFeed', function ($query) {
                    $query->where('owner_type', User::class)
                        ->where('owner_id', auth()->id());

                    $query->whereHas('tags', fn ($q) => $q->where('name->en', 'news'));
                })
                ->distinct(['headline'])
                ->orderByDesc('last_modified')
                ->paginate(request('news_limit', 15), ['*'], 'news_page', request('news_page', 1))),

            'video_feed' => \App\Models\Article::query()
                ->with('externalRssFeed.tags')
                ->whereHas('externalRssFeed', function ($query) {
                    $query->where('owner_type', User::class)
                        ->where('owner_id', auth()->id());

                    $query->whereHas('tags', fn ($q) => $q->where('name->en', 'video'));
                })
                ->orderByDesc('last_modified')
                ->paginate(request('video_limit', 15), ['*'], 'video_page', request('video_page', 1)),
            'expiring_domains' => auth()->user()
                ->domains()
                ->where('expires_at', '>=', now()->subDays(7))
                ->where('expires_at', '<=', now()->addWeeks(4))
                ->orderByDesc('expires_at')
                ->paginate(request('expiring_limit', 15), ['*'], 'expiring_page', request('expiring_page', 1)),
            'job_batches' => $batchJobs,
        ]);
    }
}
