<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Cep\CorreiosCepProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CorreiosCepProviderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        config()->set('cep.provider', 'correios');
        config()->set('cep.correios.base_url', 'https://api.correios.com.br');
        config()->set('cep.correios.token_base_url', 'https://api.correios.com.br');
        config()->set('cep.correios.auth_type', 'contrato');
        config()->set('cep.correios.username', 'usuario-correios');
        config()->set('cep.correios.password', 'codigo-acesso');
        config()->set('cep.correios.contract_number', '1234567890');
        config()->set('cep.correios.contract_dr', '10');
        config()->set('cep.correios.bearer_token', null);
        config()->set('cep.correios.token_cache_key', 'correios:token:test');
        config()->set('cep.correios.token_cache_margin_seconds', 300);
    }

    public function test_can_generate_token_and_lookup_cep_in_correios_provider(): void
    {
        Http::fake([
            'https://api.correios.com.br/token/v1/autentica/contrato' => Http::response([
                'token' => 'token-gerado-pelos-correios',
                'expiraEm' => now()->addHour()->toIso8601String(),
            ]),
            'https://api.correios.com.br/cep/v2/enderecos/01001001' => Http::response([
                'cep' => '01001001',
                'uf' => 'SP',
                'localidade' => 'São Paulo',
                'logradouro' => 'Praça da Sé',
                'bairro' => 'Sé',
            ]),
        ]);

        $provider = app(CorreiosCepProvider::class);
        $address = $provider->find('01001-001');

        $this->assertSame('01001-001', $address->toArray()['cep']);
        $this->assertSame('Praça da Sé', $address->logradouro);
        $this->assertSame('Sé', $address->bairro);
        $this->assertSame('São Paulo', $address->cidade);
        $this->assertSame('SP', $address->uf);

        Http::assertSentCount(2);
        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://api.correios.com.br/token/v1/autentica/contrato'
                && $request->method() === 'POST'
                && $request['numero'] === '1234567890'
                && $request['dr'] === 10;
        });
        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://api.correios.com.br/cep/v2/enderecos/01001001'
                && $request->hasHeader('Authorization', 'Bearer token-gerado-pelos-correios');
        });
    }

    public function test_can_use_preconfigured_bearer_token_without_requesting_token_endpoint(): void
    {
        config()->set('cep.correios.bearer_token', 'token-fixo-ou-subdelegado');

        Http::fake([
            'https://api.correios.com.br/cep/v2/enderecos/80010000' => Http::response([
                'cep' => '80010000',
                'uf' => 'PR',
                'localidade' => 'Curitiba',
                'logradouro' => 'Praça Tiradentes',
                'bairro' => 'Centro',
            ]),
        ]);

        $provider = app(CorreiosCepProvider::class);
        $address = $provider->find('80010-000');

        $this->assertSame('Curitiba', $address->cidade);
        $this->assertSame('PR', $address->uf);

        Http::assertSentCount(1);
        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://api.correios.com.br/cep/v2/enderecos/80010000'
                && $request->hasHeader('Authorization', 'Bearer token-fixo-ou-subdelegado');
        });
    }
}
