<?php

namespace Tests\Fakes;

use App\Integrations\Metadata\MetadataUnavailable;
use App\Integrations\Metadata\ProblemMetadata;
use App\Integrations\Metadata\ProblemMetadataProvider;

class FakeProblemMetadataProvider implements ProblemMetadataProvider
{
    /** @var list<string> */
    public array $calls = [];

    public function __construct(private ?ProblemMetadata $metadata = null, private bool $fails = false) {}

    public static function succeeding(?ProblemMetadata $metadata = null): self
    {
        return new self($metadata ?? new ProblemMetadata('Two Sum', 'easy', false));
    }

    public static function failing(): self
    {
        return new self(null, true);
    }

    public function fetch(string $slug): ProblemMetadata
    {
        $this->calls[] = $slug;

        if ($this->fails || $this->metadata === null) {
            throw new MetadataUnavailable('Fake provider failure.');
        }

        return $this->metadata;
    }
}
