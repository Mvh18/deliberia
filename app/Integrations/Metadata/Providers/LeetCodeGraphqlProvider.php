<?php

namespace App\Integrations\Metadata\Providers;

use App\Integrations\Metadata\MetadataUnavailable;
use App\Integrations\Metadata\ProblemMetadata;
use App\Integrations\Metadata\ProblemMetadataProvider;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Usa el endpoint GraphQL NO oficial de LeetCode. Solo se piden título, dificultad y marca de pago;
 * si la respuesta incluyera algo más (por ejemplo el enunciado), se descarta.
 */
class LeetCodeGraphqlProvider implements ProblemMetadataProvider
{
    private const QUERY = 'query q($titleSlug: String!) { question(titleSlug: $titleSlug) { title difficulty isPaidOnly } }';

    public function __construct(private readonly int $timeout = 5) {}

    public function fetch(string $slug): ProblemMetadata
    {
        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->withHeaders(['Referer' => "https://leetcode.com/problems/{$slug}/"])
                ->post('https://leetcode.com/graphql', [
                    'query' => self::QUERY,
                    'variables' => ['titleSlug' => $slug],
                ]);
        } catch (Throwable) {
            throw new MetadataUnavailable('LeetCode GraphQL request failed.');
        }

        if ($response->failed()) {
            throw new MetadataUnavailable("LeetCode GraphQL returned HTTP {$response->status()}.");
        }

        $question = $response->json('data.question');

        if (! is_array($question) || ! is_string($question['title'] ?? null) || $question['title'] === '') {
            throw new MetadataUnavailable('Problem not found in LeetCode GraphQL.');
        }

        $difficulty = is_string($question['difficulty'] ?? null) ? strtolower($question['difficulty']) : null;

        return new ProblemMetadata(
            title: $question['title'],
            difficulty: in_array($difficulty, ['easy', 'medium', 'hard'], true) ? $difficulty : null,
            isPaidOnly: ($question['isPaidOnly'] ?? false) === true,
        );
    }
}
