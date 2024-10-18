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
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/phonepe.php' => config_path('phonepe.php'),
        ], 'phonepe-config');
    }
}
