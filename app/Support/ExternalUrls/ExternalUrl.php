<?php

namespace App\Support\ExternalUrls;

/**
 * Descompone una URL y comprueba que su host está en la lista permitida.
 *
 * El host se compara completo y de forma exacta: nunca por sufijo ni con str_contains.
 */
final class ExternalUrl
{
    /**
     * @param  list<string>  $allowedHosts
     * @return array{host: string, path: string, query: array<int|string, mixed>}|null
     */
    public static function split(string $url, array $allowedHosts): ?array
    {
        $parts = parse_url(trim($url));

        if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        if (! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return null;
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            return null;
        }

        $host = strtolower(rtrim($parts['host'], '.'));

        if (! in_array($host, $allowedHosts, true)) {
            return null;
        }

        $query = [];
        parse_str($parts['query'] ?? '', $query);

        return [
            'host' => $host,
            'path' => $parts['path'] ?? '/',
            'query' => $query,
        ];
    }
}
