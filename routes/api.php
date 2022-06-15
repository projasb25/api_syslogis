<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BillLoadController;
use App\Http\Controllers\Web\MainController;
use App\Http\Controllers\Web\MassiveLoadController;
use App\Http\Controllers\Web\PurchaseOrderController;
use App\Http\Controllers\Web\ReporteController;
use App\Http\Controllers\Web\ShippingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
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

Route::group(['middleware' => 'api', 'prefix' => 'web', 'namespace' => 'Web'], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::get('logout', [AuthController::class, 'logout']);
    Route::get('validateToken', [AuthController::class, 'me']);
    Route::get('properties', [AuthController::class, 'properties']);

    Route::get('guide/status/{id_guide}', 'PublicoController@guide_status');

    Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'main'], function() {
        Route::post('', [MainController::class, 'index']);
        Route::post('simpleTransaction', [MainController::class, 'simpleTransaction']);
        Route::post('paginated', [MainController::class, 'paginated']);
    });

    Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'bill_load'], function() {
        Route::post('', [BillLoadController::class, 'index']);
        Route::post('process', [BillLoadController::class, 'process']);
    });

    Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'purchase_order'], function() {
        Route::post('', [PurchaseOrderController::class, 'index']);
        Route::post('process', [PurchaseOrderController::class, 'process']);
        Route::post('cancel', [PurchaseOrderController::class, 'cancel']);
        Route::post('print/detail', [PurchaseOrderController::class, 'print_detail']);
    });

    Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'massive_load'], function() {
        Route::post('', [MassiveLoadController::class, 'index']);
        Route::post('process', [MassiveLoadController::class, 'process']);
        Route::post('print/cargo', [MassiveLoadController::class, 'print_cargo']);
        Route::post('print/marathon', [MassiveLoadController::class, 'print_marathon']);
    });

    Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'shipping'], function() {
        Route::post('print/hoja_ruta', [ShippingController::class, 'print_hoja_ruta']);
        Route::post('/imagen', [ShippingController::class, 'grabarImagen']);
    });

    Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'reportes'], function() {
        Route::post('inventario', [ReporteController::class, 'reporte_inventario']);
        Route::post('inventario_producto', [ReporteController::class, 'reporte_inventario_producto']);
    });
});
