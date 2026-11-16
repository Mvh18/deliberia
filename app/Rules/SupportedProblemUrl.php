<?php

namespace App\Rules;

use App\Support\ExternalUrls\LeetCodeUrl;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SupportedProblemUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! LeetCodeUrl::hasSupportedHost($value)) {
            $fail('Dominio no soportado: solo se aceptan problemas de LeetCode.');

            return;
        }

        if (LeetCodeUrl::parse($value) === null) {
            $fail('La URL no es un problema de LeetCode.');
        }
    }
}
