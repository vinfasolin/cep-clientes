<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $customer = $this->route('customer');

        $customerId = $customer instanceof Customer
            ? $customer->getKey()
            : $customer;

        return [
            'nome' => ['sometimes', 'required', 'string', 'min:2', 'max:120'],
            'email' => [
                'sometimes',
                'required',
                'email:rfc',
                'max:160',
                Rule::unique('customers', 'email')->ignore($customerId),
            ],
            'cep' => ['sometimes', 'required', 'regex:/^\d{5}-?\d{3}$/'],
            'logradouro' => ['sometimes', 'required', 'string', 'max:180'],
            'numero' => ['sometimes', 'required', 'string', 'max:20'],
            'complemento' => ['sometimes', 'nullable', 'string', 'max:120'],
            'bairro' => ['sometimes', 'required', 'string', 'max:120'],
            'cidade' => ['sometimes', 'required', 'string', 'max:120'],
            'uf' => ['sometimes', 'required', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('nome')) {
            $data['nome'] = trim((string) $this->input('nome'));
        }

        if ($this->has('email')) {
            $data['email'] = mb_strtolower(trim((string) $this->input('email')));
        }

        if ($this->has('cep')) {
            $data['cep'] = $this->formatCep((string) $this->input('cep'));
        }

        if ($this->has('logradouro')) {
            $data['logradouro'] = trim((string) $this->input('logradouro'));
        }

        if ($this->has('numero')) {
            $data['numero'] = trim((string) $this->input('numero'));
        }

        if ($this->has('complemento')) {
            $data['complemento'] = $this->filled('complemento')
                ? trim((string) $this->input('complemento'))
                : null;
        }

        if ($this->has('bairro')) {
            $data['bairro'] = trim((string) $this->input('bairro'));
        }

        if ($this->has('cidade')) {
            $data['cidade'] = trim((string) $this->input('cidade'));
        }

        if ($this->has('uf')) {
            $data['uf'] = strtoupper(trim((string) $this->input('uf')));
        }

        $this->merge($data);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'email' => 'e-mail',
            'cep' => 'CEP',
            'logradouro' => 'logradouro',
            'numero' => 'número',
            'complemento' => 'complemento',
            'bairro' => 'bairro',
            'cidade' => 'cidade',
            'uf' => 'UF',
        ];
    }

    private function formatCep(string $cep): string
    {
        $digits = preg_replace('/\D/', '', $cep) ?? '';

        if (strlen($digits) !== 8) {
            return trim($cep);
        }

        return substr($digits, 0, 5).'-'.substr($digits, 5);
    }
}