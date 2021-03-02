<?php

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

Route::post('test', function(Request $request){
    $pdf = App::make('snappy.pdf.wrapper');
    $pdf->loadHTML('<h1>Test</h1>');
    return $pdf->inline();
});
Route::get('pdf23', function(Request $request){
    $pdf = PDF::loadView('pdf.orden_compra.detalle');
    $pdf->setOptions([
        'footer-right' => '[page]',
        'margin-bottom' => 20
    ]);
    return $pdf->download('invoice.pdf');

    $pdf = App::make('snappy.pdf.wrapper');
    $pdf->loadView('pdf.orden_compra.detalle');
    $pdf->setOption('margin-top',20);
    return $pdf->inline();
});

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
    Route::get('/finalizar/{id_shipping_order}', 'ShippingController@finalizar')->where('id_shipping_order', '[0-9]+');
    // Route::get('/coordenadas/{idofertaenvio}', 'EnviosController@coordenadas')->where('idofertaenvio', '[0-9]+');
});

Route::group(['middleware' => ['assign.guard:drivers','jwt.auth'], 'prefix' => 'pedido'], function () {
    Route::get('/motivos', 'ShippingController@getMotivos');
    Route::post('/imagen', 'ShippingController@grabarImagen');
    Route::get('/imagen/{id_shipping_order}/{guide_number}', 'ShippingController@getImagen')->where('id_shipping_order', '[0-9]+');
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
    Route::get('properties', 'AuthController@properties');

    Route::get('guide/status/{id_guide}', 'PublicoController@guide_status');

    Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'main'], function() {
        Route::post('', 'MainController@index');
        Route::post('/simpleTransaction', 'MainController@simpleTransaction');
        Route::post('paginated', 'MainController@paginated');
    });

    Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'bill_load'], function() {
        Route::post('', 'BillLoadController@index');
        Route::post('process', 'BillLoadController@process');
    });

    Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'purchase_order'], function() {
        Route::post('', 'PurchaseOrderController@index');
        Route::post('process', 'PurchaseOrderController@process');
        Route::post('cancel', 'PurchaseOrderController@cancel');
        Route::post('print/detail', 'PurchaseOrderController@print_detail');
    });

    Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'massive_load'], function() {
        Route::post('', 'MassiveLoadController@index');
        Route::post('process', 'MassiveLoadController@process');
        Route::post('print/cargo', 'MassiveLoadController@print_cargo');
        Route::post('print/marathon', 'MassiveLoadController@print_marathon');
    });

    Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'shipping'], function() {
        Route::post('print/hoja_ruta', 'ShippingController@print_hoja_ruta');
        Route::post('/imagen', 'ShippingController@grabarImagen');
        // Route::post('process', 'ShippingController@process');
    });

    Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'reportes'], function() {
        Route::post('inventario', 'ReporteController@reporte_inventario');
        Route::post('inventario_producto', 'ReporteController@reporte_inventario_producto');


        // Route::post('control', 'ReporteController@reporte_control');
        // Route::post('torre_control', 'ReporteController@reporte_torre_control');
        // Route::post('control_sku', 'ReporteController@reporte_control_sku');
        // Route::post('control_proveedor', 'ReporteController@control_proveedor');
        // Route::post('img_monitor', 'ReporteController@img_monitor');
    });
});
