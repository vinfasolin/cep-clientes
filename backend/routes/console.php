<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

Artisan::command('about:cep-clientes', function (): void {
    $this->info('CEP Clientes API');
})->purpose('Exibe informações básicas do projeto');
