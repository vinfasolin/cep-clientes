<?php

declare(strict_types=1);

namespace App\Services\Cep;

use App\DTOs\CepAddressData;
use App\Exceptions\CepNotFoundException;
use InvalidArgumentException;

class FakeCepProvider implements CepProviderInterface
{
    /**
     * @var array<string, array{logradouro:string,bairro:string,cidade:string,uf:string}>
     */
    private array $addresses = [
        '80010000' => [
            'logradouro' => 'Praça Tiradentes',
            'bairro' => 'Centro',
            'cidade' => 'Curitiba',
            'uf' => 'PR',
        ],
        '01001000' => [
            'logradouro' => 'Praça da Sé',
            'bairro' => 'Sé',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
        ],
        '81530000' => [
            'logradouro' => 'Rua Coronel Francisco Heráclito dos Santos',
            'bairro' => 'Jardim das Américas',
            'cidade' => 'Curitiba',
            'uf' => 'PR',
        ],
    ];

    public function find(string $cep): CepAddressData
    {
        $digits = $this->normalize($cep);
        $address = $this->addresses[$digits] ?? null;

        if ($address === null) {
            throw new CepNotFoundException('CEP não encontrado no provider fake.');
        }

        return new CepAddressData(
            cep: $digits,
            logradouro: $address['logradouro'],
            bairro: $address['bairro'],
            cidade: $address['cidade'],
            uf: $address['uf'],
        );
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