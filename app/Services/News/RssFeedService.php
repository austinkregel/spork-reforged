<?php

declare(strict_types=1);

namespace App\Services\News;

use App\Contracts\Services\News\RssServiceContract;
use App\Services\News\Feeds\AbstractFeed;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RssFeedService implements RssServiceContract
{
    public function fetchRssFeed(string $url): ?AbstractFeed
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        try {
            $request = cache()->remember(
                $url.'.',
                now()->addMinutes(15),
                function () use ($url) {
                    $request = Http::withHeaders([
                        'Accept' => '*/*',
                        'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:130.0) Gecko/20100101 Firefox/130.0',
                    ])->get($url);
                    // This is for accidental extra decoding, which seems to happen with the CNN news source.
                    $body = str_replace(
                        '&amamp;',
                        '&amp;',
                        $request->body()
                    );

                    if ($request->status() >= 400) {
                        abort(404);
                    }

                    return [
                        'url' => $url,
                        'headers' => array_change_key_case($request->headers(), CASE_LOWER),
                        'body' => $body,
                    ];
                }
            );
        } catch (ConnectionException|ClientException|HttpException $e) {
            info('Exception occurred', [$e->getMessage(), $url, get_class($e)]);

            return null;
        }

        $firstHundredChars = strtolower(substr($request['body'], 0, 100));
        if (stripos($firstHundredChars, '<!DOCTYPE html>') !== false) {
            info('Found an HTML document hiding in our RSS feeds', [
                'url' => $request['url'],
            ]);

            return null;
        }

        return (new RssParserFactory)->parse($request);
    }

    public function isValidRssFeed(string $url): bool
    {
        return $this->fetchRssFeed($url) !== null;
    }
}
