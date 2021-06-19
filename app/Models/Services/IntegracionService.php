<?php

namespace App\Models\Services;

use App\Exceptions\CustomException;
use App\Helpers\ResponseHelper as Res;
use App\Models\Repositories\ConductorRepository;
use App\Models\Repositories\EnvioRepository;
use App\Models\Repositories\IntegracionRepository;
use App\Models\Repositories\OfertasEnvioRepository;
use App\Models\Repositories\PedidoDetalleRepository;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IntegracionService
{
    protected $repository;

    public function __construct(IntegracionRepository $integracionRepository)
    {
        $this->repository = $integracionRepository;
    }

    public function integracionRipley()
    {
        $res['success'] = false;
        try {
            $guides = $this->repository->getGuides(1);
            Log::info('Proceso de integracion con ripley', ['nro_registros' => count($guides)]);
            foreach ($guides as $key => $guide) {
                if ($guide->Estado === 'CURSO') {
                    $guide->Estado = 'En Transito';
                    $guide->SubEstado = 'En Ruta hacia el Cliente';
                }
                $req_body = [
                    "CUD" => $guide->CUD,
                    "Estado" => ucwords(strtolower($guide->Estado)),
                    "SubEstado" => utf8_decode(utf8_decode($guide->SubEstado)),
                    "Placa" => $guide->Placa,
                    "Courier" => $guide->Courier,
                    "Fecha" => $guide->Fecha,
                    "NombreReceptor" => $guide->NombreReceptor,
                    "IDReceptor" => $guide->IDReceptor,
                    "TrackNumber" => $guide->TrackNumber,
                    "URL" => env('WEB_APP_URL') . 'guidestatus/' . $guide->id_guide
                ];

                $cliente = new Client(['base_uri' => env('RIPLEY_INTEGRACION_API_URL')]);

                try {
                    $req = $cliente->request('POST', 'sendStateCourierOnline', [
                        "headers" => [
                            'x-api-key' => env('RIPLEY_INTEGRACION_API_KEY'),
                        ],
                        "json" => $req_body
                    ]);
                } catch (\GuzzleHttp\Exception\RequestException $e) {
                    $response = (array) json_decode($e->getResponse()->getBody()->getContents());
                    Log::error('Reportar estado a ripley, ', ['req' => $req_body, 'exception' => $response]);
                    $buscar = strpos(strtoupper($response['message']), strtoupper("Already exists a record with CUD:'" . $guide->CUD . "', Estado:'" . $guide->Estado . "' and SubEstado:'" . utf8_decode(utf8_decode($guide->SubEstado)) . "'"));
                    if ($buscar === false) {
                        $this->repository->LogInsert($guide->CUD, $guide->id_guide, $guide->Estado, $guide->SubEstado, 'ERROR', $req_body, $response);
                        $this->repository->updateReportado($guide->id_guide, 2);
                    } else {
                        $this->repository->LogInsert($guide->CUD, $guide->id_guide, $guide->Estado, $guide->SubEstado, 'SUCCESS', $req_body, $response);
                        $this->repository->updateReportado($guide->id_guide, 1);
                    }
                    continue;
                }

                $response = json_decode($req->getBody()->getContents());
                $this->repository->LogInsert($guide->CUD, $guide->id_guide, $guide->Estado, $guide->SubEstado, 'SUCCESS', $req_body, $response);
                $this->repository->updateReportado($guide->id_guide, 1);
            }

            $res['success'] = true;
            Log::info('Proceso de integracion con ripley exitoso', ['nro_registros' => count($guides)]);
        } catch (Exception $e) {
            Log::error('Integracion ripley', ['cliente' => 'Ripley', 'exception' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();
        }
        return $res;
    }

    public function integracionOechsle()
    {
        $res['success'] = false;
        try {
            $guides = $this->repository->getGuideOeschle();
            Log::info('Proceso de integracion con Oeschle', ['nro_registros' => count($guides)]);

            foreach ($guides as $key => $guide) {
                $g = '';
                $items = [];
                $g .= $guide->ids_guias . ',';
                $productos = explode("|||", $guide->contenido);
                foreach ($productos as $producto) {
                    $detalle = explode("///", $producto);
                    array_push($items, [
                        'skuCode' => $detalle[0],
                        'deliveredQuantity' => $detalle[1]
                    ]);
                }

                $req_body = [
                    "companyCode" => "OE",
                    "dispatchNumber" => $guide->alt_code1,
                    "items" => $items
                ];

                $guias = rtrim($g, ',');

                if (env('OESCHLE_INTEGRACION_API_SEND')) {
                    $cliente = new Client(['base_uri' => env('OESCHLE_INTEGRACION_API_URL')]);

                    try {
                        $req = $cliente->request('POST', 'provider/delivery', [
                            "headers" => [
                                'client_id' => env('OESCHLE_INTEGRACION_API_KEY'),
                            ],
                            "json" => $req_body
                        ]);
                    } catch (\GuzzleHttp\Exception\RequestException $e) {
                        $response = (array) json_decode($e->getResponse()->getBody()->getContents());
                        Log::error('Reportar estado a Oechsle, ', ['req' => $req_body, 'exception' => $response]);
                        $this->repository->LogInsertOechsle('ERROR', $req_body, $response, $guias, $guide->alt_code1);
                        $this->repository->updateReportadoOeschle($guias, 2);
                        continue;
                    }

                    $response = json_decode($req->getBody()->getContents());
                    $this->repository->updateReportadoOeschle($guias, 1);
                } else {
                    $response = $guias;
                }
                $this->repository->LogInsertOechsle('SUCCESS', $req_body, $response, $guias, $guide->alt_code1);
            }
            $res['success'] = true;
            Log::info('Proceso de integracion con Oechsle exitoso', ['nro_registros' => count($guides)]);
        } catch (Exception $e) {
            Log::error('Integracion Oechsle', ['cliente' => 'Oechsle', 'exception' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();
        }
        return $res;
    }

    public function integracionInretail()
    {
        $res['success'] = false;
        try {
            $guides = $this->repository->getGuidesInRetail();
            Log::info('Proceso de integracion con inRetail', ['nro_registros' => count($guides)]);
            foreach ($guides as $key => $guide) {
                $evidences = [];
                $fotos = explode(",", $guide->imagenes);
                foreach ($fotos as $foto) {
                    array_push($evidences, [
                        'evidence_url' => $foto
                    ]);
                }

                switch ($guide->status) {
                    case 'CURSO':
                        $guide->status = 'EN RUTA';
                        $guide->SubEstado = '';
                        break;
                    case 'RECOLECCION COMPLETA':
                        $guide->status = 'RECOLECTADO';
                        $guide->SubEstado = '';
                        break;
                    default:
                        break;
                }
                // if ($guide->status === 'CURSO') {
                //     $guide->status = 'EN RUTA';
                //     $guide->SubEstado = '';
                // }

                $req_body = [
                    "Account" => $guide->alt_code1,
                    "OrderNumber" => $guide->seg_code,
                    "SellerName" => $guide->sellerName,
                    "GuideNumber" => $guide->guide_number,
                    "TrackingUrl" => env('WEB_APP_URL') . 'guidestatus/' . $guide->id_guide,
                    "Status" => $guide->status,
                    "StatusDescription" => $guide->motive,
                    "Evidences" => $evidences
                ];

                if (env('INRETAIL.FAKE')) {
                    $response = json_decode('{
                        "Account": "1",
                        "OrderNumber": "1234567-1",
                        "SellerName": "PRUEBA S.A.C.",
                        "GuideNumber": "WXSS2333",
                        "TrackingUrl": "abc.com/seguimiento",
                        "Status": "ENTREGADA",
                        "StatusDescription": "Entregada a familiar directo",
                        "Evidences": [
                                {
                                "evidence_url": "abc.com/evidencia1.jpg"
                                }
                            ]
                        }');
                } else {
                    $cliente = new Client(['base_uri' => env('INRETAIL.URL')]);
                    try {
                        $req = $cliente->request('POST', 'guide/state', [
                            "headers" => [
                                'client_id' => env('INRETAIL_API_CLIENT_ID'),
                            ],
                            "json" => $req_body
                        ]);
                    } catch (\GuzzleHttp\Exception\RequestException $e) {
                        $response = (array) json_decode($e->getResponse()->getBody()->getContents());
                        Log::error('Reportar estado a InRetail, ', ['req' => $req_body, 'exception' => $response]);
                        $this->repository->logInsertInRetail($guide->seg_code, $guide->guide_number, $guide->id_guide, $guide->status, $guide->motive, 'ERROR', $req_body, $response);
                        $this->repository->updateReportado($guide->id_guide, 2);
                        continue;
                    }
                    $response = json_decode($req->getBody()->getContents());
                }

                $this->repository->logInsertInRetail($guide->seg_code, $guide->guide_number, $guide->id_guide, $guide->status, $guide->motive, 'SUCCESS', $req_body, $response);
                $this->repository->updateReportado($guide->id_guide, 1);
            }
            $res['success'] = true;
            Log::info('Proceso de integracion con inRetail exitoso', ['nro_registros' => count($guides)]);
        } catch (Exception $e) {
            Log::error('Integracion inRetail', ['cliente' => 'inRetail', 'exception' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();
        }
        return $res;
    }

    public function integracionCoolbox()
    {
        $res['success'] = false;
        try {
            $guides = $this->repository->getGuidesCoolbox();
            Log::info('Proceso de integracion con coolbox', ['nro_registros' => count($guides)]);

            if (!count($guides)) {
                Log::info('Nada que reportar Coolbox');
                $res['success'] = true;
                return $res;
            }
            
            if (!env('COOLBOX.FAKE')) {
                $accessToken = $this->prepare_access_token();
            } else { $accessToken = 'token prueba'; }

            Log::info('token ', ['token' => $accessToken]);

            foreach ($guides as $key => $guide) {
                $evidences = [];
                $fotos = explode(",", $guide->imagenes);
                // foreach ($fotos as $foto) {
                //     array_push($evidences, [
                //         'evidence_url' => $foto
                //     ]);
                // }

                switch ($guide->status) {
                    case 'CURSO':
                        $estado = 7;
                        break;
                    case 'ENTREGADO':
                        $estado = 8;
                        break;
                    case 'NO ENTREGADO':
                        $estado = 18;
                        break;
                    default:
                        break;
                }
                // if ($guide->status === 'CURSO') {
                //     $guide->status = 'EN RUTA';
                //     $guide->SubEstado = '';
                // }

                $req_body = [
                    "pedido" => 0,
                    "numPedido" => $guide->seg_code,
                    "estado" =>  $estado,
                    "ubicacion" => 0,
                    "guia" => 0,
                    "archivo" => ($estado == 8) ? $fotos[0] : ""
                ];

                if (env('COOLBOX.FAKE')) {
                    $response = json_decode('{
                        "actualizado": true,
                        "mensaje": "Estado actualizado de forma correcta."
                        }');
                } else {
                    $cliente = new Client(['base_uri' => env('COOLBOX.URL')]);
                    try {
                        $req = $cliente->request('POST', 'ActualizarEstadoPedidoporNumPedido', [
                            "headers" => [
                                'Authorization' => 'Bearer ' . $accessToken['token'],
                            ],
                            "json" => $req_body
                        ]);
                    } catch (\GuzzleHttp\Exception\RequestException $e) {
                        $response = (array) json_decode($e->getResponse()->getBody()->getContents());
                        Log::error('Reportar estado a Coolbox, ', ['req' => $req_body, 'exception' => $response]);
                        $this->repository->logInsertCoolbox($guide->seg_code, $guide->guide_number, $guide->id_guide, $guide->status, $guide->motive, 'ERROR', $req_body, $response);
                        $this->repository->updateReportado($guide->id_guide, 2);
                        continue;
                    }
                    $response = json_decode($req->getBody()->getContents());
                    if(!$response->actualizado){
                        $this->repository->logInsertCoolbox($guide->seg_code, $guide->guide_number, $guide->id_guide, $guide->status, $guide->motive, 'ERROR', $req_body, $response);
                        $this->repository->updateReportado($guide->id_guide, 2);
                        continue;
                    }
                }

                $this->repository->logInsertCoolbox($guide->seg_code, $guide->guide_number, $guide->id_guide, $guide->status, $guide->motive, 'SUCCESS', $req_body, $response);
                $this->repository->updateReportado($guide->id_guide, 1);
            }
            $res['success'] = true;
            Log::info('Proceso de integracion con Coolbox exitoso', ['nro_registros' => count($guides)]);
        } catch (Exception $e) {
            Log::error('Integracion Coolbox', ['cliente' => 'Coolbox', 'exception' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();
        }
        return $res;
    }

    public function prepare_access_token()
    {
        $http = new Client(['base_uri' => env('COOLBOX.OAUTH_API_URL')]);

        $response = $http->request('POST', 'login', array(
            'json' => [
                'Username' => env('COOLBOX.USUARIO'),
                'Password' => env('COOLBOX.PASSWORD')
            ]
        ));

        $responseBody = $response->getBody(true);
        $responseArr = json_decode($responseBody, true);
        return $responseArr;
    }
}
