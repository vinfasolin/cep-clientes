<?php

declare(strict_types=1);

namespace App\Services\Cep;

use App\DTOs\CepAddressData;

interface CepProviderInterface
{
    public function find(string $cep): CepAddressData;
}
