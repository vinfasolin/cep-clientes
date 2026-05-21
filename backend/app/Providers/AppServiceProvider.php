<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Cep\CepProviderInterface;
use App\Services\Cep\CepService;
use App\Services\Cep\CorreiosCepProvider;
use App\Services\Cep\FakeCepProvider;
use App\Services\Cep\ViaCepProvider;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CepProviderInterface::class, function () {
            return $this->makeCepProvider((string) config('cep.provider', 'viacep'));
        });

        $this->app->bind(CepService::class, function () {
            $provider = $this->makeCepProvider((string) config('cep.provider', 'viacep'));

            $fallbackProviderName = config('cep.fallback_provider');

            $fallbackProvider = null;

            if (is_string($fallbackProviderName) && $fallbackProviderName !== '') {
                $fallbackProvider = $this->makeCepProvider($fallbackProviderName);
            }

            return new CepService(
                provider: $provider,
                fallbackProvider: $fallbackProvider,
            );
        });
    }

    public function boot(): void
    {
        //
    }

    private function makeCepProvider(string $provider): CepProviderInterface
    {
        return match ($provider) {
            'viacep' => $this->app->make(ViaCepProvider::class),
            'correios' => $this->app->make(CorreiosCepProvider::class),
            'fake' => $this->app->make(FakeCepProvider::class),
            default => throw new InvalidArgumentException("Provider de CEP inválido: {$provider}"),
        };
    }
}