<?php

namespace App\Support\ExternalUrls;

final class YouTubeUrl
{
    /**
     * @return list<string>
     */
    private static function hosts(): array
    {
        /** @var list<string> */
        return config('deliberia.allowed_hosts.youtube', []);
    }

    /**
     * Indica si la URL apunta a un host de YouTube permitido.
     */
    public static function hasSupportedHost(string $url): bool
    {
        return ExternalUrl::split($url, self::hosts()) !== null;
    }

    /**
     * Devuelve el ID de 11 caracteres del video, o null si no es un video de YouTube.
     */
    public static function parse(string $url): ?string
    {
        $parts = ExternalUrl::split($url, self::hosts());

        if ($parts === null) {
            return null;
        }

        $segments = array_values(array_filter(explode('/', $parts['path']), fn (string $s): bool => $s !== ''));

        if ($parts['host'] === 'youtu.be') {
            $candidate = $segments[0] ?? null;
        } elseif (($segments[0] ?? null) === 'watch') {
            $candidate = $parts['query']['v'] ?? null;
        } elseif (in_array($segments[0] ?? null, ['shorts', 'embed'], true)) {
            $candidate = $segments[1] ?? null;
        } else {
            $candidate = null;
        }

        if (! is_string($candidate) || preg_match('/^[A-Za-z0-9_-]{11}$/', $candidate) !== 1) {
            return null;
        }

        return $candidate;
    }
}
