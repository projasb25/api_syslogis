<?php

namespace App\Providers;

use App\Helpers\Response\ResponseHelper;
use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Creacion de respuestas
        $this->app->bind('ResponseHelper', function () {
            return new ResponseHelper;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
