<?php

namespace App\Integrations\Metadata\Providers;

use App\Integrations\Metadata\MetadataUnavailable;
use App\Integrations\Metadata\VideoMetadata;
use App\Integrations\Metadata\VideoMetadataProvider;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Respaldo sin clave de API: no devuelve la duración.
 */
class YouTubeOEmbedProvider implements VideoMetadataProvider
{
    public function __construct(private readonly int $timeout = 5) {}

    public function fetch(string $videoId): VideoMetadata
    {
        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->get('https://www.youtube.com/oembed', [
                    'url' => "https://www.youtube.com/watch?v={$videoId}",
                    'format' => 'json',
                ]);
        } catch (Throwable) {
            throw new MetadataUnavailable('YouTube oEmbed request failed.');
        }

        if ($response->failed()) {
            throw new MetadataUnavailable("YouTube oEmbed returned HTTP {$response->status()}.");
        }

        $title = $response->json('title');

        if (! is_string($title) || $title === '') {
            throw new MetadataUnavailable('YouTube oEmbed returned no title.');
        }

        $author = $response->json('author_name');
        $thumbnail = $response->json('thumbnail_url');

        return new VideoMetadata(
            title: $title,
            author: is_string($author) ? $author : null,
            durationSeconds: null,
            thumbnailUrl: is_string($thumbnail) ? $thumbnail : null,
        );
    }
}
