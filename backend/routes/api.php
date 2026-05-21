<?php

declare(strict_types=1);

use App\Http\Controllers\Api\CepController;
use App\Http\Controllers\Api\CustomerController;
use Illuminate\Support\Facades\Route;

Route::get('/health', static fn () => response()->json([
    'status' => 'ok',
    'app' => config('app.name'),
    'environment' => app()->environment(),
]));

Route::get('/ceps/{cep}', [CepController::class, 'show']);

Route::apiResource('customers', CustomerController::class)
    ->only(['index', 'store', 'update', 'destroy']);