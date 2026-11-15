<?php

namespace App\Support\ExternalUrls;

final class LeetCodeUrl
{
    /**
     * @return list<string>
     */
    private static function hosts(): array
    {
        /** @var list<string> */
        return config('deliberia.allowed_hosts.leetcode', []);
    }

    public static function hasSupportedHost(string $url): bool
    {
        return ExternalUrl::split($url, self::hosts()) !== null;
    }

    /**
     * Devuelve plataforma y slug del problema, o null si la URL no es de un problema.
     * Ignora subrutas (/description/, /solutions/), query, fragmento y barra final.
     */
    public static function parse(string $url): ?ProblemReference
    {
        $parts = ExternalUrl::split($url, self::hosts());

        if ($parts === null) {
            return null;
        }

        $segments = array_values(array_filter(explode('/', $parts['path']), fn (string $s): bool => $s !== ''));

        if (($segments[0] ?? null) !== 'problems') {
            return null;
        }

        $slug = strtolower($segments[1] ?? '');

        if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) !== 1) {
            return null;
        }

        return new ProblemReference('leetcode', $slug);
    }
}
