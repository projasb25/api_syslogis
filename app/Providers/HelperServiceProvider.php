<?php

namespace App\Providers;

use App\Helpers\QueryHelper;
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
        // Creacion de querys
        $this->app->bind('QueryHelper', function () {
            return new QueryHelper;
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
