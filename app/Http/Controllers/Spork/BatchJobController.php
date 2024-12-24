<?php

declare(strict_types=1);

namespace App\Http\Controllers\Spork;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Kregel\ExceptionProbe\Stacktrace;

class BatchJobController extends Controller
{
    public function index()
    {
        $batches = \DB::table('job_batches')
            ->select('*')
            ->orderByDesc('created_at')
            ->paginate();

        $paginator = new LengthAwarePaginator(
            array_map(function ($batch) {
                $batch->jobs = \DB::table('failed_jobs')
                    ->select('*')
                    ->whereIn('uuid', json_decode($batch->failed_job_ids, true))
                    ->orderByDesc('failed_at')
                    ->get()
                    ->map(function ($job) {
                        $job->parsed_exception = (new Stacktrace)->parse($job->exception);
                        $job->payload = json_decode($job->payload, true);

                        return $job;
                    });
                $batch->failed_at = $batch->jobs->max('failed_at');

                return $batch;
            }, $batches->items()),
            $batches->total(),
            $batches->perPage(),
            $batches->currentPage()
        );

        return Inertia::render('Admin/BatchJob/Index', [
            'title' => 'Batch Jobs',
            'paginator' => $paginator,
        ]);
    }

    public function show(Request $request, $batch)
    {
        $batches = \DB::table('job_batches')
            ->select('*')
            ->orderByDesc('created_at')
            ->where('id', $batch)
            ->paginate();

        $paginator = new LengthAwarePaginator(
            array_map(function ($batch) {
                $batch->jobs = \DB::table('failed_jobs')
                    ->select('*')
                    ->whereIn('uuid', json_decode($batch->failed_job_ids, true))
                    ->orderByDesc('failed_at')
                    ->get()
                    ->map(function ($job) {
                        $job->parsed_exception = (new Stacktrace)->parse($job->exception);
                        $job->payload = json_decode($job->payload, true);

                        return $job;
                    });
                $batch->failed_at = $batch->jobs->max('failed_at');

                return $batch;
            }, $batches->items()),
            $batches->total(),
            $batches->perPage(),
            $batches->currentPage()
        );

        return Inertia::render('Admin/BatchJob/Show', [
            'title' => 'Batch Jobs',
            'paginator' => $paginator,
        ]);
    }
}
