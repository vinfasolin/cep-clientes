<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CepApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cep.provider' => 'viacep',
            'cep.fallback_provider' => null,
            'cep.viacep.base_url' => 'https://viacep.com.br/ws',
            'cep.viacep.timeout' => 10,
        ]);

        Http::preventStrayRequests();
    }

    public function test_deve_buscar_cep_no_viacep_e_retornar_endereco_formatado(): void
    {
        Http::fake([
            'https://viacep.com.br/ws/81530000/json/' => Http::response([
                'cep' => '81530-000',
                'logradouro' => 'Rua Coronel Francisco Heráclito dos Santos',
                'complemento' => '',
                'bairro' => 'Jardim das Américas',
                'localidade' => 'Curitiba',
                'uf' => 'PR',
                'ibge' => '4106902',
                'gia' => '',
                'ddd' => '41',
                'siafi' => '7535',
            ], 200),
        ]);

        $response = $this->getJson('/api/ceps/81530000');

        $response
            ->assertOk()
            ->assertExactJson([
                'cep' => '81530-000',
                'logradouro' => 'Rua Coronel Francisco Heráclito dos Santos',
                'bairro' => 'Jardim das Américas',
                'cidade' => 'Curitiba',
                'uf' => 'PR',
            ]);

        $this->assertDatabaseHas('cep_caches', [
            'cep' => '81530000',
            'logradouro' => 'Rua Coronel Francisco Heráclito dos Santos',
            'bairro' => 'Jardim das Américas',
            'cidade' => 'Curitiba',
            'uf' => 'PR',
        ]);

        Http::assertSent(
            fn (Request $request): bool => $request->url() === 'https://viacep.com.br/ws/81530000/json/'
        );
    }

    public function test_deve_usar_cache_apos_primeira_consulta_de_cep(): void
    {
        Http::fake([
            'https://viacep.com.br/ws/81530000/json/' => Http::response([
                'cep' => '81530-000',
                'logradouro' => 'Rua Coronel Francisco Heráclito dos Santos',
                'complemento' => '',
                'bairro' => 'Jardim das Américas',
                'localidade' => 'Curitiba',
                'uf' => 'PR',
                'ibge' => '4106902',
                'gia' => '',
                'ddd' => '41',
                'siafi' => '7535',
            ], 200),
        ]);

        $this->getJson('/api/ceps/81530000')
            ->assertOk()
            ->assertJson([
                'cep' => '81530-000',
                'cidade' => 'Curitiba',
                'uf' => 'PR',
            ]);

        $this->getJson('/api/ceps/81530000')
            ->assertOk()
            ->assertJson([
                'cep' => '81530-000',
                'cidade' => 'Curitiba',
                'uf' => 'PR',
            ]);

        Http::assertSentCount(1);
    }

    public function test_deve_retornar_erro_422_quando_cep_for_invalido(): void
    {
        $response = $this->getJson('/api/ceps/123');

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'CEP deve conter exatamente 8 dígitos.',
            ]);

        Http::assertNothingSent();
    }
}