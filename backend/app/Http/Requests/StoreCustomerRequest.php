<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
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
        return [
            'nome' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:160', Rule::unique('customers', 'email')],
            'cep' => ['required', 'regex:/^\d{5}-?\d{3}$/'],
            'logradouro' => ['required', 'string', 'max:180'],
            'numero' => ['required', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:120'],
            'bairro' => ['required', 'string', 'max:120'],
            'cidade' => ['required', 'string', 'max:120'],
            'uf' => ['required', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $complemento = $this->filled('complemento')
            ? trim((string) $this->input('complemento'))
            : null;

        $this->merge([
            'nome' => trim((string) $this->input('nome')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'cep' => $this->formatCep((string) $this->input('cep')),
            'logradouro' => trim((string) $this->input('logradouro')),
            'numero' => trim((string) $this->input('numero')),
            'complemento' => $complemento,
            'bairro' => trim((string) $this->input('bairro')),
            'cidade' => trim((string) $this->input('cidade')),
            'uf' => strtoupper(trim((string) $this->input('uf'))),
        ]);
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