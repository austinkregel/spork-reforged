<?php

declare(strict_types=1);

namespace App\Services\News\Feeds;

use Carbon\Carbon;
use Illuminate\Support\Arr;

class RdfFeed extends AbstractFeed
{
    public function getLastModified(): ?Carbon
    {
        $headers = array_change_key_case($this->headers, CASE_LOWER);

        $lastModifiedHeader = Arr::get($headers, 'last-modified', [null])[0];

        if (isset($lastModifiedHeader)) {
            return Carbon::parse($lastModifiedHeader);
        }

        if (isset($this->element->date)) {
            return Carbon::parse((string) $this->element->date);
        }

        return null;
    }

    public function getPhoto(): ?string
    {
        if (isset($this->element->image)) {
            return $this->element->image;
        }

        return null;
    }

    public function getName(): string
    {
        return (string) $this->element->title ?? '';
    }

    public function getData(): array
    {
        $feedItems = [];

        foreach ($this->element->item as $post) {
            $feedItem = new FeedItem();
            $feedItem->id = (string) ($post->guid ?? $post->link ?? '');

            $feedItem->setTitle($post->title);
            // This bit could be too specific for other rdf feeds :sweat_smile:
            // I know it works for slashdot :sweat_smile:
            $feedItem->setPublishedAt($post->children('dc', true)->date);
            $feedItem->setUrl($post->children('feedburner', true)->origLink);

            $feedItems[] = $feedItem;
        }

        return $feedItems;
    }
}
