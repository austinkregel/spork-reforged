<?php

declare(strict_types=1);

namespace App\Jobs\News;

use App\Models\Article;
use App\Models\ExternalRssFeed;
use App\Services\News\Feeds\AbstractFeed;
use App\Services\News\Feeds\FeedItem;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateFeed implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ExternalRssFeed $feed;

    public function __construct(ExternalRssFeed $feed)
    {
        $this->feed = $feed;
    }

    public function handle(\App\Services\News\RssFeedService $service): void
    {
        if ($this->batch() && $this->batch()->cancelled()) {
            return;
        }

        /** @var AbstractFeed $rssFeed */
        $rssFeed = $service->fetchRssFeed($this->feed->url);

        if (empty($rssFeed)) {
            return;
        }

        /** @var FeedItem $feedItem */
        foreach ($rssFeed->getData() as $feedItem) {
            // If we already have the item's GUID, we must already have this item so we should stop,
            // as any items afterwards are probably already in our system as well.
            if ($this->feed->articles()
                ->where('external_guid', $feedItem->getUuidIfExists() ?? $feedItem->getUrl())
                ->exists()) {
                break;
            }

            // As a second check, if the item is older than the newest item we already have, we
            // probably don't want to add it since it is most likely already in our database.
            // This should hopefully never actually be catching anything as the GUID should cover
            // everything, but just in case a feed doesn't provide GUIDs or something this is a
            // decent back-up protection against duplicate articles.
            // Sub one minute just in case the time parsing doesn't quite match up.
            $mostRecentPost = $mostRecentPost ?? $this->feed->articles()->first();

            if (! empty($mostRecentPost) && $feedItem->getPublishedAt()->lessThanOrEqualTo($mostRecentPost->created_at->subMinute())) {
                break;
            }

            Article::fromFeedItem($this->feed, $feedItem);
        }

        $this->feed->name = $rssFeed->getName();

        if (! empty($rssFeed->getPhoto())) {
            $this->feed->profile_photo_path = $rssFeed->getPhoto();
        }

        if (! empty($rssFeed->getLastModified())) {
            $this->feed->last_modified = $rssFeed->getLastModified();
        }

        if (! empty($rssFeed->getEtag())) {
            $this->feed->etag = $rssFeed->getEtag();
        }

        if ($this->feed->isDirty()) {
            $this->feed->save();
        }
    }
}
