<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Location\Coordinate;
use Location\Distance\Vincenty;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::get('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
});

// Route::group(['middleware' => 'auth:api', 'prefix' => 'conductor'], function () {
//     Route::get('/ofertas', 'ConductorController@listarOfertas');
//     Route::post('/actualizarEstado', 'ConductorController@actualizarEstado');
// });

Route::group(['middleware' => ['assign.guard:drivers','jwt.auth'], 'prefix' => 'conductor'], function () {
    Route::get('/ofertas', 'DriverController@listarOfertas');
    Route::post('/actualizarEstado', 'DriverController@actualizarEstado');
});

Route::group(['middleware' => ['assign.guard:drivers','jwt.auth'], 'prefix' => 'envio'], function () {
    Route::get('/aceptar/{idofertaenvio}', 'ShippingController@aceptar');
    Route::get('/rechazar/{idofertaenvio}', 'ShippingController@rechazar');
    Route::get('/rutas/{idofertaenvio}', 'ShippingController@listarRutas');
    Route::get('/iniciar/{idofertaenvio}', 'ShippingController@iniciar')->where('idofertaenvio', '[0-9]+');
    // Route::get('/finalizar/{idenvio}', 'EnviosController@finalizar')->where('idenvio', '[0-9]+');
    // Route::get('/coordenadas/{idofertaenvio}', 'EnviosController@coordenadas')->where('idofertaenvio', '[0-9]+');
});

Route::group(['middleware' => ['assign.guard:drivers','jwt.auth'], 'prefix' => 'pedido'], function () {
    Route::get('/motivos', 'ShippingController@getMotivos');
    Route::post('/imagen', 'ShippingController@grabarImagen');
    Route::get('/imagen/{id_shipping_order_detail}', 'ShippingController@getImagen')->where('id_shipping_order_detail', '[0-9]+');
    Route::post('/actualizar', 'ShippingController@actualizar');
    //     Route::get('/agencias/{idcliente}', 'PedidoController@getAgencias')->where('idcliente', '[0-9]+');
});

/**
 *  || RUTAS PARA LA WEB ||
 */

Route::group(['middleware' => 'api', 'prefix' => 'web', 'namespace' => 'Web'], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::get('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::get('validateToken', 'AuthController@me');
    Route::post('change', 'AuthController@change');

    Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'main'], function() {
        Route::post('', 'MainController@index');
        Route::post('/simpleTransaction', 'MainController@simpleTransaction');
    });

    Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'massive_load'], function() {
        Route::post('', 'MassiveLoadController@index');
        Route::post('process', 'MassiveLoadController@process');
        Route::post('print/cargo', 'MassiveLoadController@print_cargo');
    });

    Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'shipping'], function() {
        Route::post('print/hoja_ruta', 'ShippingController@print_hoja_ruta');
        // Route::post('process', 'ShippingController@process');
    });
});
