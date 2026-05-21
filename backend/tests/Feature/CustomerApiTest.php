<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_deve_listar_cadastros_realizados(): void
    {
        Customer::query()->create([
            'nome' => 'João Silva',
            'email' => 'joao@example.com',
            'cep' => '81530-000',
            'logradouro' => 'Rua Coronel Francisco Heráclito dos Santos',
            'numero' => '100',
            'complemento' => 'Apto 12',
            'bairro' => 'Jardim das Américas',
            'cidade' => 'Curitiba',
            'uf' => 'PR',
        ]);

        $response = $this->getJson('/api/customers');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'nome',
                        'email',
                        'cep',
                        'logradouro',
                        'numero',
                        'complemento',
                        'bairro',
                        'cidade',
                        'uf',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJsonPath('data.0.nome', 'João Silva')
            ->assertJsonPath('data.0.email', 'joao@example.com')
            ->assertJsonPath('data.0.cidade', 'Curitiba')
            ->assertJsonPath('data.0.uf', 'PR');
    }

    public function test_deve_criar_cadastro_com_dados_validos(): void
    {
        $payload = [
            'nome' => 'Maria Oliveira',
            'email' => 'MARIA@EXAMPLE.COM',
            'cep' => '81530000',
            'logradouro' => 'Rua Coronel Francisco Heráclito dos Santos',
            'numero' => '250',
            'complemento' => '',
            'bairro' => 'Jardim das Américas',
            'cidade' => 'Curitiba',
            'uf' => 'pr',
        ];

        $response = $this->postJson('/api/customers', $payload);

        $response
            ->assertCreated()
            ->assertJson([
                'message' => 'Cadastro criado com sucesso.',
                'data' => [
                    'nome' => 'Maria Oliveira',
                    'email' => 'maria@example.com',
                    'cep' => '81530-000',
                    'logradouro' => 'Rua Coronel Francisco Heráclito dos Santos',
                    'numero' => '250',
                    'complemento' => null,
                    'bairro' => 'Jardim das Américas',
                    'cidade' => 'Curitiba',
                    'uf' => 'PR',
                ],
            ]);

        $this->assertDatabaseHas('customers', [
            'nome' => 'Maria Oliveira',
            'email' => 'maria@example.com',
            'cep' => '81530-000',
            'logradouro' => 'Rua Coronel Francisco Heráclito dos Santos',
            'numero' => '250',
            'complemento' => null,
            'bairro' => 'Jardim das Américas',
            'cidade' => 'Curitiba',
            'uf' => 'PR',
        ]);
    }

    public function test_deve_atualizar_cadastro_com_dados_validos(): void
    {
        $customer = Customer::query()->create([
            'nome' => 'Cliente Antigo',
            'email' => 'cliente.antigo@example.com',
            'cep' => '80010-000',
            'logradouro' => 'Praça Tiradentes',
            'numero' => '10',
            'complemento' => 'Casa',
            'bairro' => 'Centro',
            'cidade' => 'Curitiba',
            'uf' => 'PR',
        ]);

        $response = $this->putJson("/api/customers/{$customer->id}", [
            'nome' => 'Cliente Atualizado',
            'email' => 'CLIENTE.ATUALIZADO@EXAMPLE.COM',
            'cep' => '81530000',
            'logradouro' => 'Avenida Coronel Francisco Heráclito dos Santos',
            'numero' => '250',
            'complemento' => '',
            'bairro' => 'Jardim das Américas',
            'cidade' => 'Curitiba',
            'uf' => 'pr',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Cadastro atualizado com sucesso.',
                'data' => [
                    'id' => $customer->id,
                    'nome' => 'Cliente Atualizado',
                    'email' => 'cliente.atualizado@example.com',
                    'cep' => '81530-000',
                    'logradouro' => 'Avenida Coronel Francisco Heráclito dos Santos',
                    'numero' => '250',
                    'complemento' => null,
                    'bairro' => 'Jardim das Américas',
                    'cidade' => 'Curitiba',
                    'uf' => 'PR',
                ],
            ]);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'nome' => 'Cliente Atualizado',
            'email' => 'cliente.atualizado@example.com',
            'cep' => '81530-000',
            'logradouro' => 'Avenida Coronel Francisco Heráclito dos Santos',
            'numero' => '250',
            'complemento' => null,
            'bairro' => 'Jardim das Américas',
            'cidade' => 'Curitiba',
            'uf' => 'PR',
        ]);
    }

    public function test_deve_atualizar_parcialmente_cadastro_com_patch(): void
    {
        $customer = Customer::query()->create([
            'nome' => 'Cliente Patch',
            'email' => 'cliente.patch@example.com',
            'cep' => '80010-000',
            'logradouro' => 'Praça Tiradentes',
            'numero' => '10',
            'complemento' => null,
            'bairro' => 'Centro',
            'cidade' => 'Curitiba',
            'uf' => 'PR',
        ]);

        $response = $this->patchJson("/api/customers/{$customer->id}", [
            'numero' => '999',
            'complemento' => 'Bloco B',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Cadastro atualizado com sucesso.',
                'data' => [
                    'id' => $customer->id,
                    'nome' => 'Cliente Patch',
                    'email' => 'cliente.patch@example.com',
                    'numero' => '999',
                    'complemento' => 'Bloco B',
                ],
            ]);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'nome' => 'Cliente Patch',
            'email' => 'cliente.patch@example.com',
            'numero' => '999',
            'complemento' => 'Bloco B',
        ]);
    }

    public function test_deve_excluir_cadastro(): void
    {
        $customer = Customer::query()->create([
            'nome' => 'Cliente Para Excluir',
            'email' => 'cliente.excluir@example.com',
            'cep' => '80010-000',
            'logradouro' => 'Praça Tiradentes',
            'numero' => '10',
            'complemento' => null,
            'bairro' => 'Centro',
            'cidade' => 'Curitiba',
            'uf' => 'PR',
        ]);

        $response = $this->deleteJson("/api/customers/{$customer->id}");

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Cadastro excluído com sucesso.',
            ]);

        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_deve_rejeitar_payload_invalido_ao_criar_cadastro(): void
    {
        $response = $this->postJson('/api/customers', [
            'nome' => 'A',
            'email' => 'email-invalido',
            'cep' => '123',
            'logradouro' => '',
            'numero' => '',
            'bairro' => '',
            'cidade' => '',
            'uf' => 'Paraná',
        ]);

        $response
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

    public function test_deve_rejeitar_email_duplicado_ao_criar_cadastro(): void
    {
        Customer::query()->create([
            'nome' => 'Cliente Existente',
            'email' => 'cliente@example.com',
            'cep' => '80010-000',
            'logradouro' => 'Praça Tiradentes',
            'numero' => '10',
            'complemento' => null,
            'bairro' => 'Centro',
            'cidade' => 'Curitiba',
            'uf' => 'PR',
        ]);

        $response = $this->postJson('/api/customers', [
            'nome' => 'Novo Cliente',
            'email' => 'cliente@example.com',
            'cep' => '81530-000',
            'logradouro' => 'Rua Coronel Francisco Heráclito dos Santos',
            'numero' => '123',
            'complemento' => null,
            'bairro' => 'Jardim das Américas',
            'cidade' => 'Curitiba',
            'uf' => 'PR',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email',
            ]);
    }

    public function test_deve_rejeitar_email_duplicado_ao_atualizar_cadastro(): void
    {
        Customer::query()->create([
            'nome' => 'Cliente Um',
            'email' => 'cliente.um@example.com',
            'cep' => '80010-000',
            'logradouro' => 'Praça Tiradentes',
            'numero' => '10',
            'complemento' => null,
            'bairro' => 'Centro',
            'cidade' => 'Curitiba',
            'uf' => 'PR',
        ]);

        $customer = Customer::query()->create([
            'nome' => 'Cliente Dois',
            'email' => 'cliente.dois@example.com',
            'cep' => '81530-000',
            'logradouro' => 'Avenida Coronel Francisco Heráclito dos Santos',
            'numero' => '250',
            'complemento' => null,
            'bairro' => 'Jardim das Américas',
            'cidade' => 'Curitiba',
            'uf' => 'PR',
        ]);

        $response = $this->patchJson("/api/customers/{$customer->id}", [
            'email' => 'cliente.um@example.com',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email',
            ]);
    }
}