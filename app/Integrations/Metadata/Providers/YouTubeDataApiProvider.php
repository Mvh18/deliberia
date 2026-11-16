<?php

namespace App\Integrations\Metadata\Providers;

use App\Integrations\Metadata\MetadataUnavailable;
use App\Integrations\Metadata\VideoMetadata;
use App\Integrations\Metadata\VideoMetadataProvider;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Http;
use Throwable;

class YouTubeDataApiProvider implements VideoMetadataProvider
{
    public function __construct(
        private readonly string $apiKey,
        private readonly int $timeout = 5,
    ) {}

    public function fetch(string $videoId): VideoMetadata
    {
        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->get('https://www.googleapis.com/youtube/v3/videos', [
                    'part' => 'snippet,contentDetails',
                    'id' => $videoId,
                    'key' => $this->apiKey,
                ]);
        } catch (Throwable) {
            // Sin detalles: el mensaje de la excepción podría incluir la URL con la clave.
            throw new MetadataUnavailable('YouTube Data API request failed.');
        }

        if ($response->failed()) {
            throw new MetadataUnavailable("YouTube Data API returned HTTP {$response->status()}.");
        }

        $item = $response->json('items.0');

        if (! is_array($item) || ! is_string($item['snippet']['title'] ?? null)) {
            throw new MetadataUnavailable('Video not found in YouTube Data API.');
        }

        $snippet = $item['snippet'];
        $thumbnails = is_array($snippet['thumbnails'] ?? null) ? $snippet['thumbnails'] : [];
        $thumbnail = $thumbnails['high']['url'] ?? $thumbnails['medium']['url'] ?? $thumbnails['default']['url'] ?? null;
        $duration = $item['contentDetails']['duration'] ?? null;

        return new VideoMetadata(
            title: $snippet['title'],
            author: is_string($snippet['channelTitle'] ?? null) ? $snippet['channelTitle'] : null,
            durationSeconds: is_string($duration) ? self::parseDuration($duration) : null,
            thumbnailUrl: is_string($thumbnail) ? $thumbnail : null,
        );
    }

    /**
     * Convierte una duración ISO 8601 (PT1H2M3S) a segundos.
     */
    public static function parseDuration(string $iso): ?int
    {
        try {
            $interval = CarbonInterval::make($iso);

            return $interval === null ? null : (int) round($interval->totalSeconds);
        } catch (Throwable) {
            return null;
        }
    }
}
