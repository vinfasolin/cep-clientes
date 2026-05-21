<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CepCache extends Model
{
    protected $fillable = [
        'cep',
        'logradouro',
        'bairro',
        'cidade',
        'uf',
        'raw_response',
    ];

    protected $casts = [
        'raw_response' => 'array',
    ];
}
