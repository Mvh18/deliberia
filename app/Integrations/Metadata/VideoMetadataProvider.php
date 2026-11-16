<?php

namespace App\Integrations\Metadata;

interface VideoMetadataProvider
{
    /**
     * @throws MetadataUnavailable
     */
    public function fetch(string $videoId): VideoMetadata;
}
