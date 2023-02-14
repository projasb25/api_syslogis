<?php

use App\Helpers\ArrayHelper;
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
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
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

Route::post('test', function (Request $request) {
    $token = 'k3SIBJ4IMdKpw5EBPSseB9ziOXUESkTfpbH20uUusiYJcl70sks19J5aPoicwvNSiD4EsD+tyodMlMe+iGNWZdmdj2bvvVgNKvxPmvGBHupqccqapUtqFYHM+fw8hQ0egvwWyIRmUPnydA3Yrbkaz71xnrbpXLR3iTaU9SQDwlQ6GM7/s64kCzJ5C6L7xs6zdP5NSBD2Nc9Wd58jeBI3LA==';
    $client = new SoapClient("http://70.35.202.222/wsnexus/ControladorWSCliente.asmx?WSDL", ['trace' => true]);

    $xmlr = new SimpleXMLElement("<RAIZ></RAIZ>");
    $xmlr->addChild('GUID', $token);
    $xmlr->addChild('DepartamentoClienteInicial', 'QYX');
    $xmlr->addChild('DepartamentoClienteFinal', 'QYX');
    $xmlr->addChild('ReferenciaEntregaInicial', 'RP20230204212705');
    $xmlr->addChild('ReferenciaEntregaFinal', 'RP20230204212705');
    $xmlr->addChild('FechaInicial', '2023-02-01');
    $xmlr->addChild('fechafinal', '2023-02-13');
    $xmlr->addChild('IncluirAnexas', 'true');
    $xmlr->addChild('Pendientes', 'false');

    $params = new stdClass();
    $params->xml = $xmlr->asXML();
    $string2 = "<![CDATA[" . $xmlr->asXML() . "]]>";

    $param = array(
        new SoapVar($token, XSD_STRING, null, null, 'GUID'),
        new SoapVar('0082713127', XSD_STRING, null, null, 'Referencia'),
    );

    $data = array(
        // new SoapVar(array(
        new SoapVar($token, XSD_STRING, null, null, 'ns1:GUID'),
        new SoapVar('0082713127', XSD_STRING, null, null, 'ns1:Referencia'),
        // ), SOAP_ENC_OBJECT, null, null, 'ns1:DatosConsulta')
    );

    $data2 = array(
        new SoapVar(array(
            new SoapVar($string2, 147),
        ), SOAP_ENC_OBJECT, null, null, 'ns1:Valor')
    );

    $out = new SoapVar($data2, SOAP_ENC_OBJECT, null, null, null, 'http://www.direcline.com/');
    // dd();

    $res = $client->DescargarImagenes($out);
    // $res = $client->__soapCall('ObtenerExpedicionPorReferencia', [$param2]);
    // dd($client->__getLastRequest());

    $xml = simplexml_load_string($res->DescargarImagenesResult);
    $data = json_decode(json_encode($xml), TRUE);

    if (!isset($data['IMAGEN']) && !count($data['IMAGEN'])){
        dd('si hay imagenes');
    }
    dd(isset($data['IMAGENS']));
    
    $destination_path = Storage::disk('imagenes')->getAdapter()->getPathPrefix();

    $image = base64_decode($data['IMAGEN'][1]['BASE64']);
    $resize = Image::make($image);

    $resize->resize(720, 720, function ($constraint) {
        $constraint->aspectRatio();
    })->save($destination_path . '/' . 'test.png');
    
    // Storage::disk('public')->put('test.jpg', $image);
    

    dd($data['IMAGEN'][0]['BASE64']);














    $params = array(
        'GUID' => $token,
        'DepartamentoClienteInicial' => '',
        'DepartamentoClienteFinal' => '',
        'ReferenciaEntregaInicial' => '0082713127',
        'ReferenciaEntregaFinal' => '0082713127',
        'FechaInicial' => date('Y-m-d'),
        'fechafinal' => date('Y-m-d'),
        'IncluirAnexas' => 'true',
        'Pendientes' => 'false',

    );




    $xmlr = new SimpleXMLElement("<RAIZ></RAIZ>");
    $xmlr->addChild('GUID', $token);
    $xmlr->addChild('DepartamentoClienteInicial', '');
    $xmlr->addChild('DepartamentoClienteFinal', '');
    $xmlr->addChild('ReferenciaEntregaInicial', '0082713127');
    $xmlr->addChild('ReferenciaEntregaFinal', '0082713127');
    $xmlr->addChild('FechaInicial', date('Y-m-d'));
    $xmlr->addChild('fechafinal', date('Y-m-d'));
    $xmlr->addChild('IncluirAnexas', 'true');
    $xmlr->addChild('Pendientes', 'false');

    $params = new stdClass();
    $params->xml = $xmlr->asXML();

    $string = '<![CDATA[<?xml version="1.0" encoding="iso-8859-1"?><RAIZ><GUID>DwbrztEXJ7GdHVhcNRgVajAWyH2dw2PrsFfRatJQxXatOYlW/oM/jyi+OVTPCV+qiNjVHKqpYYqMhg3dj0LkpRtHOaUqsdQqf6Fl3yLwMZDFoIeQ7n0y8r+FkzhJ3drV+7OK30VOBVPv7esI7uxmFwoYQu8AozFcjKKx3JaDGpLsYUMT2OlvnGNwgOO9Dwo+dP5NSBD2Nc9Wd58jeBI3LA==</GUID>
    <DepartamentoClienteInicial></DepartamentoClienteInicial>
    <DepartamentoClienteFinal></DepartamentoClienteFinal>
    <ReferenciaEntregaInicial>00110140950001</ReferenciaEntregaInicial>
    <ReferenciaEntregaFinal>00110140950001</ReferenciaEntregaFinal>
    <FechaInicial>2023-01-01</FechaInicial>
    <fechafinal>2023-02-20</fechafinal>
    <IncluirAnexas>true</IncluirAnexas>
    <Pendientes>false</Pendientes>
    </RAIZ>]]>';
    $string2 = "<![CDATA[$params->xml]]>";

    $parm = array(
        new SoapVar(
            array(
                new SoapVar($string, 147)
            ),
            SOAP_ENC_OBJECT,
            null,
            null,
            'Valor'
        )
        // array(
        // new SoapVar('123', XSD_STRING, null, null, 'customerNo' ),
        // new SoapVar('THIS', XSD_STRING, null, null, 'selection' ),
        // new SoapVar('THAaaaT', XSD_STRING, null, null, 'selection' )
    );
    // $parm[] = new SoapVar('123', XSD_STRING, null, null, 'customerNo' );
    // $parm[] = new SoapVar('THIS', XSD_STRING, null, null, 'selection' );
    // $parm[] = new SoapVar('THAT', XSD_STRING, null, null, 'selection' );

    $pa = array(new SoapVar($string, 147, NULL, NULL, 'Valor'));

    // dd($pa);
    // $res = $client->__soapCall('ObtenerExpedicionPorReferencia', array(['GUID' => $token, 'Referencia' => '0082713127']));

    $resp = $client->DescargarImagenes(new SoapVar($parm, SOAP_ENC_OBJECT));
    // dd($resp);
    dd($client->__getLastRequest());


    $soapvar = new SoapVar($string2, 147);
    $params = array("Valor" => $soapvar);
    // dd($params);

    $res = $client->__soapCall('DescargarImagenes', array(['Valor' => $params]));
    // dd($result);
    // $xml = simplexml_load_string($result);








    // creating object of SimpleXMLElement
    $xml_data = new SimpleXMLElement('<?xml version="1.0" encoding="iso-8859-1"?><RAIZ></RAIZ>');

    // function call to convert array to xml
    ArrayHelper::array_to_xml($params, $xml_data);

    $test = $xml_data->asXML();
    $string = "<![CDATA[$test]]>";
    // dd($string);
    // dd($string);
    // dd($xml_data->asXML());
    // dd(simplexml_load_string($xml_data->data));

    // $res = $client->__soapCall('ObtenerExpedicionPorReferencia', array(['GUID' => $token, 'Referencia' => '0082713127']));
    // $res = $client->__soapCall('ObtenerEstructuraXML', array(['NombreMetodo' => 'DescargarImagenes']));
    $res = $client->__soapCall('DescargarImagenes', array(['Valor' => $string]));

    // $xml = simplexml_load_string($res->ObtenerExpedicionPorReferenciaResult);
    // $xml = simplexml_load_string($res->ObtenerEstructuraXMLResult);

    $xml = simplexml_load_string($res->DescargarImagenesResult);
    dd($client->__getLastRequest());

    // $json = json_encode($xml);
    // $array = json_decode($json,TRUE);
    dd($xml);
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

Route::group(['middleware' => ['assign.guard:drivers', 'jwt.auth'], 'prefix' => 'conductor'], function () {
    Route::get('/ofertas', 'DriverController@listarOfertas');
    Route::post('/actualizarEstado', 'DriverController@actualizarEstado');
});

Route::group(['middleware' => ['assign.guard:drivers', 'jwt.auth'], 'prefix' => 'novedad'], function () {
    Route::post('/insertar', 'ShippingController@novedad_insertar');
});

Route::group(['middleware' => ['assign.guard:drivers', 'jwt.auth'], 'prefix' => 'envio'], function () {
    Route::get('/aceptar/{idofertaenvio}', 'ShippingController@aceptar');
    Route::get('/rechazar/{idofertaenvio}', 'ShippingController@rechazar');
    Route::get('/rutas/{idofertaenvio}', 'ShippingController@listarRutas');
    Route::get('/iniciar/{idofertaenvio}', 'ShippingController@iniciar')->where('idofertaenvio', '[0-9]+');
    Route::get('/finalizar/{id_shipping_order}', 'ShippingController@finalizar')->where('id_shipping_order', '[0-9]+');
    // Route::get('/coordenadas/{idofertaenvio}', 'EnviosController@coordenadas')->where('idofertaenvio', '[0-9]+');
});

Route::group(['middleware' => ['assign.guard:drivers', 'jwt.auth'], 'prefix' => 'pedido'], function () {
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

    Route::group(['middleware' => ['assign.guard:users', 'jwt.auth'], 'prefix' => 'main'], function () {
        Route::post('', 'MainController@index');
        Route::post('/simpleTransaction', 'MainController@simpleTransaction');
        Route::post('paginated', 'MainController@paginated');
    });

    Route::group(['middleware' => ['assign.guard:users', 'jwt.auth'], 'prefix' => 'massive_load'], function () {
        Route::post('', 'MassiveLoadController@index');
        Route::post('process', 'MassiveLoadController@process');
        Route::post('print/cargo', 'MassiveLoadController@print_cargo');
        Route::get('print/cargo/{id_guide}', 'MassiveLoadController@print_cargo_guide');
        Route::post('print/marathon', 'MassiveLoadController@print_marathon');
        Route::post('unitaria', 'MassiveLoadController@unitaria');
    });

    Route::group(['middleware' => ['assign.guard:users', 'jwt.auth'], 'prefix' => 'collect'], function () {
        Route::post('load', 'CollectController@load');
        Route::post('process', 'CollectController@process');
        // Route::post('print/cargo', 'MassiveLoadController@print_cargo');
        // Route::post('print/marathon', 'MassiveLoadController@print_marathon');
    });

    Route::group(['middleware' => ['api'], 'prefix' => 'complete'], function () {
        Route::post('load', [CompleteLoadController::class, 'load']);
        Route::post('load/process', [CompleteLoadController::class, 'process']);
        Route::post('load/process_distribution', [CompleteLoadController::class, 'process_distribution']);
    });

    Route::group(['middleware' => ['assign.guard:users', 'jwt.auth'], 'prefix' => 'shipping'], function () {
        Route::post('print/hoja_ruta', 'ShippingController@print_hoja_ruta');
        Route::post('/imagen', 'ShippingController@grabarImagen');
        // Route::post('process', 'ShippingController@process');
    });

    Route::group(['middleware' => ['assign.guard:users', 'jwt.auth'], 'prefix' => 'reportes'], function () {
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

    Route::group(['middleware' => ['assign.guard:integration_users', 'jwt.auth']], function () {
        Route::post('registrar', 'IntegrationController@registrar');
        Route::get('consultar/{seg_code}', 'IntegrationController@consultar');
        Route::get('consultar/cargo/{seg_code}', 'IntegrationController@exportar_cargo');
    });

    Route::group(['middleware' => ['api'], 'prefix' => 'carga'], function () {
        Route::post('procesar', [IntegrationController::class, 'index']);
    });
});
