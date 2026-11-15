<?php

namespace App\Integrations\Metadata;

final readonly class VideoMetadata
{
    public function __construct(
        public string $title,
        public ?string $author = null,
        public ?int $durationSeconds = null,
        public ?string $thumbnailUrl = null,
    ) {}
}
