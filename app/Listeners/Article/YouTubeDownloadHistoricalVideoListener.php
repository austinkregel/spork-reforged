<?php

namespace App\Listeners\Article;

use App\Events\Models\Article\ArticleCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class YouTubeDownloadHistoricalVideoListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ArticleCreated $event): void
    {
        $hasItems = $event->model->externalRssFeed->tagsWithType('auto')->count() > 0;

        if ($hasItems > 0) {
            return ;
        }

        // We want to
    }
}
