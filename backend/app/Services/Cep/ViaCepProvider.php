<?php

declare(strict_types=1);

namespace App\Services\Cep;

use App\DTOs\CepAddressData;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

class ViaCepProvider implements CepProviderInterface
{
    public function find(string $cep): CepAddressData
    {
        $digits = $this->normalize($cep);

        $baseUrl = rtrim((string) config('cep.viacep.base_url', 'https://viacep.com.br/ws'), '/');
        $timeout = (int) config('cep.viacep.timeout', 10);

        try {
            $response = Http::acceptJson()
                ->timeout($timeout)
                ->get("{$baseUrl}/{$digits}/json/");

            if ($response->failed()) {
                throw new RuntimeException('Não foi possível consultar o CEP no ViaCEP.');
            }

            $data = $response->json();

            if (! is_array($data)) {
                throw new RuntimeException('Resposta inválida recebida do ViaCEP.');
            }

            if (($data['erro'] ?? false) === true) {
                throw new RuntimeException('CEP não encontrado no ViaCEP.');
            }

            return new CepAddressData(
                cep: $data['cep'] ?? $this->formatCep($digits),
                logradouro: $data['logradouro'] ?? '',
                bairro: $data['bairro'] ?? '',
                cidade: $data['localidade'] ?? '',
                uf: $data['uf'] ?? '',
            );
        } catch (ConnectionException | RequestException $exception) {
            throw new RuntimeException('Erro de comunicação com o ViaCEP.', previous: $exception);
        }
    }

    private function normalize(string $cep): string
    {
        $digits = preg_replace('/\D/', '', $cep) ?? '';

        if (strlen($digits) !== 8) {
            throw new InvalidArgumentException('CEP deve conter exatamente 8 dígitos.');
        }

        return $digits;
    }

    private function formatCep(string $cep): string
    {
        return substr($cep, 0, 5) . '-' . substr($cep, 5);
    }
}