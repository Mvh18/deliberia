<?php

namespace Tests\Fakes;

use App\Integrations\Metadata\MetadataUnavailable;
use App\Integrations\Metadata\VideoMetadata;
use App\Integrations\Metadata\VideoMetadataProvider;

class FakeVideoMetadataProvider implements VideoMetadataProvider
{
    /** @var list<string> */
    public array $calls = [];

    public function __construct(private ?VideoMetadata $metadata = null, private bool $fails = false) {}

    public static function succeeding(?VideoMetadata $metadata = null): self
    {
        return new self($metadata ?? new VideoMetadata('Video de prueba', 'Canal de prueba', 600, 'https://i.ytimg.com/vi/x/hqdefault.jpg'));
    }

    public static function failing(): self
    {
        return new self(null, true);
    }

    public function fetch(string $videoId): VideoMetadata
    {
        $this->calls[] = $videoId;

        if ($this->fails || $this->metadata === null) {
            throw new MetadataUnavailable('Fake provider failure.');
        }

        return $this->metadata;
    }
}
