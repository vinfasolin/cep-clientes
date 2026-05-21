<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_customer(): void
    {
        $payload = $this->validPayload();

        $this->postJson('/api/customers', $payload)
            ->assertCreated()
            ->assertJsonPath('message', 'Cadastro criado com sucesso.')
            ->assertJsonPath('data.nome', 'João Silva')
            ->assertJsonPath('data.email', 'joao@example.com');

        $this->assertDatabaseHas('customers', [
            'email' => 'joao@example.com',
            'cep' => '80010-000',
            'cidade' => 'Curitiba',
            'uf' => 'PR',
        ]);
    }

    public function test_can_list_customers(): void
    {
        $this->postJson('/api/customers', $this->validPayload())
            ->assertCreated();

        $this->getJson('/api/customers')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nome', 'João Silva');
    }

    public function test_rejects_invalid_payload(): void
    {
        $this->postJson('/api/customers', [
            'nome' => '',
            'email' => 'email-invalido',
            'cep' => '123',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'nome',
                'email',
                'cep',
                'logradouro',
                'numero',
                'bairro',
                'cidade',
                'uf',
            ]);
    }

    public function test_rejects_duplicate_email(): void
    {
        $payload = $this->validPayload();

        $this->postJson('/api/customers', $payload)
            ->assertCreated();

        $this->postJson('/api/customers', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * @return array<string, string>
     */
    private function validPayload(): array
    {
        return [
            'nome' => 'João Silva',
            'email' => 'joao@example.com',
            'cep' => '80010000',
            'logradouro' => 'Praça Tiradentes',
            'numero' => '123',
            'complemento' => 'Apto 10',
            'bairro' => 'Centro',
            'cidade' => 'Curitiba',
            'uf' => 'pr',
        ];
    }
}
