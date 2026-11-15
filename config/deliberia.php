<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hosts permitidos para URLs que el sistema consulta
    |--------------------------------------------------------------------------
    |
    | Solo se aplican a las URLs sobre las que la aplicación hace (o puede
    | hacer) peticiones: videos de YouTube y problemas de LeetCode. La
    | comparación es exacta sobre el host completo. Los artículos y cursos
    | nunca se consultan, así que no pasan por esta lista.
    |
    */

    'allowed_hosts' => [
        'youtube' => [
            'youtube.com',
            'www.youtube.com',
            'm.youtube.com',
            'youtu.be',
        ],
        'leetcode' => [
            'leetcode.com',
            'www.leetcode.com',
        ],
    ],

    'youtube' => [
        // Opcional. Sin clave se usa oEmbed y los videos quedan sin duración.
        'api_key' => env('YOUTUBE_API_KEY'),
    ],

    'leetcode' => [
        // Endpoint GraphQL no oficial: desactivable en cualquier momento.
        'metadata_enabled' => (bool) env('LEETCODE_METADATA_ENABLED', true),
    ],

    // Tiempo máximo (segundos) de cada petición a un proveedor externo.
    'http_timeout' => 5,

];
