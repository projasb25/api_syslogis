<?php

namespace App\Models\Services;

use App\Exceptions\CustomException;
use App\Helpers\ResponseHelper as Res;
use App\Models\Repositories\ConductorRepository;
use App\Models\Repositories\EnvioRepository;
use App\Models\Repositories\IntegracionRepository;
use App\Models\Repositories\OfertasEnvioRepository;
use App\Models\Repositories\PedidoDetalleRepository;
use Carbon\Carbon;
use Error;
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
                if ($guide->status === 'CURSO' && $guide->type === 'RECOLECCION' && $guide->delivery_type === 'Logistica inversa') { 
                    $this->repository->updateReportado($guide->id_guide, 1);
                    continue; // Jumps to next iteration
                }

                $evidences = [];
                $fotos = explode(",", $guide->imagenes);
                foreach ($fotos as $foto) {
                    array_push($evidences, [
                        'evidence_url' => $foto
                    ]);
                }

                switch ($guide->status) {
                    case 'PENDIENTE': 
                        $guide->estado = 'EMITIDO';
                        break;
                    case 'CURSO':
                        $guide->estado = 'EN RUTA';
                        break;
                    case 'RECOLECCION COMPLETA':
                        $guide->estado = 'RECOLECTADO';
                        break;
                    default:
                        $guide->estado = $guide->status;
                        break;
                }

                if ($guide->delivery_type === 'Logistica inversa') {
                    switch ($guide->status) {
                        case 'RECOLECCION COMPLETA': 
                            $guide->estado = 'RECOLECTADO CLIENTE';
                            break;
                        case 'NO RECOLECTADO':
                            $guide->estado = 'NO RECOLECTADO CLIENTE';
                            break;
                        case 'CURSO':
                            $guide->estado = 'EN DEVOLUCION';
                            break;
                        case 'ENTREGADO':
                            $guide->estado = 'DEVUELTA';
                            break;
                        case 'NO ENTREGADO':
                            $guide->estado = 'NO DEVUELTA';
                            break;
                        default:
                            break;
                    }
                }

                if ($guide->status === 'CURSO' && $guide->type === 'RECOLECCION') {
                    $guide->estado = 'EN RUTA RECOLECCION';
                }

                $req_body = [
                    "Account" => $guide->alt_code1,
                    "Evidences" => $evidences,
                    "GuideNumber" => $guide->guide_number,
                    "OrderNumber" => $guide->seg_code,
                    "SellerName" => $guide->sellerName,
                    "Status" => $guide->estado,
                    "StatusDescription" => $guide->motive,
                    "TrackingUrl" => env('WEB_APP_URL') . 'guidestatus/' . $guide->id_guide,
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
                        $this->repository->logInsertInRetail($guide->seg_code, $guide->guide_number, $guide->id_guide, $guide->estado, $guide->motive, 'ERROR', $req_body, $response);
                        $this->repository->updateReportado($guide->id_guide, 2);
                        continue;
                    }
                    $response = json_decode($req->getBody()->getContents());
                }

                $this->repository->logInsertInRetail($guide->seg_code, $guide->guide_number, $guide->id_guide, $guide->estado, $guide->motive, 'SUCCESS', $req_body, $response);
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
                    "ubicacion" => "",
                    "guia" => "",
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

    public function integracionOechsleInter()
    {
        $res['success'] = false;
        try {
            $guides = $this->repository->getGuideOeschleInter();
            Log::info('Proceso de integracion con Oeschle puntos medios', ['nro_registros' => count($guides)]);

            if (count($guides) === 0) {
                $res['success'] = true;
                return $res;
            }

            foreach ($guides as $key => $guide) {
                $delivery_mode = ($guide->id_organization === 141) ? 'STORE_WITHDRAWAL' : 'HOME_DELIVERY';
                $g = '';
                $items = [];
                $g .= $guide->ids_guias . ',';
                Log::info('guias ',['ids_guias' => $guide->ids_guias, 'g' => $g]);
                $productos = explode("|", $guide->contenido);
                foreach ($productos as $key => $producto) {
                    $detalle = explode("/", $producto);
                    array_push($items, [
                        'dispatchNumber' => $guide->alt_code1,
                        'skuCode' => explode('-', $detalle[0])[0],
                        'quantity' => (int) $detalle[1]
                    ]);

                    if ($guide->id_organization === 141) {
                        $items[$key]['entityCode'] = $guide->alt_code2; 
                    }

                    if ($guide->status === 'NO ENTREGADO') {
                        $items[$key]['reason'] = explode(",", $guide->motive)[0];
                    }
                }

                $req_body = [
                    "companyCode" => "OE",
                    "deliveryMode" => $delivery_mode,
                    // "stateDate" => explode(",", $guide->stateDate)[0],
                    "stateDate" => date("Y-m-d H:i:s"),
                    "userName" => 'QAYARIX_APP',
                    "items" => $items
                ];

                $guias = rtrim($g, ',');

                switch ($guide->status) {
                    case 'NO ENTREGADO':
                        $type = 'ORDER_NOT_DELIVERED';
                        break;
                    case 'ENTREGADO':
                        $type = ($guide->id_organization === 141) ? 'ORDER_RECEIVED' : 'ORDER_DELIVERED';
                        break;
                    default:
                        $type = 'ORDER_IN_TRIP_DISPATCHED';
                        break;
                }
                
                $headers = [
                    "Content-Type" => "application/json",
                    'client_id' => env('OESCHLE_INTEGRACION_API_KEY_INTER'),
                    'X-DadCenter-Event' => $type,
                    'X-Origin-System' => 'EXT'
                ];

                Log::info('header', ['header' => $headers]);
                
                // $type = ($guide->status === 'NO ENTREGADO') ? 'ORDER_NOT_DELIVERED' : 'ORDER_IN_TRIP_DISPATCHED';
                if (env('OESCHLE_INTEGRACION_API_SEND')) {
                    $cliente = new Client(['base_uri' => env('OESCHLE_INTEGRACION_API_URL_INTER')]);

                    try {
                        $req = $cliente->request('POST', 'dispatch/event', [
                            "headers" => [
                                "Content-Type" => "application/json",
                                'client_id' => env('OESCHLE_INTEGRACION_API_KEY_INTER'),
                                'X-DadCenter-Event' => $type,
                                'X-Origin-System' => 'EXT'
                            ],
                            "json" => $req_body
                        ]);
                    } catch (\GuzzleHttp\Exception\RequestException $e) {
                        $response = $e->getResponse()->getBody()->getContents();
                        Log::error('Reportar estado a Oechsle, ', ['exception' => $response, 'req' => $req_body]);
                        $this->repository->LogInsertOechsle_inter('ERROR', $req_body, $response, $guias, $guide->alt_code1, $guide->status, $type);
                        $this->repository->updateReportadoOeschle($guias, 2);
                        continue;
                    }

                    $response = json_decode($req->getBody()->getContents());
                    $this->repository->updateReportadoOeschle($guias, 1);
                } else {
                    $response = $guias;
                }
                $this->repository->LogInsertOechsle_inter('SUCCESS', $req_body, $response, $guias, $guide->alt_code1, $guide->status, $type);

                Log::info('registro ',['req_body' => $req_body]);
            }
            $res['success'] = true;
            Log::info('Proceso de integracion con Oechsle exitoso', ['nro_registros' => count($guides)]);
        } catch (Exception $e) {
            Log::error('Integracion Oechsle', ['cliente' => 'Oechsle', 'exception' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();
        }
        return $res;
    }

    public function integracionTailoy()
    {
        $res['success'] = false;
        try {
            $guides = $this->repository->getGuidesAllStatus(48);
            Log::info('Proceso de integracion con TaiLoy', ['nro_registros' => count($guides)]);
            if (count($guides) === 0) {
                $res['success'] = true;
                return $res;
            }

            foreach ($guides as $key => $guide) {
                $evidences = [];
                $coordenadas = ['lat' => null, 'lng' => null];
                if ($guide->imagenes) {
                    $fotos = explode(",", $guide->imagenes);
                    foreach ($fotos as $foto) {
                        array_push($evidences, [
                            'imagen' => $foto
                        ]);
                    }
                }

                $estados_tailoy = [];
                $motive_code = null;
                switch ($guide->status) {
                    case 'PENDIENTE':
                        $estados_tailoy = [1,2];
                        break;
                    case 'CURSO':
                        $estados_tailoy = [3];
                        break;
                    case 'ENTREGADO':
                        $estados_tailoy = [5,6,7];
                        $coordenadas['lat'] = $guide->latitude;
                        $coordenadas['lng'] = $guide->longitude;
                        break;
                    case 'NO ENTREGADO':
                        $estados_tailoy = [5,10];
                        $motive_code = ($guide->motive === 'Consignaron Datos Incorrectos') ? 514 : (($guide->motive === 'Documentacion Incorrecta') ? 8 : 2);
                        break;
                    default:
                        $estados_tailoy = [1,2];
                        break;
                }
                
                foreach ($estados_tailoy as $key => $estado) {
                    $req_body = [
                        'recurso' => 'TAREA',
                        'data' => [
                            [
                                'nroTarea' => $guide->guide_number,
                                'codigoRastreo' => $guide->seg_code,
                                'estado' => $estado,
                                'coordenadas' => $coordenadas,
                                'fechaHora' => Carbon::parse($guide->fecha_estado)->format('d/m/Y H:i:s'),
                                'evidencias' => $evidences,
                                'conductor' => $guide->driver_name,
                                'placa' => $guide->plate_number,
                                'fechaEstimadaEntregaInicio' => null,
                                'fechaEstimadaEntregaFin' => null,
                                'codigoMotivo' => $motive_code
                            ]
                        ]
                    ];
                    
                    if (env('TAILOY.FAKE')) {
                        $body = json_decode('{
                            "mensaje": "No se pudo realizar la actualización de los estados de las tareas solicitadas",
                            "resultado": "Ok",
                            "errores": [
                                {
                                    "codigoRastreo": "2001",
                                    "estado": "1",
                                    "fechaHora": "2021-08-06 10:18:04",
                                    "nroTarea": "2001"
                                }
                            ]
                        }');
                    } else {
                        try {
                            $cliente = new Client(['base_uri' => env('TAILOY.URL')]);
                            $request = $cliente->post('integracion/couriers', [
                                "headers" => [ 'X-AUTH-TOKEN' => env('TAILOY.TOKEN')],
                                "json" => $req_body
                            ]);
                            $body = json_decode($request->getBody());
                        } catch (RequestException $e) {
                            $response = json_decode((string) $e->getResponse()->getBody());
                            Log::error('Reportar estado a Tailoy, ', ['req' => json_encode($req_body, JSON_UNESCAPED_SLASHES), 'exception' => (array) $response]);
                            $this->repository->insertLogIntegration($guide->seg_code, $guide->id_corporation, $guide->id_organization, $guide->guide_number, $guide->id_guide, $guide->status, $guide->motive, 'ERROR', $req_body, $response);
                            $this->repository->updateReportado($guide->id_guide, 2);
                            continue;
                        }
                    }

                    if ($body->resultado !== 'Ok') {
                        $this->repository->insertLogIntegration($guide->seg_code, $guide->id_corporation, $guide->id_organization, $guide->guide_number, $guide->id_guide, $guide->status, $guide->motive, 'ERROR', $req_body, $body);
                        $this->repository->updateReportado($guide->id_guide, 2);
                        continue;
                    }

                    $this->repository->insertLogIntegration($guide->seg_code, $guide->id_corporation, $guide->id_organization, $guide->guide_number, $guide->id_guide, $guide->status, $guide->motive, 'SUCCESS', $req_body, $body);
                    $this->repository->updateReportado($guide->id_guide, 1);
                }
            }
            $res['success'] = true;
            Log::info('Proceso de integracion con Tailoy exitoso', ['nro_registros' => count($guides)]);
        } catch (Exception $e) {
            Log::error('Integracion Tailoy', ['cliente' => 'Tailoy', 'exception' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();
        }
        return $res;
    }

    public function falabella()
    {
        $res['success'] = false;
        try {
            $res['success'] = true;
        } catch (Exception $e) {
            Log::error('Integracion Falabella', ['cliente' => 'Falabella', 'exception' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();
        }
        return $res;
    }
}
