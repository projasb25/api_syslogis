<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

Route::post('test', function(){
    $fechas = [];
    $cuadro_detalle = DB::select("CALL SP_REP_EFICIENCIA_V2_PT2(?,?,?,?,?,'RECOLECCION')",[1, 30, '2021-07-01', '2021-07-03', 'rpjas']);
    $tempArr = array_unique(array_column($cuadro_detalle, 'fecha_entrega'));
    print_r(array_intersect_key($cuadro_detalle, $tempArr));
    dd($tempArr);
    // foreach ($cuadro_detalle as $key => $value) {
    //     echo $value->fecha_entrega.'</br>';
    //     if(!array_search($value->fecha_entrega, $fechas)) {
    //         array_push($fechas, $value->fecha_entrega);
    //     }
    // }
    dd($fechas);
    // $new = new \App\Models\Services\IntegracionService(new \App\Models\Repositories\IntegracionRepository());
    // $new->integracionRipley();
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
    Route::get('/motivos', 'ShippingController@getMotivosDist');
    Route::get('/motivos/{tipo}', 'ShippingController@getMotivos');
    Route::post('/imagen', 'ShippingController@grabarImagen');
    Route::get('/imagen/{id_shipping_order}/{guide_number}', 'ShippingController@getImagen')->where('id_shipping_order', '[0-9]+');
    Route::post('/actualizar', 'ShippingController@actualizar');
    //     Route::get('/agencias/{idcliente}', 'PedidoController@getAgencias')->where('idcliente', '[0-9]+');
});

/**AuthController
 */

Route::group(['middleware' => 'api', 'prefix' => 'web', 'namespace' => 'Web'], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::get('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::get('validateToken', 'AuthController@me');
    Route::post('change', 'AuthController@change');
    Route::get('properties', 'AuthController@properties');

    Route::get('guide/status/{id_guide}', 'PublicoController@guide_status');

    // Route::group(['middleware' => ['assign.guard:users'], 'prefix' => 'public'], function() {
    //     Route::post('massive_load', 'MassiveLoadController@public_massive_load');
    // });

    Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'main'], function() {
        Route::post('', 'MainController@index');
        Route::post('/simpleTransaction', 'MainController@simpleTransaction');
        Route::post('paginated', 'MainController@paginated');
    });

    Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'massive_load'], function() {
        Route::post('', 'MassiveLoadController@index');
        Route::post('process', 'MassiveLoadController@process');
        Route::post('print/cargo', 'MassiveLoadController@print_cargo');
        Route::post('print/marathon', 'MassiveLoadController@print_marathon');
    });

    Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'collect'], function() {
        Route::post('load', 'CollectController@load');
        Route::post('process', 'CollectController@process');
        // Route::post('print/cargo', 'MassiveLoadController@print_cargo');
        // Route::post('print/marathon', 'MassiveLoadController@print_marathon');
    });

    Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'shipping'], function() {
        Route::post('print/hoja_ruta', 'ShippingController@print_hoja_ruta');
        Route::post('/imagen', 'ShippingController@grabarImagen');
        // Route::post('process', 'ShippingController@process');
    });

    Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'reportes'], function() {
        Route::post('control', 'ReporteController@reporte_control');
        Route::post('torre_control', 'ReporteController@reporte_torre_control');
        Route::post('control_sku', 'ReporteController@reporte_control_sku');
        Route::post('control_proveedor', 'ReporteController@control_proveedor');
        Route::post('img_monitor', 'ReporteController@img_monitor');
        Route::post('reporte_recoleccion', 'ReporteController@reporte_recoleccion');
        Route::post('reporte_eficiencia', 'ReporteController@reporte_eficiencia');
        Route::post('reporte_data_carga', 'ReporteController@reporte_data_carga');
    });
});


/**
 *  Rutas para la integracion
 */
Route::group(['middleware' => 'api', 'prefix' => 'integracion', 'namespace' => 'Integration'], function ($router) {
    Route::post('login', 'IntegrationAuthController@login');

    Route::group(['middleware' => ['api'], 'prefix' => 'carga'], function() {
        Route::post('procesar', 'IntegrationController@index');
        // TODO: crear comando para procesar a recoleccion
        Route::post('test', 'IntegrationController@procesar');
        // TODO: crear comando para procesar a distribucion
        Route::post('test_dist', 'IntegrationController@procesar_distribucion');
    });
});
