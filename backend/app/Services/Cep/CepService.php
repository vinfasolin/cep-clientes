<?php

declare(strict_types=1);

namespace App\Services\Cep;

use App\DTOs\CepAddressData;
use App\Models\CepCache;
use InvalidArgumentException;
use Throwable;

class CepService
{
    public function __construct(
        private readonly CepProviderInterface $provider,
        private readonly ?CepProviderInterface $fallbackProvider = null,
    ) {
    }

    public function find(string $cep): CepAddressData
    {
        $digits = $this->normalize($cep);

        $cached = CepCache::query()
            ->where('cep', $digits)
            ->first();

        if ($cached instanceof CepCache) {
            return new CepAddressData(
                cep: $cached->cep,
                logradouro: $cached->logradouro,
                bairro: $cached->bairro,
                cidade: $cached->cidade,
                uf: $cached->uf,
            );
        }

        $address = $this->findUsingProviders($digits);

        CepCache::query()->updateOrCreate(
            ['cep' => $digits],
            [
                'logradouro' => $address->logradouro,
                'bairro' => $address->bairro,
                'cidade' => $address->cidade,
                'uf' => $address->uf,
                'raw_response' => $address->toArray(),
            ]
        );

        return $address;
    }

    private function findUsingProviders(string $cep): CepAddressData
    {
        try {
            return $this->provider->find($cep);
        } catch (Throwable $primaryException) {
            if ($this->fallbackProvider === null) {
                throw $primaryException;
            }

            try {
                return $this->fallbackProvider->find($cep);
            } catch (Throwable) {
                throw $primaryException;
            }
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
}