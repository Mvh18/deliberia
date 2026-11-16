<?php

namespace Tests\Unit\ExternalUrls;

use App\Rules\SupportedProblemUrl;
use App\Support\ExternalUrls\LeetCodeUrl;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LeetCodeUrlTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     */
    public static function validUrls(): array
    {
        return [
            'canonica' => ['https://leetcode.com/problems/two-sum/'],
            'sin barra final' => ['https://leetcode.com/problems/two-sum'],
            'description' => ['https://leetcode.com/problems/two-sum/description/'],
            'solutions' => ['https://leetcode.com/problems/two-sum/solutions/123/some-title'],
            'query' => ['https://leetcode.com/problems/two-sum/?envType=study-plan&envId=top-interview-150'],
            'fragmento' => ['https://leetcode.com/problems/two-sum/#intro'],
            'www' => ['https://www.leetcode.com/problems/two-sum/'],
            'host en mayusculas' => ['HTTPS://LeetCode.com/problems/two-sum/'],
            'slug en mayusculas' => ['https://leetcode.com/problems/Two-Sum/'],
        ];
    }

    #[DataProvider('validUrls')]
    public function test_parses_platform_and_slug(string $url): void
    {
        $ref = LeetCodeUrl::parse($url);

        $this->assertNotNull($ref);
        $this->assertSame('leetcode', $ref->platform);
        $this->assertSame('two-sum', $ref->slug);
        $this->assertSame('https://leetcode.com/problems/two-sum/', $ref->canonicalUrl());
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidUrls(): array
    {
        return [
            'userinfo' => ['https://leetcode.com@evil.example.com/problems/two-sum/'],
            'sufijo engañoso' => ['https://leetcode.com.evil.example.com/problems/two-sum/'],
            'subdominio no permitido' => ['https://evil.leetcode.com/problems/two-sum/'],
            'leetcode.cn' => ['https://leetcode.cn/problems/two-sum/'],
            'esquema no http' => ['ftp://leetcode.com/problems/two-sum/'],
            'lista de problemas' => ['https://leetcode.com/problemset/'],
            'problems sin slug' => ['https://leetcode.com/problems/'],
            'slug invalido' => ['https://leetcode.com/problems/two_sum!/'],
            'perfil' => ['https://leetcode.com/u/alguien/'],
            'no es url' => ['two-sum'],
        ];
    }

    #[DataProvider('invalidUrls')]
    public function test_rejects_non_problem_or_unsafe_urls(string $url): void
    {
        $this->assertNull(LeetCodeUrl::parse($url));
    }

    public function test_validation_rule_messages(): void
    {
        $rule = fn (string $url): ?string => Validator::make(['u' => $url], ['u' => [new SupportedProblemUrl]])->errors()->first('u') ?: null;

        $this->assertNull($rule('https://leetcode.com/problems/two-sum/description/'));
        $this->assertStringContainsString('Dominio no soportado', (string) $rule('https://example.com/problems/two-sum/'));
        $this->assertStringContainsString('no es un problema', (string) $rule('https://leetcode.com/problemset/'));
    }
}
