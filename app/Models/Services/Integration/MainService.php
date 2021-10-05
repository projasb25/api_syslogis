<?php

namespace App\Models\Services\Integration;

use App\Exceptions\CustomException;
use App\Helpers\ResponseHelper as Res;
use App\Models\Repositories\Integration\MainRepository;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use function PHPSTORM_META\map;

class MainService
{
    private $repo;

    public function __construct(MainRepository $mainRepository)
    {
        $this->repo = $mainRepository;
    }

    public function index($request)
    {
        try {
            $request_data = $request->all();
            if (strtolower($request_data['selectedSla']) === strtolower('Envío express')) {
                $organizacion = 58;
            } else {
                $organizacion = 53;
            }
            $user = (object) [
                'id_integration_user' => 1,
                'id_corporation' => 15,
                'id_organization' => $organizacion,
                'integration_user' => 'inretail'
            ];

            // $request_data['selectedSla'] = "Envío a domicilio";
            $insertar = $this->repo->insertData($request_data, $user);
            // if (strtolower($request_data['selectedSla']) === strtolower('Delivery Express')) {
            //     $integration_data = $this->repo->getIntegrationDataExpress();
            //     $id = $this->repo->insertMassiveLoad($integration_data);
            //     Log::info('Integracion carga - Carga masiva generada, delivery express', ['id_carga' => $id]);
            // }
            // if (env('INRETAIL.FAKE')) {
            //     $response = json_decode('{
            //         "Account": "1",
            //         "OrderNumber": "12234-1",
            //         "SellerName": "QAYARIX",
            //         "GuideNumber": "WX334434",
            //         "TrackingUrl": "urlseguimiento.com/web/WX334434"
            //        }');
            // } else {
            //     $req_body = [
            //         "Account"=> $request_data['marketplaceId'],
            //         "GuideNumber"=> $insertar,
            //         "OrderNumber"=> $request_data['orderNumber'],
            //         "SellerName"=> $request_data['sellerCorporateName'],
            //         "TrackingUrl"=> ""
            //     ];

            //     $cliente = new Client(['base_uri' => env('INRETAIL.URL')]);

            //     $req = $cliente->request('POST', 'guide/create', [
            //         "headers" => [
            //             'client_id' => env('INRETAIL_API_CLIENT_ID'),
            //         ],
            //         "json" => $req_body
            //     ]);

            //     $response = json_decode($req->getBody()->getContents());
            // }

            Log::info('Integracion carga exito', ['id_carga' => $insertar, 'req' => $request->all()]);
        // } catch (\GuzzleHttp\Exception\RequestException $e) {
        //     Log::error('Integracion carga error', ['exception' => $e->getResponse()->getBody(true), 'req_body' => $req_body, 'req' => $request->all()]);
        //     return response()->json([
        //         'codigo' => '3000',
        //         "tipoError" => "Connection Error API",
        //         'mensaje'=> "Error en el proceso",
        //     ]);
        } catch (Exception $e) {
            Log::error('Integracion carga error', ['exception' => $e->getMessage(), 'req' => $request->all()]);
            return response()->json([
                'codigo' => '3000',
                "tipoError" => "Connection Error",
                'mensaje'=> "Error en el proceso",
            ]);
        }

        return response()->json([
            'codigo' => '1',
            "tipoError" => "",
            'mensaje'=> "Se creó la guía correctamente",
            "numeroDeGuia" => $insertar
        ]);
    }

    public function procesar()
    {
        $res['success'] = false;
        try {
            $integration_data = $this->repo->getIntegrationData();

            $id = $this->repo->insertMassiveLoad($integration_data);
            $res =[
                'id_massive_load' => $id
            ];

            $res['success'] = true;
            Log::info('Integracion Crear Carga exito', ['id_carga' => $id]);
        } catch (CustomException $e) {
            Log::warning('Integracion Crear Carga error', ['expcetion' => $e->getData()[0]]);
            $res['mensaje'] = $e->getData()[0];
        } catch (QueryException $e) {
            Log::warning('Integracion Crear Carga Query', ['expcetion' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();
        } catch (Exception $e) {
            Log::warning('Integracion Crear Carga error', ['exception' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();
        }
        return $res;
    }

    public function procesar_recoleccion_provincia()
    {
        $res['success'] = false;
        try {
            $integration_data = $this->repo->getIntegrationDataProvincia();

            $integration_data[0]->id_corporation = 15;
            $integration_data[0]->id_organization = 65;

            $id = $this->repo->insertMassiveLoad($integration_data);
            $res =[
                'id_massive_load' => $id
            ];

            $res['success'] = true;
            Log::info('Integracion Crear Carga Provincia exito', ['id_carga' => $id]);
        } catch (CustomException $e) {
            Log::warning('Integracion Crear Carga Provincia error', ['expcetion' => $e->getData()[0]]);
            $res['mensaje'] = $e->getData()[0];
        } catch (QueryException $e) {
            Log::warning('Integracion Crear Carga Provincia Query', ['expcetion' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();
        } catch (Exception $e) {
            Log::warning('Integracion Crear Carga Provincia error', ['exception' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();
        }
        return $res;
    }

    public function recoleccion_express()
    {
        $res['success'] = false;
        try {
            $integration_data = $this->repo->getIntegrationDataExpress();

            $id = $this->repo->insertMassiveLoad($integration_data);
            $res =[
                'id_massive_load' => $id
            ];

            $res['success'] = true;
            Log::info('Integracion Crear Carga Express exito', ['id_carga' => $id]);
        } catch (CustomException $e) {
            Log::warning('Integracion Crear Carga Express error', ['expcetion' => $e->getData()[0]]);
            $res['mensaje'] = $e->getData()[0];
        } catch (QueryException $e) {
            Log::warning('Integracion Crear Carga Express Query', ['expcetion' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();
        } catch (Exception $e) {
            Log::warning('Integracion Crear Carga Express error', ['exception' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();
        }
        return $res;
    }

    public function procesar_distribucion()
    {
        $res['success'] = false;
        try {
            $integration_data = $this->repo->getGuidesCollected();

            $id = $this->repo->insertMassiveLoadDist($integration_data);


            $res =[
                'id_massive_load' => $id
            ];
            $res['success'] = true;
            Log::info('Integracion procesar distribucion exito', ['id_carga' => $id]);
        } catch (CustomException $e) {
            Log::warning('Integracion procesar distribucion error', ['expcetion' => $e->getData()[0]]);
            $res['mensaje'] = $e->getData()[0];
        } catch (QueryException $e) {
            Log::warning('Integracion procesar distribucion Query', ['expcetion' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();
        } catch (Exception $e) {
            Log::warning('Integracion procesar distribucion error', ['exception' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();
        }
        return $res;
    }

    public function procesar_distribucion_express()
    {
        $res['success'] = false;
        try {
            $integration_data = $this->repo->getGuidesCollectedExpress();

            $id = $this->repo->insertMassiveLoadDist($integration_data);

            $res =[
                'id_massive_load' => $id
            ];
            $res['success'] = true;
            Log::info('Integracion procesar distribucion express exito', ['id_carga' => $id]);
        } catch (CustomException $e) {
            Log::warning('Integracion procesar distribucion express error', ['expcetion' => $e->getData()[0]]);
            $res['mensaje'] = $e->getData()[0];
        } catch (QueryException $e) {
            Log::warning('Integracion procesar distribucion express Query', ['expcetion' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();
        } catch (Exception $e) {
            Log::warning('Integracion procesar distribucion express error', ['exception' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();
        }
        return $res;
    }

    public function procesar_distribucion_provincia()
    {
        $res['success'] = false;
        try {
            $integration_data = $this->repo->getGuidesCollectedProvince();

            $integration_data[0]->id_corporation = 15;
            $integration_data[0]->id_organization = 65;
            $id = $this->repo->insertMassiveLoadDist($integration_data);


            $res =[
                'id_massive_load' => $id
            ];
            $res['success'] = true;
            Log::info('Integracion procesar distribucion exito', ['id_carga' => $id]);
        } catch (CustomException $e) {
            Log::warning('Integracion procesar distribucion error', ['expcetion' => $e->getData()[0]]);
            $res['mensaje'] = $e->getData()[0];
        } catch (QueryException $e) {
            Log::warning('Integracion procesar distribucion Query', ['expcetion' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();
        } catch (Exception $e) {
            Log::warning('Integracion procesar distribucion error', ['exception' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();
        }
        return $res;
    }

    public function reportar_carga()
    {
        $res['success'] = false;
        try {
            $integration_data = $this->repo->getDataToReport();
            if (!count($integration_data)) {
                $res['success'] = true;
                Log::info('Proceso reportar carga a inRetail - Nada que reportar');
                return $res;
            }

            foreach ($integration_data as $key => $load) {
                $req_body = [
                    "Account"=> $load->alt_code1,
                    "GuideNumber"=> $load->guide_number,
                    "OrderNumber"=> $load->seg_code,
                    "SellerName"=> $load->seller_name,
                    "TrackingUrl"=> ""
                ];

                if (env('INRETAIL.FAKE')) {
                    $response = json_decode('{
                        "Account": "1",
                        "OrderNumber": "12234-1",
                        "SellerName": "QAYARIX",
                        "GuideNumber": "WX334434",
                        "TrackingUrl": "urlseguimiento.com/web/WX334434"
                       }');
                } else {
                    $cliente = new Client(['base_uri' => env('INRETAIL.URL')]);
                    try {
                        $req = $cliente->request('POST', 'guide/create', [
                            "headers" => [
                                'client_id' => env('INRETAIL_API_CLIENT_ID'),
                            ],
                            "json" => $req_body
                        ]);
                    } catch (\GuzzleHttp\Exception\RequestException $e) {
                        $response = (array) json_decode($e->getResponse()->getBody()->getContents());
                        Log::error('Reportar carga a InRetail error, ', ['req' => $req_body, 'exception' => $response]);
                        $this->repo->updateReportado($load->id_integration_data, 0);
                        continue;
                    }
                    $response = json_decode($req->getBody()->getContents());
                    Log::info('Reportado con exito ',['req' => $req_body, 'resp' => $response]);
                }
                $this->repo->updateReportado($load->id_integration_data, 1);
            }
            $res['success'] = true;
            Log::info('Proceso reportar carga a inRetail', ['nro_registros' => count($integration_data)]);
        } catch (CustomException $e) {
            Log::warning('Reportar carga a inRetail error', ['expcetion' => $e->getData()[0]]);
            $res['mensaje'] = $e->getData()[0];
        } catch (QueryException $e) {
            Log::warning('Reportar carga a inRetail Query', ['expcetion' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();
        } catch (Exception $e) {
            Log::warning('Reportar carga a inRetail error', ['exception' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();
        }
        return $res;
    }

    public function registrar($request)
    {
        try {
            $campos = $request->all();
            $user = auth()->user();

            $insertar = $this->repo->insertIntegrationData($campos, $user);

            Log::info('Integracion carga exito', ['id_carga' => $insertar, 'req' => $request->all()]);
        } catch (CustomException $e) {
            Log::warning('Integracion registrar error', ['expcetion' => $e->getData()[0], 'request' => $campos]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Integracion registrar Query', ['expcetion' => $e->getMessage(), 'request' => $campos]);
            return Res::error(['Unxpected error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Integracion registrar error', ['exception' => $e->getMessage(), 'request' => $campos]);
            return Res::error(['Unxpected error', 3000], 500);
        }
        return Res::success([
            'mensaje' => 'Pedido registrado correctamente',
            'codigo_original' => $campos['segCode'],
            'codigo_segumiento' => $insertar
        ]);
    }

    public function consultar($request)
    {
        try {
            $user = auth()->user();
            $guide = $this->repo->getGuideFromIntegration($request->seg_code, $user);
            $res = [];
            $items = [];

            if (!count($guide)) {
                $integration_data = $this->repo->getLoadDataByGuide($request->seg_code, $user);
                if (!count($integration_data)) {
                    throw new CustomException(["Codigo de segumiento no encontrado", 400]);
                }
                $data = $integration_data;
                $status  = 'REGISTRADO';
            } else {
                $data = $guide;
                $status = $guide[0]->status;
            }

            foreach ($data as $key => $value) {
                array_push($items, [
                    'id' => $value->sku_code,
                    'description' => $value->sku_description,
                    'quantity' => $value->sku_pieces
                ]);
            }

            $res = [
                'codigo_original' => $data[0]->seg_code,
                'codigo_segumiento' => $data[0]->guide_number,
                'estado' => $status,
                'items' => $items
            ];
        } catch (CustomException $e) {
            Log::warning('Integracion registrar error', ['expcetion' => $e->getData()[0], 'request' => $request->seg_code]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Integracion registrar Query', ['expcetion' => $e->getMessage(), 'request' => $request->seg_code]);
            return Res::error(['Unxpected error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Integracion registrar error', ['exception' => $e->getMessage(), 'request' => $request->seg_code]);
            return Res::error(['Unxpected error', 3000], 500);
        }
        return Res::success([
            $res
        ]);
    }
}
