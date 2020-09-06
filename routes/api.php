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

Route::group(['middleware' => 'auth:api', 'prefix' => 'conductor'], function () {
    Route::get('/ofertas', 'ConductorController@listarOfertas');
    Route::post('/actualizarEstado', 'ConductorController@actualizarEstado');
});

Route::group(['middleware' => 'auth:api', 'prefix' => 'envio'], function () {
    Route::get('/aceptar/{idofertaenvio}', 'EnviosController@aceptar');
    Route::get('/rechazar/{idofertaenvio}', 'EnviosController@rechazar');
    Route::get('/rutas/{idofertaenvio}', 'EnviosController@listarRutas');
    Route::get('/iniciar/{idenvio}', 'EnviosController@iniciar')->where('idenvio', '[0-9]+');
    Route::get('/finalizar/{idenvio}', 'EnviosController@finalizar')->where('idenvio', '[0-9]+');
    Route::get('/coordenadas/{idofertaenvio}', 'EnviosController@coordenadas')->where('idofertaenvio', '[0-9]+');
});

Route::group(['middleware' => 'auth:api', 'prefix' => 'pedido'], function () {
    Route::get('/imagen/{idpedido_detalle}', 'PedidoController@getImagen')->where('idpedido_detalle', '[0-9]+');
    Route::post('/imagen', 'PedidoController@grabarImagen');
    Route::post('/actualizar', 'PedidoController@actualizar');
    Route::get('/motivos/{idcliente}', 'PedidoController@getMotivos')->where('idcliente', '[0-9]+');
    Route::get('/agencias/{idcliente}', 'PedidoController@getAgencias')->where('idcliente', '[0-9]+');
});

/**
 *  || RUTAS PARA LA WEB ||
 */

Route::group(['middleware' => 'api', 'prefix' => 'web', 'namespace' => 'Web'], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::get('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');

    Route::post('main', 'MainController@index');
});
