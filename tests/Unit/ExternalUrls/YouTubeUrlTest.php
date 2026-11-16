<?php

namespace Tests\Unit\ExternalUrls;

use App\Rules\SupportedVideoUrl;
use App\Support\ExternalUrls\YouTubeUrl;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class YouTubeUrlTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     */
    public static function validUrls(): array
    {
        return [
            'watch' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
            'watch sin www' => ['https://youtube.com/watch?v=dQw4w9WgXcQ'],
            'mobile' => ['https://m.youtube.com/watch?v=dQw4w9WgXcQ'],
            'con tiempo y lista' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=42s&list=PL1234'],
            'v no es el primer parametro' => ['https://www.youtube.com/watch?list=PL1234&v=dQw4w9WgXcQ'],
            'youtu.be' => ['https://youtu.be/dQw4w9WgXcQ'],
            'youtu.be con t' => ['https://youtu.be/dQw4w9WgXcQ?t=10'],
            'shorts' => ['https://www.youtube.com/shorts/dQw4w9WgXcQ'],
            'embed' => ['https://www.youtube.com/embed/dQw4w9WgXcQ'],
            'host en mayusculas' => ['HTTPS://WWW.YOUTUBE.COM/watch?v=dQw4w9WgXcQ'],
        ];
    }

    #[DataProvider('validUrls')]
    public function test_parses_video_id(string $url): void
    {
        $this->assertSame('dQw4w9WgXcQ', YouTubeUrl::parse($url));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidUrls(): array
    {
        return [
            'sufijo engañoso' => ['https://youtube.com.evil.example.com/watch?v=dQw4w9WgXcQ'],
            'prefijo engañoso' => ['https://evilyoutube.com/watch?v=dQw4w9WgXcQ'],
            'userinfo' => ['https://youtube.com@evil.example.com/watch?v=dQw4w9WgXcQ'],
            'userinfo con host valido' => ['https://user:pass@www.youtube.com/watch?v=dQw4w9WgXcQ'],
            'esquema ftp' => ['ftp://www.youtube.com/watch?v=dQw4w9WgXcQ'],
            'esquema javascript' => ['javascript:alert(1)'],
            'otro dominio' => ['https://vimeo.com/watch?v=dQw4w9WgXcQ'],
            'canal' => ['https://www.youtube.com/@canal'],
            'playlist' => ['https://www.youtube.com/playlist?list=PL1234'],
            'sin v' => ['https://www.youtube.com/watch'],
            'id corto' => ['https://www.youtube.com/watch?v=abc'],
            'id con caracteres invalidos' => ['https://www.youtube.com/watch?v=dQw4w9WgXc!'],
            'no es url' => ['esto no es una url'],
            'vacio' => [''],
        ];
    }

    #[DataProvider('invalidUrls')]
    public function test_rejects_non_video_or_unsafe_urls(string $url): void
    {
        $this->assertNull(YouTubeUrl::parse($url));
    }

    public function test_validation_rule_messages(): void
    {
        $rule = fn (string $url): ?string => Validator::make(['u' => $url], ['u' => [new SupportedVideoUrl]])->errors()->first('u') ?: null;

        $this->assertNull($rule('https://youtu.be/dQw4w9WgXcQ'));
        $this->assertStringContainsString('Dominio no soportado', (string) $rule('https://vimeo.com/123'));
        $this->assertStringContainsString('no es un video', (string) $rule('https://www.youtube.com/@canal'));
    }
}
