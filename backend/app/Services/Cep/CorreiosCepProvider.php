<?php

declare(strict_types=1);

namespace App\Services\Cep;

use App\DTOs\CepAddressData;
use App\Exceptions\CepNotFoundException;
use App\Exceptions\CepProviderException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class CorreiosCepProvider implements CepProviderInterface
{
    public function __construct(
        private readonly CorreiosTokenService $tokenService,
    ) {
    }

    public function find(string $cep): CepAddressData
    {
        $digits = $this->normalize($cep);
        $token = $this->tokenService->getToken();
        $urls = $this->buildCepUrls($digits);
        $lastResponse = null;

        foreach ($urls as $url) {
            $response = $this->requestCep($url, $token);
            $lastResponse = $response;

            if ($response->successful()) {
                $payload = $response->json();

                if (! is_array($payload)) {
                    throw new CepProviderException('Resposta inválida da API Busca CEP dos Correios.');
                }

                return $this->mapPayloadToAddress($digits, $payload);
            }

            if ($response->status() === 401 || $response->status() === 403) {
                throw new CepProviderException('Token dos Correios recusado. Verifique credenciais, contrato e liberação da API Busca CEP.');
            }

            if (! in_array($response->status(), [404, 405], true)) {
                throw new CepProviderException('Falha ao consultar API Busca CEP dos Correios.');
            }
        }

        if ($lastResponse?->status() === 404) {
            throw new CepNotFoundException('CEP não encontrado nos Correios.');
        }

        throw new CepProviderException('Não foi possível consultar o CEP na API dos Correios.');
    }

    private function requestCep(string $url, string $token): Response
    {
        $timeout = (int) config('cep.correios.timeout', 15);
        $retryTimes = (int) config('cep.correios.retry_times', 2);
        $retrySleepMs = (int) config('cep.correios.retry_sleep_ms', 300);

        return Http::acceptJson()
            ->withToken($token)
            ->timeout($timeout)
            ->retry($retryTimes, $retrySleepMs)
            ->get($url);
    }

    /**
     * @return list<string>
     */
    private function buildCepUrls(string $cep): array
    {
        $baseUrl = rtrim((string) config('cep.correios.base_url'), '/');
        $configuredEndpoint = (string) config('cep.correios.cep_endpoint', '/cep/v2/enderecos/{cep}');

        $endpoints = [
            $configuredEndpoint,
            '/cep/v2/enderecos/{cep}',
            '/cep/v2/endereços/{cep}',
            '/cep/v2/enderecos?cep={cep}',
        ];

        $urls = [];

        foreach ($endpoints as $endpoint) {
            $path = str_replace('{cep}', $cep, $endpoint);
            $url = str_starts_with($path, 'http') ? $path : $baseUrl.'/'.ltrim($path, '/');
            $urls[$url] = $url;
        }

        return array_values($urls);
    }

    /**
     * Mapeamento tolerante para reduzir acoplamento com variações de payload.
     *
     * @param array<string, mixed> $payload
     */
    private function mapPayloadToAddress(string $cep, array $payload): CepAddressData
    {
        $addressPayload = $this->extractAddressPayload($payload);

        $logradouro = Arr::get($addressPayload, 'logradouro')
            ?? Arr::get($addressPayload, 'endereco')
            ?? trim((string) (Arr::get($addressPayload, 'tipoLogradouro', '').' '.Arr::get($addressPayload, 'nomeLogradouro', '')));

        $bairro = Arr::get($addressPayload, 'bairro')
            ?? Arr::get($addressPayload, 'nomeBairro')
            ?? '';

        $cidade = Arr::get($addressPayload, 'cidade')
            ?? Arr::get($addressPayload, 'localidade')
            ?? Arr::get($addressPayload, 'municipio')
            ?? Arr::get($addressPayload, 'nomeLocalidade')
            ?? '';

        $uf = Arr::get($addressPayload, 'uf')
            ?? Arr::get($addressPayload, 'estado')
            ?? '';

        if (empty($cidade) || empty($uf)) {
            throw new CepProviderException('Resposta dos Correios não possui dados mínimos de endereço.');
        }

        return new CepAddressData(
            cep: $cep,
            logradouro: trim((string) $logradouro),
            bairro: trim((string) $bairro),
            cidade: trim((string) $cidade),
            uf: strtoupper(trim((string) $uf)),
        );
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function extractAddressPayload(array $payload): array
    {
        $items = Arr::get($payload, 'itens')
            ?? Arr::get($payload, 'items')
            ?? Arr::get($payload, 'enderecos')
            ?? Arr::get($payload, 'data');

        if (is_array($items) && array_is_list($items)) {
            if ($items === []) {
                throw new CepNotFoundException('CEP não encontrado nos Correios.');
            }

            $firstItem = $items[0];

            if (is_array($firstItem)) {
                return $firstItem;
            }
        }

        return $payload;
    }

    private function normalize(string $cep): string
    {
        $digits = preg_replace('/\D/', '', $cep) ?? '';

        if (strlen($digits) !== 8) {
            throw new InvalidArgumentException('CEP deve conter exatamente 8 dígitos.');
        }

        return $digits;
    }
}