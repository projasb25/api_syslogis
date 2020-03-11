<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
    Route::post('/aceptar', 'EnviosController@aceptar');
});
