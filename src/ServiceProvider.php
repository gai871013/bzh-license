<?php

namespace Gai871013\License;

use Illuminate\Support\Arr;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([__DIR__ . '/config/license.php' => config_path('license.php'),], 'config');
        $this->publishes([__DIR__ . '/cert/public.pem' => storage_path('cert/public.pem')], 'license');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/license.php', 'license');
        $this->app->singleton(License::class, function () {
            return new License();
        });

        $this->app->alias(License::class, 'License');
    }


    public function provides(): array
    {
        return [License::class, 'License'];
    }
}
