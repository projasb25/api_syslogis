<?php

use App\Http\Controllers\Integration\IntegrationController;
use App\Http\Controllers\Web\CompleteLoadController;
use App\Models\Repositories\Integration\MainRepository;
use App\Models\Repositories\Web\CompleteLoadRepository;
use App\Models\Services\Integration\MainService;
use App\Models\Services\Web\CompleteLoadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Location\Coordinate;
use Location\Distance\Vincenty;

// /*
// |--------------------------------------------------------------------------
// | API Routes
// |--------------------------------------------------------------------------
// |
// | Here is where you can register API routes for your application. These
// | routes are loaded by the RouteServiceProvider within a group which
// | is assigned the "api" middleware group. Enjoy building your API!
// |
// */

// Route::post('test', function(Request $request) {
//     // $params['type'] = 'Provincia';
//     // $params['service'] = ['Envío a domicilio', 'Retiro en tienda'];
//     // $params['organization'] = 65;
//     // $params['name'] = 'InRetail Provincia';

//     // $db = new \App\Models\Services\Integration\MainService(new \App\Models\Repositories\Integration\MainRepository());
//     // $res = $db->newInretailDistribucion($params);

//     $params['id_complete_load'] = 5;
//     $service = new CompleteLoadService(new CompleteLoadRepository());
//     $res = $service->procesarDistribucion($params);
//     dd($res);
// });

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
//     Route::post('login', 'AuthController@login');
//     Route::get('logout', 'AuthController@logout');
//     Route::post('refresh', 'AuthController@refresh');
//     Route::post('me', 'AuthController@me');
// });

// // Route::group(['middleware' => 'auth:api', 'prefix' => 'conductor'], function () {
// //     Route::get('/ofertas', 'ConductorController@listarOfertas');
// //     Route::post('/actualizarEstado', 'ConductorController@actualizarEstado');
// // });

// Route::group(['middleware' => ['assign.guard:drivers','jwt.auth'], 'prefix' => 'conductor'], function () {
//     Route::get('/ofertas', 'DriverController@listarOfertas');
//     Route::post('/actualizarEstado', 'DriverController@actualizarEstado');
// });

// Route::group(['middleware' => ['assign.guard:drivers','jwt.auth'], 'prefix' => 'novedad'], function () {
//     Route::post('/insertar', 'ShippingController@novedad_insertar');
// });

// Route::group(['middleware' => ['assign.guard:drivers','jwt.auth'], 'prefix' => 'envio'], function () {
//     Route::get('/aceptar/{idofertaenvio}', 'ShippingController@aceptar');
//     Route::get('/rechazar/{idofertaenvio}', 'ShippingController@rechazar');
//     Route::get('/rutas/{idofertaenvio}', 'ShippingController@listarRutas');
//     Route::get('/iniciar/{idofertaenvio}', 'ShippingController@iniciar')->where('idofertaenvio', '[0-9]+');
//     Route::get('/finalizar/{id_shipping_order}', 'ShippingController@finalizar')->where('id_shipping_order', '[0-9]+');
//     // Route::get('/coordenadas/{idofertaenvio}', 'EnviosController@coordenadas')->where('idofertaenvio', '[0-9]+');
// });

// Route::group(['middleware' => ['assign.guard:drivers','jwt.auth'], 'prefix' => 'pedido'], function () {
//     Route::get('/motivos', 'ShippingController@getMotivosDist');
//     Route::get('/motivos/{tipo}', 'ShippingController@getMotivos');
//     Route::post('/imagen', 'ShippingController@grabarImagen');
//     Route::get('/imagen/{id_shipping_order}/{guide_number}', 'ShippingController@getImagen')->where('id_shipping_order', '[0-9]+');
//     Route::post('/actualizar', 'ShippingController@actualizar');
//     //     Route::get('/agencias/{idcliente}', 'PedidoController@getAgencias')->where('idcliente', '[0-9]+');
// });

// /**AuthController
//  */

// Route::group(['middleware' => 'api', 'prefix' => 'web', 'namespace' => 'Web'], function ($router) {
//     Route::post('login', 'AuthController@login');
//     Route::get('logout', 'AuthController@logout');
//     Route::post('refresh', 'AuthController@refresh');
//     Route::get('validateToken', 'AuthController@me');
//     Route::post('change', 'AuthController@change');
//     Route::get('properties', 'AuthController@properties');

//     Route::get('guide/status/{id_guide}', 'PublicoController@guide_status');

//     Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'main'], function() {
//         Route::post('', 'MainController@index');
//         Route::post('/simpleTransaction', 'MainController@simpleTransaction');
//         Route::post('paginated', 'MainController@paginated');
//     });

//     Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'massive_load'], function() {
//         Route::post('', 'MassiveLoadController@index');
//         Route::post('process', 'MassiveLoadController@process');
//         Route::post('print/cargo', 'MassiveLoadController@print_cargo');
//         Route::get('print/cargo/{id_guide}', 'MassiveLoadController@print_cargo_guide');
//         Route::post('print/marathon', 'MassiveLoadController@print_marathon');
//         Route::post('unitaria' , 'MassiveLoadController@unitaria');
//     });

//     Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'collect'], function() {
//         Route::post('load', 'CollectController@load');
//         Route::post('process', 'CollectController@process');
//         // Route::post('print/cargo', 'MassiveLoadController@print_cargo');
//         // Route::post('print/marathon', 'MassiveLoadController@print_marathon');
//     });

//     Route::group(['middleware' => ['api'], 'prefix' => 'complete'], function() {
//         Route::post('load', [CompleteLoadController::class, 'load']);
//         Route::post('load/process', [CompleteLoadController::class, 'process']);
//         Route::post('load/process_distribution', [CompleteLoadController::class, 'process_distribution']);
//     });

//     Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'shipping'], function() {
//         Route::post('print/hoja_ruta', 'ShippingController@print_hoja_ruta');
//         Route::post('/imagen', 'ShippingController@grabarImagen');
//         // Route::post('process', 'ShippingController@process');
//     });

//     Route::group(['middleware' => ['assign.guard:users','jwt.auth'], 'prefix' => 'reportes'], function() {
//         Route::post('control', 'ReporteController@reporte_control');
//         Route::post('torre_control', 'ReporteController@reporte_torre_control');
//         Route::post('control_sku', 'ReporteController@reporte_control_sku');
//         Route::post('control_proveedor', 'ReporteController@control_proveedor');
//         Route::post('img_monitor', 'ReporteController@img_monitor');
//         Route::post('reporte_recoleccion', 'ReporteController@reporte_recoleccion');
//         Route::post('reporte_eficiencia', 'ReporteController@reporte_eficiencia');
//         Route::post('reporte_data_carga', 'ReporteController@reporte_data_carga');
//     });
// });


/**
 *  Rutas para la integracion
 */
Route::group(['middleware' => 'api', 'prefix' => 'integracion', 'namespace' => 'Integration'], function ($router) {
    Route::post('login', 'IntegrationAuthController@login');

    Route::group(['middleware' => ['assign.guard:integration_users','jwt.auth']], function(){
        Route::post('registrar', 'IntegrationController@registrar');
        Route::get('consultar/{seg_code}', 'IntegrationController@consultar');
        Route::get('consultar/cargo/{seg_code}', 'IntegrationController@exportar_cargo');
    });

    Route::group(['middleware' => ['api'], 'prefix' => 'carga'], function() {
        Route::post('procesar', [IntegrationController::class, 'index']);
    });

});
