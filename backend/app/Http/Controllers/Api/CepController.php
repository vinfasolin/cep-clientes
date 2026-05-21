<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\CepNotFoundException;
use App\Exceptions\CepProviderException;
use App\Http\Controllers\Controller;
use App\Services\Cep\CepService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Throwable;

class CepController extends Controller
{
    public function __construct(
        private readonly CepService $cepService,
    ) {
    }

    public function show(string $cep): JsonResponse
    {
        try {
            $address = $this->cepService->find($cep);

            return response()->json($address->toArray());
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (CepNotFoundException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        } catch (CepProviderException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 502);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Erro inesperado ao consultar o CEP.',
            ], 500);
        }
    }
}