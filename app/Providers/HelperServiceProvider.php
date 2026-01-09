<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * Lista de helpers a cargar
     */
    protected $helpers = [
        'GeneralHelper',
        'VentasHelper', // Agrega aquí tu nuevo helper
        // Agrega más helpers según necesites
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        foreach ($this->helpers as $helper) {
            $helperPath = app_path('Helpers/' . $helper . '.php');
            
            if (file_exists($helperPath)) {
                require_once $helperPath;
            }
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}