<?php

namespace Tests\Unit\Integrations;

use App\Integrations\Metadata\MetadataUnavailable;
use App\Integrations\Metadata\ProblemMetadataProvider;
use App\Integrations\Metadata\Providers\DisabledProblemMetadataProvider;
use App\Integrations\Metadata\Providers\LeetCodeGraphqlProvider;
use App\Integrations\Metadata\Providers\YouTubeDataApiProvider;
use App\Integrations\Metadata\Providers\YouTubeOEmbedProvider;
use App\Integrations\Metadata\VideoMetadataProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetadataProvidersTest extends TestCase
{
    public function test_youtube_data_api_maps_fields_and_converts_duration(): void
    {
        Http::fake(['www.googleapis.com/*' => Http::response(['items' => [[
            'snippet' => [
                'title' => 'Un video',
                'channelTitle' => 'Un canal',
                'thumbnails' => ['default' => ['url' => 'https://i.ytimg.com/d.jpg'], 'high' => ['url' => 'https://i.ytimg.com/h.jpg']],
            ],
            'contentDetails' => ['duration' => 'PT1H2M3S'],
        ]]])]);

        $meta = (new YouTubeDataApiProvider('test-key'))->fetch('dQw4w9WgXcQ');

        $this->assertSame('Un video', $meta->title);
        $this->assertSame('Un canal', $meta->author);
        $this->assertSame(3723, $meta->durationSeconds);
        $this->assertSame('https://i.ytimg.com/h.jpg', $meta->thumbnailUrl);

        Http::assertSent(fn (Request $r) => str_starts_with($r->url(), 'https://www.googleapis.com/youtube/v3/videos')
            && $r['id'] === 'dQw4w9WgXcQ'
            && $r['part'] === 'snippet,contentDetails');
    }

    public function test_youtube_data_api_fails_when_video_missing_or_http_error(): void
    {
        Http::fake(['www.googleapis.com/*' => Http::sequence()
            ->push(['items' => []])
            ->push(['error' => 'quota'], 403)]);

        $provider = new YouTubeDataApiProvider('test-key');

        foreach ([1, 2] as $_) {
            try {
                $provider->fetch('dQw4w9WgXcQ');
                $this->fail('Expected MetadataUnavailable.');
            } catch (MetadataUnavailable) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_youtube_data_api_error_does_not_leak_api_key(): void
    {
        Http::fake(['www.googleapis.com/*' => fn () => throw new ConnectionException('cURL error for https://x?key=secret-key')]);

        try {
            (new YouTubeDataApiProvider('secret-key'))->fetch('dQw4w9WgXcQ');
            $this->fail('Expected MetadataUnavailable.');
        } catch (MetadataUnavailable $e) {
            $this->assertStringNotContainsString('secret-key', $e->getMessage());
        }
    }

    public function test_oembed_returns_title_without_duration(): void
    {
        Http::fake(['www.youtube.com/oembed*' => Http::response([
            'title' => 'Otro video',
            'author_name' => 'Canal',
            'thumbnail_url' => 'https://i.ytimg.com/hq.jpg',
        ])]);

        $meta = (new YouTubeOEmbedProvider)->fetch('dQw4w9WgXcQ');

        $this->assertSame('Otro video', $meta->title);
        $this->assertSame('Canal', $meta->author);
        $this->assertNull($meta->durationSeconds);
        $this->assertSame('https://i.ytimg.com/hq.jpg', $meta->thumbnailUrl);
    }

    public function test_oembed_failure_throws(): void
    {
        Http::fake(['www.youtube.com/oembed*' => Http::response('Not Found', 404)]);

        $this->expectException(MetadataUnavailable::class);

        (new YouTubeOEmbedProvider)->fetch('dQw4w9WgXcQ');
    }

    public function test_leetcode_returns_only_allowed_fields_and_drops_statement(): void
    {
        Http::fake(['leetcode.com/graphql' => Http::response(['data' => ['question' => [
            'title' => 'Two Sum',
            'difficulty' => 'Easy',
            'isPaidOnly' => true,
            'content' => '<p>Enunciado que no debe guardarse</p>',
        ]]])]);

        $meta = (new LeetCodeGraphqlProvider)->fetch('two-sum');

        $this->assertSame('Two Sum', $meta->title);
        $this->assertSame('easy', $meta->difficulty);
        $this->assertTrue($meta->isPaidOnly);
        $this->assertSame(['title', 'difficulty', 'isPaidOnly'], array_keys(get_object_vars($meta)));

        Http::assertSent(fn (Request $r) => ! str_contains((string) $r['query'], 'content')
            && $r['variables'] === ['titleSlug' => 'two-sum']);
    }

    public function test_leetcode_unknown_problem_or_error_throws(): void
    {
        Http::fake(['leetcode.com/graphql' => Http::sequence()
            ->push(['data' => ['question' => null]])
            ->push('Forbidden', 403)]);

        $provider = new LeetCodeGraphqlProvider;

        foreach ([1, 2] as $_) {
            try {
                $provider->fetch('two-sum');
                $this->fail('Expected MetadataUnavailable.');
            } catch (MetadataUnavailable) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_disabled_provider_makes_no_request(): void
    {
        try {
            (new DisabledProblemMetadataProvider)->fetch('two-sum');
            $this->fail('Expected MetadataUnavailable.');
        } catch (MetadataUnavailable) {
            Http::assertNothingSent();
        }
    }

    public function test_container_binds_providers_from_config(): void
    {
        config(['deliberia.youtube.api_key' => null, 'deliberia.leetcode.metadata_enabled' => false]);
        $this->assertInstanceOf(YouTubeOEmbedProvider::class, app(VideoMetadataProvider::class));
        $this->assertInstanceOf(DisabledProblemMetadataProvider::class, app(ProblemMetadataProvider::class));

        config(['deliberia.youtube.api_key' => 'k', 'deliberia.leetcode.metadata_enabled' => true]);
        $this->assertInstanceOf(YouTubeDataApiProvider::class, app(VideoMetadataProvider::class));
        $this->assertInstanceOf(LeetCodeGraphqlProvider::class, app(ProblemMetadataProvider::class));
    }

    public function test_parse_duration(): void
    {
        $this->assertSame(45, YouTubeDataApiProvider::parseDuration('PT45S'));
        $this->assertSame(600, YouTubeDataApiProvider::parseDuration('PT10M'));
        $this->assertSame(0, YouTubeDataApiProvider::parseDuration('P0D'));
    }
}
