<?php

namespace App\Providers;

use App\Helpers\ArrayHelper;
use App\Helpers\FCMHelper;
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

        // Array Helper
        $this->app->bind('ArrayHelper', function () {
            return new ArrayHelper;
        });

        // FCM Helper
        $this->app->bind('FCMHelper', function () {
            return new FCMHelper;
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
