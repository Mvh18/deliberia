<?php

namespace App\Integrations\Metadata;

/**
 * Solo metadatos mínimos: nunca enunciado, casos de prueba ni soluciones.
 */
final readonly class ProblemMetadata
{
    /**
     * @param  'easy'|'medium'|'hard'|null  $difficulty
     */
    public function __construct(
        public string $title,
        public ?string $difficulty = null,
        public bool $isPaidOnly = false,
    ) {}
}
