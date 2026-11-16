<?php

namespace App\Support\ExternalUrls;

final readonly class ProblemReference
{
    public function __construct(
        public string $platform,
        public string $slug,
    ) {}

    /**
     * URL canónica del problema.
     */
    public function canonicalUrl(): string
    {
        return "https://leetcode.com/problems/{$this->slug}/";
    }
}
