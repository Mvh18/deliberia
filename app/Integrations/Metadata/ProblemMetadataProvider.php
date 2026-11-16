<?php

namespace App\Integrations\Metadata;

interface ProblemMetadataProvider
{
    /**
     * @throws MetadataUnavailable
     */
    public function fetch(string $slug): ProblemMetadata;
}
