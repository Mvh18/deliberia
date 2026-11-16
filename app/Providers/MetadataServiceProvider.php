<?php

namespace App\Providers;

use App\Integrations\Metadata\ProblemMetadataProvider;
use App\Integrations\Metadata\Providers\DisabledProblemMetadataProvider;
use App\Integrations\Metadata\Providers\LeetCodeGraphqlProvider;
use App\Integrations\Metadata\Providers\YouTubeDataApiProvider;
use App\Integrations\Metadata\Providers\YouTubeOEmbedProvider;
use App\Integrations\Metadata\VideoMetadataProvider;
use Illuminate\Support\ServiceProvider;

class MetadataServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(VideoMetadataProvider::class, function (): VideoMetadataProvider {
            $timeout = (int) config('deliberia.http_timeout', 5);
            $key = config('deliberia.youtube.api_key');

            return is_string($key) && $key !== ''
                ? new YouTubeDataApiProvider($key, $timeout)
                : new YouTubeOEmbedProvider($timeout);
        });

        $this->app->bind(ProblemMetadataProvider::class, function (): ProblemMetadataProvider {
            return config('deliberia.leetcode.metadata_enabled')
                ? new LeetCodeGraphqlProvider((int) config('deliberia.http_timeout', 5))
                : new DisabledProblemMetadataProvider;
        });
    }
}
