<?php

namespace App\Integrations\Metadata\Providers;

use App\Integrations\Metadata\MetadataUnavailable;
use App\Integrations\Metadata\ProblemMetadata;
use App\Integrations\Metadata\ProblemMetadataProvider;

/**
 * Se usa con LEETCODE_METADATA_ENABLED=false: no hace ninguna petición.
 */
class DisabledProblemMetadataProvider implements ProblemMetadataProvider
{
    public function fetch(string $slug): ProblemMetadata
    {
        throw new MetadataUnavailable('Problem metadata integration is disabled.');
    }
}
