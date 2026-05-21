<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class CepAddressData
{
    public function __construct(
        public string $cep,
        public string $logradouro,
        public string $bairro,
        public string $cidade,
        public string $uf,
    ) {
    }

    /**
     * @return array{cep:string,logradouro:string,bairro:string,cidade:string,uf:string}
     */
    public function toArray(): array
    {
        return [
            'cep' => $this->formatCep($this->cep),
            'logradouro' => $this->logradouro,
            'bairro' => $this->bairro,
            'cidade' => $this->cidade,
            'uf' => strtoupper($this->uf),
        ];
    }

    private function formatCep(string $cep): string
    {
        $digits = preg_replace('/\D/', '', $cep) ?? $cep;

        if (strlen($digits) !== 8) {
            return $cep;
        }

        return substr($digits, 0, 5).'-'.substr($digits, 5);
    }
}
