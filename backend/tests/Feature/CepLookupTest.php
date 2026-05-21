<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CepCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CepLookupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cep.provider' => 'fake',
            'cep.fallback_provider' => null,
        ]);
    }

    public function test_can_lookup_a_valid_cep_using_fake_provider(): void
    {
        $response = $this->getJson('/api/ceps/80010000');

        $response->assertOk()
            ->assertJson([
                'cep' => '80010-000',
                'logradouro' => 'Praça Tiradentes',
                'bairro' => 'Centro',
                'cidade' => 'Curitiba',
                'uf' => 'PR',
            ]);
    }

    public function test_lookup_stores_cep_cache(): void
    {
        $this->getJson('/api/ceps/80010000')
            ->assertOk();

        $this->assertDatabaseHas('cep_caches', [
            'cep' => '80010000',
            'cidade' => 'Curitiba',
            'uf' => 'PR',
        ]);
    }

    public function test_lookup_uses_cached_cep_when_available(): void
    {
        CepCache::query()->create([
            'cep' => '12345678',
            'logradouro' => 'Rua em Cache',
            'bairro' => 'Bairro Cache',
            'cidade' => 'Cidade Cache',
            'uf' => 'SC',
            'raw_response' => [],
        ]);

        $this->getJson('/api/ceps/12345678')
            ->assertOk()
            ->assertJsonPath('logradouro', 'Rua em Cache')
            ->assertJsonPath('cidade', 'Cidade Cache')
            ->assertJsonPath('uf', 'SC');
    }

    public function test_returns_validation_error_when_cep_is_invalid(): void
    {
        $this->getJson('/api/ceps/123')
            ->assertStatus(422)
            ->assertJsonPath('message', 'CEP deve conter exatamente 8 dígitos.');
    }

    public function test_returns_not_found_when_fake_provider_does_not_have_cep(): void
    {
        $this->getJson('/api/ceps/99999999')
            ->assertStatus(404);
    }
}