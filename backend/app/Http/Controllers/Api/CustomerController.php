<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    public function index(): JsonResponse
    {
        $customers = Customer::query()
            ->latest()
            ->get();

        return response()->json([
            'data' => $customers,
        ]);
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = Customer::query()->create($request->validated());

        return response()->json([
            'message' => 'Cadastro criado com sucesso.',
            'data' => $customer,
        ], 201);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $customer->update($request->validated());

        return response()->json([
            'message' => 'Cadastro atualizado com sucesso.',
            'data' => $customer->fresh(),
        ]);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json([
            'message' => 'Cadastro excluído com sucesso.',
        ]);
    }
}