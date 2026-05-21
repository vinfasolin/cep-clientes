<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Provider de CEP
    |--------------------------------------------------------------------------
    |
    | viacep: consulta real usando ViaCEP, sem necessidade de token.
    | correios: consulta real na API oficial dos Correios.
    | fake: útil apenas para testes automatizados/desenvolvimento offline.
    |
    */

    'provider' => env('CEP_PROVIDER', 'viacep'),

    /*
    |--------------------------------------------------------------------------
    | Provider de fallback
    |--------------------------------------------------------------------------
    |
    | Caso o provider principal falhe, a aplicação pode tentar outro provider.
    | Deixe vazio no .env para desativar fallback.
    |
    */

    'fallback_provider' => env('CEP_FALLBACK_PROVIDER'),

    /*
    |--------------------------------------------------------------------------
    | ViaCEP
    |--------------------------------------------------------------------------
    */

    'viacep' => [
        'base_url' => env('VIACEP_BASE_URL', 'https://viacep.com.br/ws'),
        'timeout' => (int) env('VIACEP_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | API oficial dos Correios
    |--------------------------------------------------------------------------
    */

    'correios' => [
        'base_url' => env('CORREIOS_BASE_URL', 'https://api.correios.com.br'),
        'token_base_url' => env('CORREIOS_TOKEN_BASE_URL', 'https://api.correios.com.br'),

        /*
        |--------------------------------------------------------------------------
        | Token
        |--------------------------------------------------------------------------
        |
        | Se CORREIOS_BEARER_TOKEN estiver preenchido, ele será usado diretamente.
        | Caso contrário, a aplicação gera token automaticamente pela API Token.
        |
        | CORREIOS_AUTH_TYPE aceito: usuario, contrato, cartaopostagem.
        | Para Busca CEP, o manual recomenda autorização por contrato.
        |
        */

        'auth_type' => env('CORREIOS_AUTH_TYPE', 'contrato'),
        'username' => env('CORREIOS_USERNAME'),
        'password' => env('CORREIOS_PASSWORD'),
        'contract_number' => env('CORREIOS_CONTRACT_NUMBER'),
        'contract_dr' => env('CORREIOS_CONTRACT_DR'),
        'posting_card_number' => env('CORREIOS_POSTING_CARD_NUMBER'),
        'bearer_token' => env('CORREIOS_BEARER_TOKEN'),

        'token_endpoints' => [
            'usuario' => env('CORREIOS_TOKEN_ENDPOINT_USUARIO', '/token/v1/autentica'),
            'contrato' => env('CORREIOS_TOKEN_ENDPOINT_CONTRATO', '/token/v1/autentica/contrato'),
            'cartaopostagem' => env('CORREIOS_TOKEN_ENDPOINT_CARTAO_POSTAGEM', '/token/v1/autentica/cartaopostagem'),
        ],

        'token_cache_key' => env('CORREIOS_TOKEN_CACHE_KEY', 'correios:token'),
        'token_cache_margin_seconds' => (int) env('CORREIOS_TOKEN_CACHE_MARGIN_SECONDS', 300),
        'token_default_ttl_seconds' => (int) env('CORREIOS_TOKEN_DEFAULT_TTL_SECONDS', 3300),

        /*
        |--------------------------------------------------------------------------
        | Busca CEP
        |--------------------------------------------------------------------------
        |
        | Endpoint principal sem acento. O provider também pode possuir fallbacks
        | internos para variações vistas na documentação/Swagger dos Correios.
        |
        */

        'cep_endpoint' => env('CORREIOS_CEP_ENDPOINT', '/cep/v2/enderecos/{cep}'),
        'timeout' => (int) env('CORREIOS_TIMEOUT', 15),
        'retry_times' => (int) env('CORREIOS_RETRY_TIMES', 2),
        'retry_sleep_ms' => (int) env('CORREIOS_RETRY_SLEEP_MS', 300),
    ],
];