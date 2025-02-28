<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\ModelQuery;
use App\Events\Models\Article\ArticleCreated;
use App\Events\Models\Article\ArticleCreating;
use App\Events\Models\Article\ArticleDeleted;
use App\Events\Models\Article\ArticleDeleting;
use App\Events\Models\Article\ArticleUpdated;
use App\Events\Models\Article\ArticleUpdating;
use App\Models\Traits\ScopeQSearch;
use App\Models\Traits\ScopeRelativeSearch;
use App\Services\News\Feeds\FeedItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Tags\HasTags;

class Article extends Model implements ModelQuery, Taggable
{
    use HasTags;
    use HasFactory;
    use LogsActivity;
    use ScopeQSearch;
    use ScopeRelativeSearch;
    use Searchable;

    public $fillable = [
        'uuid',
        'external_guid',
        'author_type',
        'author_id',
        'last_modified',
        'etag',
        'headline',
        'content',
        'attachment',
        'url',
    ];

    public $dispatchesEvents = [
        'created' => ArticleCreated::class,
        'creating' => ArticleCreating::class,
        'deleting' => ArticleDeleting::class,
        'deleted' => ArticleDeleted::class,
        'updating' => ArticleUpdating::class,
        'updated' => ArticleUpdated::class,
    ];

    protected function casts(): array
    {
        return [
            'last_modified' => 'datetime',
        ];
    }

    public static function fromFeedItem(ExternalRssFeed $feed, FeedItem $item): self
    {
        if ($post = self::firstWhere('external_guid', $item->getUuidIfExists())) {
            return $post;
        }

        $post = new Article();
        // If the item's GUID is a v4 UUID, we may as well use it as our UUID.
        $post->uuid = $item->getUuidIfExists();
        $post->external_guid = $item->getUuidIfExists() ?? $item->getUrl();
        $post->author_id = $feed->id;
        $post->author_type = get_class($feed);
        $post->headline = $item->getTitle();
        $post->content = $item->getContent();
        $post->attachment = $item->getUrl();
        $post->url = $item->getUrl();
        $post->created_at = $item->getPublishedAt();
        $post->last_modified = $item->getPublishedAt();
        $post->save();

        return $post;
    }

    public function author(): MorphTo
    {
        return $this->morphTo();
    }

    public function externalRssFeed(): BelongsTo
    {
        return $this->belongsTo(ExternalRssFeed::class, 'author_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['headline', 'content', 'attachment', 'url'])
            ->useLogName('article')
            ->logFillable()
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }
}
