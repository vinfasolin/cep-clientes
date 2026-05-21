<?php

declare(strict_types=1);

namespace App\Services\Cep;

use App\Exceptions\CepProviderException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class CorreiosTokenService
{
    public function getToken(): string
    {
        $staticToken = trim((string) config('cep.correios.bearer_token', ''));

        if ($staticToken !== '') {
            return $staticToken;
        }

        $cacheKey = (string) config('cep.correios.token_cache_key', 'correios:token');
        $cachedToken = Cache::get($cacheKey);

        if (is_string($cachedToken) && trim($cachedToken) !== '') {
            return $cachedToken;
        }

        $payload = $this->requestToken();
        $token = $this->extractToken($payload);
        $ttl = $this->extractTtlInSeconds($payload);

        Cache::put($cacheKey, $token, now()->addSeconds($ttl));

        return $token;
    }

    /**
     * @return array<string, mixed>
     */
    private function requestToken(): array
    {
        $username = trim((string) config('cep.correios.username', ''));
        $password = trim((string) config('cep.correios.password', ''));
        $authType = strtolower(trim((string) config('cep.correios.auth_type', 'contrato')));
        $timeout = (int) config('cep.correios.timeout', 15);
        $retryTimes = (int) config('cep.correios.retry_times', 2);
        $retrySleepMs = (int) config('cep.correios.retry_sleep_ms', 300);

        if ($username === '' || $password === '') {
            throw new CepProviderException(
                'Credenciais dos Correios não configuradas. Informe CORREIOS_USERNAME e CORREIOS_PASSWORD ou configure CORREIOS_BEARER_TOKEN.'
            );
        }

        $endpoint = $this->tokenEndpoint($authType);
        $url = $this->absoluteUrl((string) config('cep.correios.token_base_url'), $endpoint);
        $body = $this->tokenBody($authType);

        $response = Http::acceptJson()
            ->asJson()
            ->withBasicAuth($username, $password)
            ->timeout($timeout)
            ->retry($retryTimes, $retrySleepMs)
            ->post($url, $body);

        if ($response->status() === 401 || $response->status() === 403) {
            throw new CepProviderException('Credenciais dos Correios recusadas ao gerar token. Verifique usuário, código de acesso, contrato e permissões.');
        }

        if ($response->failed()) {
            throw new CepProviderException('Falha ao gerar token na API dos Correios.');
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new CepProviderException('Resposta inválida ao gerar token na API dos Correios.');
        }

        return $payload;
    }

    private function tokenEndpoint(string $authType): string
    {
        $endpoints = config('cep.correios.token_endpoints', []);
        $endpoint = Arr::get($endpoints, $authType);

        if (! is_string($endpoint) || trim($endpoint) === '') {
            throw new CepProviderException("Tipo de autenticação dos Correios inválido: {$authType}.");
        }

        return $endpoint;
    }

    /**
     * @return array<string, mixed>
     */
    private function tokenBody(string $authType): array
    {
        return match ($authType) {
            'usuario' => [],
            'contrato' => $this->contractTokenBody(),
            'cartaopostagem' => $this->postingCardTokenBody(),
            default => throw new CepProviderException("Tipo de autenticação dos Correios inválido: {$authType}."),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function contractTokenBody(): array
    {
        $contractNumber = trim((string) config('cep.correios.contract_number', ''));

        if ($contractNumber === '') {
            throw new CepProviderException('Número do contrato dos Correios não configurado. Informe CORREIOS_CONTRACT_NUMBER.');
        }

        $body = ['numero' => $contractNumber];
        $dr = config('cep.correios.contract_dr');

        if ($dr !== null && trim((string) $dr) !== '') {
            $body['dr'] = (int) $dr;
        }

        return $body;
    }

    /**
     * @return array<string, mixed>
     */
    private function postingCardTokenBody(): array
    {
        $postingCardNumber = trim((string) config('cep.correios.posting_card_number', ''));

        if ($postingCardNumber === '') {
            throw new CepProviderException('Número do cartão de postagem dos Correios não configurado. Informe CORREIOS_POSTING_CARD_NUMBER.');
        }

        $body = ['numero' => $postingCardNumber];

        $contractNumber = trim((string) config('cep.correios.contract_number', ''));
        if ($contractNumber !== '') {
            $body['contrato'] = $contractNumber;
        }

        $dr = config('cep.correios.contract_dr');
        if ($dr !== null && trim((string) $dr) !== '') {
            $body['dr'] = (int) $dr;
        }

        return $body;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractToken(array $payload): string
    {
        $token = Arr::get($payload, 'token')
            ?? Arr::get($payload, 'access_token')
            ?? Arr::get($payload, 'accessToken')
            ?? Arr::get($payload, 'bearerToken')
            ?? Arr::get($payload, 'jwt');

        if (! is_string($token) || trim($token) === '') {
            throw new CepProviderException('Resposta da API Token dos Correios não retornou um token reconhecido.');
        }

        return $token;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractTtlInSeconds(array $payload): int
    {
        $margin = max(0, (int) config('cep.correios.token_cache_margin_seconds', 300));
        $defaultTtl = max(60, (int) config('cep.correios.token_default_ttl_seconds', 3300));

        $expiresIn = Arr::get($payload, 'expires_in') ?? Arr::get($payload, 'expiresIn');

        if (is_numeric($expiresIn)) {
            return max(60, ((int) $expiresIn) - $margin);
        }

        $expiresAt = Arr::get($payload, 'expiraEm')
            ?? Arr::get($payload, 'expires_at')
            ?? Arr::get($payload, 'expiresAt')
            ?? Arr::get($payload, 'validade');

        if (is_string($expiresAt) && trim($expiresAt) !== '') {
            try {
                $expiration = CarbonImmutable::parse($expiresAt);
                $seconds = now()->diffInSeconds($expiration, false);

                if ($seconds > 0) {
                    return max(60, $seconds - $margin);
                }
            } catch (Throwable) {
                // Usa TTL padrão se a data vier em formato inesperado.
            }
        }

        return $defaultTtl;
    }

    private function absoluteUrl(string $baseUrl, string $endpoint): string
    {
        return rtrim($baseUrl, '/').'/'.ltrim($endpoint, '/');
    }
}
