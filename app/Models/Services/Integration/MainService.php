<?php

namespace App\Models\Services\Integration;

use App\Exceptions\CustomException;
use App\Helpers\ResponseHelper as Res;
use App\Models\Repositories\Integration\MainRepository;
use App\Models\Services\Web\CustomPDF;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
            $getTypes = $this->repo->InRetail_getDistinctTypes();
            if (count($getTypes)) {
                foreach ($getTypes as $key => $item) {
                    $integration_data = $this->repo->InRetail_getCollectData($item->type);
                    $id = $this->repo->insertMassiveLoad($integration_data, $item->type);
                    
                    Log::info('Integracion Crear Carga exito', ['id_carga' => $id, 'type' => $item->type]);
                }
            }
            $res =['message' => 'Ok'];
            $res['success'] = true;
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

    public function procesar_recoleccion()
    {
        $res['success'] = false;
        try {
            $integration_data = $this->repo->getLoadIntegration();

            $id = $this->repo->insertMassiveLoadIntegration($integration_data);
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

            $id = $this->repo->insertMassiveLoad($integration_data, 'Provincia');
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

            $id = $this->repo->insertMassiveLoad($integration_data, 'Express');
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
            $collectedTypes = $this->repo->InRetail_getCollectedGuidesTypes();
            if (count($collectedTypes)) {
                foreach ($collectedTypes as $key => $type) {
                    $integration_data = $this->repo->InRetail_getGuidesCollectedByType($type); 
                    $id = $this->repo->insertMassiveLoadDist($integration_data, $type);
                }
            }
            // $integration_data = $this->repo->getGuidesCollected();
            $res =[ 'id_massive_load' => $id ];
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

    public function procesar_distribucion_integracion()
    {
        $res['success'] = false;
        try {
            $integration_data = $this->repo->getGuidesCollectedIntegration();

            $id = $this->repo->insertarCargaDistribucion($integration_data);

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

            $id = $this->repo->insertMassiveLoadDist($integration_data, 'Express');

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
            $id = $this->repo->insertMassiveLoadDist($integration_data, 'Provincia');


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
            $res = [];
            $items = [];
            $track_info = [];
            $guide = $this->repo->getGuideFromIntegration($request->seg_code, $user);
            $integration_data = $this->repo->getLoadDataByGuide($request->seg_code, $user);
            if (!$guide) {
                if (!count($integration_data)) {
                    throw new CustomException(["Codigo de segumiento no encontrado", 404]);
                }
                $data = $integration_data;
                $seg_code = $integration_data[0]->seg_code;
                $guide_number = $integration_data[0]->guide_number;
                $status  = 'REGISTRADO para recoleccion';
                $servicio = 'RECOLECCION';
                $track_guide = [];
            } else {
                $data = $this->repo->getProductInfo($guide->id_guide);
                $status = (in_array($guide->status, ['RECOLECCION COMPLETA', 'NO RECOLECTADO', 'ENTREGADO', 'NO ENTREGADO'])) ? $guide->status : $guide->status  . ' para ' . $guide->type;
                $seg_code = $guide->seg_code;
                $guide_number = $guide->guide_number;
                $track_guide = $this->repo->getTrackingInfo($guide->id_guide);
                $servicio = $guide->type;
            }
            $type_temp = (!$guide) ? 'recoleccion' : $guide->type;
            $track_info = [
                ['estado' => 'REGISTRADO para ' .  $type_temp, 'subEstado' => 'Registro Automático.', 'fecha' => $integration_data[0]->date_created]
            ];

            if (count($track_guide)) {
                foreach ($track_guide as $item) {
                    if (in_array($item->status, ['NO RECOLECTADO', 'NO ENTREGADO'])) {
                        $estado = $item->status . ' ' . $item->motive;
                    }
                    elseif (in_array($item->status, ['RECOLECCION COMPLETA', 'ENTREGADO'])) {
                        $estado = $item->status;
                    }
                    else {
                        $estado = $item->status  . ' para ' . $guide->type;
                    }
                    array_push($track_info, [
                        'estado' => $estado,
                        'subEstado' => $item->motive,
                        'fecha' => $item->date_created
                    ]);
                }
            }


            foreach ($data as $key => $value) {
                array_push($items, [
                    'id' => $value->sku_code,
                    'description' => $value->sku_description,
                    'quantity' => $value->sku_pieces
                ]);
            }

            $res = [
                'codigo_original' => $seg_code,
                'codigo_segumiento' => $guide_number,
                'estado' => $status,
                'servicio' => $servicio,
                'items' => $items,
                'track_info' => $track_info
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

    public function exportar_cargo($request)
    {
        try {
            $user = auth()->user();
            $guide_number = $request->seg_code;
            $disk = Storage::disk('cargo');
            $ruta = url('storage/cargo/');
    
            if (true) {
                $data = $this->repo->getDatosRutaCargoIntegracion($guide_number, $user->id_organization);
                if (!count($data)) {
                    throw new CustomException(["Codigo de segumiento no encontrado", 404]);
                }

                $doc = $this->generar_doc_cargo_tipo1($data);
            }
            
            $doc['file_name'];
            Log::info('[INTEGRACION] Reporte cargo generado exitosamente', ['file_name' => $doc['file_name'], 'guide_number' => $guide_number]);
        } catch (CustomException $e) {
            Log::warning('Integracion reporte error', ['expcetion' => $e->getData()[0], 'request' => $request->seg_code]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Integracion reporte Query', ['expcetion' => $e->getMessage(), 'request' => $request->seg_code]);
            return Res::error(['Unxpected error', 2020], 400);
        } catch (Exception $e) {
            Log::warning('Integracion reporte error', ['exception' => $e->getMessage(), 'request' => $request->seg_code]);
            return Res::error(['Unxpected error', 2021], 500);
        }

        return Res::success(['hoja_ruta' => $ruta .'/'. $doc['file_name']]);
    }

    public function generar_doc_cargo_tipo1($data)
    {
        try {
            $pdf = new CustomPDF();
            $cellMargin = 2 * 1.000125;
            $lmargin = 5;
            $rmargin = 5;
            $pdf->AliasNbPages();
            $pdf->AddPage();
            $pdf->SetMargins($lmargin, $rmargin);
            $pdf->Ln(0);
            $pdf->SetFont('Times', '', 7);
            $y = $pdf->GetY();
            $pdf->SetAutoPageBreak(false);

            $box_x = 5;
            $box_y = 5;

            foreach ($data as $i => $guide) {
                if ($i  % 3 == 0 && $i != 0) {
                    $pdf->AddPage();
                    $box_y = 5;
                }
                // cuadro principal
                $pdf->Rect($box_x, $box_y, 200, 78);

                // cuadro 1.1 REMITENTE
                    //header
                    $pdf->Rect($box_x + 0, $box_y + 0, 6, 37);
                    $pdf->SetFont('Times', 'B', 11);
                    $pdf->TextWithDirection($box_x + 5, $box_y + 29, 'REMITENTE', 'U');

                    // body
                    $pdf->Rect($box_x + 6, $box_y + 0, 85, 37);
                    $pdf->SetFont('Times', '', 11);
                    $pdf->SetXY($box_x+6, $box_y + 1);
                    $pdf->MultiCell(85,6,'NOMBRE: '. $guide->name,0,'J');
                    $pdf->SetX($box_x+6);
                    if ($guide->name === 'InRetail') {
                        $pdf->Cell(34,6,'CIUDAD: LIMA',0,0,'L');
                        $pdf->Cell(51,6,'COD.: '.$guide->alt_code1,0,1,'L');
                    } else {
                        $pdf->Cell(85,6,'CIUDAD: LIMA'.$guide->alt_code1,0,1,'L');
                    }
                    $pdf->SetX($box_x+6);
                    $pdf->MultiCell(85,6,'FECHA: '. $guide->date_loaded,0,'J');
                    $pdf->SetX($box_x+6);
                    $pdf->SetFont('Times', 'B', 11);
                    $pdf->MultiCell(85,6,utf8_decode('Nº de Guía: ' . $guide->guide_number),0,'J');
                    $pdf->SetFont('Times', '', 11);
                    $pdf->SetX($box_x+6);
                    $pdf->MultiCell(84,6,'DIRECCION: ' . utf8_decode(ucwords(strtolower($guide->org_address))),0,'L');

                // codigo de barra
                    if (isset($guide->client_barcode)) {
                        $cod_barra = $guide->client_barcode;
                    } else {
                        $cod_barra = $guide->guide_number;
                    }

                    $pdf->code128($box_x + 23, ($box_y + 38 + 2), $cod_barra , 50, 12, false);
                    $pdf->SetXY($box_x+1, ($box_y + 52 + 2));
                    $pdf->SetFont('Times', 'B', 16);
                    $pdf->MultiCell(96,4,$cod_barra, 0,'C');
                    $pdf->Ln(2);

                // cuadro 2.1 DATOS
                    //header
                    $pdf->Rect($box_x + 0, ($box_y + 59 + 2), 6, 17);
                    $pdf->SetFont('Times', 'B', 10);
                    $pdf->TextWithDirection($box_x + 5, $box_y + 76, 'DATOS', 'U');

                    // body
                    $pdf->Rect($box_x + 6, ($box_y + 59 + 2), 85, 17);
                    $pdf->SetFont('Times', 'B', 12);
                    $pdf->SetXY($box_x+8, ($box_y + 66 + 2));
                    $pdf->MultiCell(45,4,'NRO. PIEZAS: '. $guide->total_pieces,0,'J');
                    $pdf->SetXY($box_x+8+45, ($box_y + 66 + 2));
                    $pdf->MultiCell(45,4,'PESO: '. $guide->total_weight . ' KG',0,'J');
                    $pdf->Line($box_x+8+41, ($box_y + 59 + 2), $box_x+8+41, ($box_y + 76 + 2));

                    $pdf->SetX($box_x+8);
                // cuadro 1.2 DESTINATARIO
                    $tamano = ($guide->type === 'RECOLECCION') ? 53 : 41;

                    //header
                    $pdf->Rect($box_x + 93, $box_y + 0, 6, $tamano);
                    $pdf->SetFont('Times', 'B', 11);
                    $pdf->TextWithDirection($box_x + 93 + 4, $box_y + 35, 'DESTINATARIO', 'U');

                    // body
                    $nombre = utf8_decode(ucwords(strtolower($guide->client_name)));
                    $distrito = utf8_decode(ucwords(strtolower($guide->district)));
                    $direccion = utf8_decode(ucwords(strtolower($guide->address)));
                    $provincia = utf8_decode(ucwords(strtolower($guide->province)));
                    $departamento = utf8_decode(ucwords(strtolower($guide->department)));

                    $pdf->Rect($box_x + 93 + 6, $box_y + 0, 101, $tamano);
                    $pdf->SetFont('Times', '', 11);
                    $pdf->SetXY($box_x + 92 + 7, $box_y + 1);
                    $pdf->MultiCell(101,5,'NOMBRE: '. $nombre,0,'L');
                    $pdf->SetX($box_x + 92 + 7);
                    $pdf->MultiCell(101,5,'RUC: '. $guide->client_dni,0,'L');

                    $pdf->SetX($box_x + 92 + 7);
                    if ($guide->id_organization == 65) {
                        $pdf->Cell(50,5,'DIST.: ' . $distrito,0,0,'L');
                        $pdf->Cell(50,5,'PROV: '. $provincia,0,1,'L');
                        $pdf->SetX($box_x + 92 + 7);
                        $pdf->Cell(101,5,'DEP.: ' . $departamento,0,1,'L');
                    } else {
                        $pdf->MultiCell(101,5,'DIST.: ' . $distrito,0,'J');
                    }
                    // $pdf->SetX($box_x + 92 + 7);
                    // $pdf->MultiCell(101,5,'DIST.: ' . $distrito,0,'J');
                    if ($guide->type === 'RECOLECCION') {
                        $pdf->SetX($box_x + 92 + 7);
                        $pdf->MultiCell(101,5,'TLF.: ' . $guide->client_phone1,0,'J');
                        $pdf->SetX($box_x + 92 + 7);
                        $pdf->MultiCell(101,5,'CONTACTO: ' .utf8_decode(strtolower($guide->contact_name)),0,'J');
                        $pdf->SetX($box_x + 92 + 7);
                        $pdf->MultiCell(101,5,'HORARIO REC.: ' .utf8_decode(strtolower($guide->collect_time_range)),0,'J');
                        $pdf->SetX($box_x + 92 + 7);
                        $pdf->MultiCell(101,5,'FECHA REC.: ' .utf8_decode(strtolower($guide->client_date)),0,'J');
                        $pdf->SetX($box_x + 92 + 7);
                        $pdf->MultiCell(101,5,'REF: ' .utf8_decode(strtolower($guide->address_refernce)),0,'J');
                        $pdf->SetX($box_x + 92 + 7);
                    } else {
                        $pdf->SetX($box_x + 92 + 7);
                        if ($guide->collect_time_range) {
                            $pdf->Cell(34,5,'TLF: '.$guide->client_phone1,0,0,'L');
                            $pdf->Cell(67,5,'H/ENTREGA: '.$guide->collect_time_range,0,1,'L');
                        } else {
                            $pdf->Cell(70,5,'TLF: '.$guide->client_phone1,0,1,'L');
                        }
                        if ($guide->payment_method) {
                            $pdf->SetX($box_x + 92 + 7);
                            $pdf->Cell(60,5,'F/PAGO: '.$guide->payment_method,0,0,'L');
                            $pdf->Cell(41,5,'MONTO: '.$guide->amount,0,1,'L');
                        }
                        $pdf->SetX($box_x + 92 + 7);
                        $pdf->MultiCell(101,5,'REF: ' .utf8_decode(strtolower($guide->address_refernce)),0,'J');
                        $pdf->SetX($box_x + 92 + 7);
                    }
                    $pdf->SetFont('Times', 'B', 10);
                    $pdf->MultiCell(100,5,'DIRECCION: '. $direccion,0,'L');
                    $pdf->SetFont('Times', '', 11);

                // cuadro 2.2 CONTENIDO
                    $tamano2 = ($guide->type === 'RECOLECCION') ? 23 : 36;

                    //header
                    $pdf->Rect($box_x + 93, $box_y + $tamano+1, 6, $tamano2);
                    $pdf->SetFont('Times', 'B', 10);
                    $pdf->TextWithDirection($box_x + 93 + 4, $box_y + (($tamano2===23)?76:70), 'CONTENIDO', 'U');

                    // body
                    $pdf->Rect($box_x + 93 + 6, $box_y + $tamano+1, 101, $tamano2);
                    $pdf->SetFont('Times', '', 9);
                    $pdf->SetXY($box_x + 93 + 6, $box_y + $tamano+3);

                    $contenidoArray = explode(",", $guide->contenido);
                    foreach ($contenidoArray as $key => $product) {
                        $pdf->MultiCell(101,3,utf8_decode(ucwords(strtolower($product))),0,'L');
                        $pdf->SetX($box_x + 93 + 6);
                    }
                $box_y = 78+ $box_y + 4;
            }

            $disk = Storage::disk('cargo');
            $fileName = date('YmdHis') . '_cc_' . '51616516' . '_' . rand(1, 100) . '.pdf';
            $save = $disk->put($fileName, $pdf->Output('S', '', true));
            if (!$save) {
                throw new Exception('No se pudo grabar la hoja de ruta');
            }
            $res['file_name'] = $fileName;
        } catch (Exception $e) {
            Log::warning('Generar documento hoja ruta', ['exception' => $e->getMessage()]);
            $res['mensaje'] = 'Error al actualizar las coordenadas de los envios.';
        }
        return $res;
    }
}
