<?php

namespace PhonePe;
use Illuminate\Support\ServiceProvider;
class LaravelPhonePeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Optionally, you can merge config (not necessary if only publishing)
        $this->mergeConfigFrom(
            __DIR__ . '/config/phonepe.php', 'phonepe'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish configuration file
        $this->publishes([
            __DIR__ . '/config/phonepe.php' => config_path('phonepe.php'),
        ], 'phonepe-config');
    }
}
