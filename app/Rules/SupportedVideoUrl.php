<?php

namespace App\Rules;

use App\Support\ExternalUrls\YouTubeUrl;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SupportedVideoUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! YouTubeUrl::hasSupportedHost($value)) {
            $fail('Dominio no soportado: solo se aceptan videos de YouTube.');

            return;
        }

        if (YouTubeUrl::parse($value) === null) {
            $fail('La URL no es un video de YouTube.');
        }
    }
}
