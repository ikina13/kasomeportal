<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Suppress Carbon deprecation warnings for PHP 8.1+ compatibility
        // This is a known issue with Carbon 2.x and PHP 8.1+
        // The functionality works correctly; it's just a deprecation notice
        if (PHP_VERSION_ID >= 80100) {
            set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                // Suppress Carbon deprecation warnings specifically
                if ($errno === E_DEPRECATED && 
                    str_contains($errfile, 'carbon') && 
                    str_contains($errstr, 'createFromTimestamp')) {
                    return true; // Suppress this specific warning
                }
                return false; // Let other errors through
            }, E_DEPRECATED);
        }
    }
}
