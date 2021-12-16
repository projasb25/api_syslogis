<?php

namespace App\Models\Services\Web;

use Exception;
use Log;

use App\Exceptions\CustomException;
use App\Helpers\ArrayHelper;
use App\Helpers\ResponseHelper as Res;
use App\Models\Repositories\Web\MassiveLoadRepository;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Illuminate\Support\Facades\Storage;

class MassiveLoadService
{
    protected $repo;
    public function __construct(MassiveLoadRepository $repository)
    {
        $this->repo = $repository;
    }

    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $req = $request->all();
            $data['count'] = count($req['data']);
            $data['username'] = $user->username;
            $data['data'] = $req['data'];
            $data['id_corporation'] = $req['id_corporation'];
            $data['id_organization'] = $req['id_organization'];
            $data['date_loaded'] = $req['date_loaded'];
            $data['id_load_template'] =  $req['id_load_template'];

            $id = $this->repo->insertMassiveLoad($data);

            $res =[
                'id_massive_load' => $id
            ];
        } catch (CustomException $e) {
            Log::warning('Massive Load Service error', ['expcetion' => $e->getData()[0], 'request' => $req]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Massive Load Service Query', ['expcetion' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Massive Load Service error', ['exception' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success($res);
    }

    public function unitaria(Request $request)
    {
        try {
            $user = auth()->user();
            $req = $request->all();

            $param = [];
            foreach ($req['details']['data'] as $key => $value) {
                $client_info = [
                    'org_name' => $value['org_name'] ?? null,
                    'client_name' => $value['client_name'] ?? null,
                    'client_dni' => $value['client_dni'] ?? null,
                    'address' => $value['address'] ?? null,
                    'client_phone1' => $value['client_phone1'] ?? null,
                    'district' => $value['district'] ?? null,
                    'province' => $value['province'] ?? null,
                    'department' => $value['department'] ?? null,
                ];
                $aux['guide_number'] = $value['guide_number'] ?? null;
                $aux['seg_code'] = $value['seg_code'] ?? null;
                $aux['client_barcode'] = $value['client_barcode'] ?? null;
                $aux['sku_description'] = $value['sku_description'] ?? null;
                $aux['sku_pieces'] = $value['sku_pieces'] ?? null;
                $aux['seller_name'] = $value['seller_name'] ?? null;
                $aux['client_info'] = json_encode($client_info);
                array_push($param, $aux);
            }

            $query = 'CALL SP_INS_UNIT_LOAD(:header, :details, :username)';
            $data['header'] = json_encode([]);
            $data['details'] = json_encode($param);
            $data['username'] = json_encode($user->getIdentifierData());

            $data = $this->repo->execute_store($query, $data);
        } catch (CustomException $e) {
            Log::warning('Massive Load Unitaria error', ['expcetion' => $e->getData()[0], 'request' => $req]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            if ((int) $e->getCode() >= 60000) {
                Log::warning('Massive Load Unitaria error', ['expcetion' => $e->errorInfo[2], 'request' => $req]);
                return Res::error([$e->errorInfo[2], (int) $e->getCode()], 400);
            }
            Log::warning('Massive Load Unitaria error', ['expcetion' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Massive Load Unitaria error', ['exception' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success('Exito');
    }

    public function process(Request $request)
    {
        try {
            $user = auth()->user();
            $req = $request->all();
            $data['username'] = $user->username;
            $data['data'] = $req['data'];
            $data['id_massive_load'] = $req['id_massive_load'];

            $load = $this->repo->get($data['id_massive_load']);
            if (!$load) {
                throw new CustomException(['Registro no encontrado.', 2121], 404);
            }
            if ($load->status !== "PENDIENTE") {
                throw new CustomException(['La carga masiva ya fue procesada.', 2120], 400);
            }

            $data['id_corporation'] = $load->id_corporation;
            $data['id_organization'] = $load->id_organization;

            $adresses = $this->repo->process($data);

            $propiedad = $this->repo->getPropiedad('apigmaps_call');
            if ($propiedad && $propiedad->value === '1') {
                $this->obtenerCoordenadas($adresses, $data['id_massive_load']);
            }

        } catch (CustomException $e) {
            Log::warning('Massive Load Service procesar error', ['expcetion' => $e->getData()[0], 'request' => $req]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Massive Load Service procesar Query', ['expcetion' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Massive Load Service procesar error', ['exception' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success('Exito');
    }

    public function publicoInsertarCarga(Request $request)
    {
        try {
            $req = $request->get('data');
            $obligatorios = [
                'seg_code','guide_number','sku_description','client_dni','client_name',
                'client_address','department','district','province'
            ];

            $id = $this->repo->publicoInsertarCarga($req);

            Log::info('Publico - Carga masiva exito', ['numero de registros' => count($req)]);
        } catch (CustomException $e) {
            Log::warning('Publico - Carga masiva', ['expcetion' => $e->getData()[0], 'request' => $req]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Publico - Carga masiva', ['expcetion' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Publico - Carga masiva', ['exception' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success('Carga insertada correctamente');
    }

    public function obtenerCoordenadas($direcciones, $id_massive_load)
    {
        $res['success'] = false;
        $coordenadas = [];

        foreach ($direcciones as $key => $value) {
            if (!$value->latitude) {
                # Limpiamos la direccion para que no haya problemas con la api de google
                $direccion = $this->sanitizeAdress($value->address);

                try {
                    $client = new Client(['base_uri' => env('GOOGLEAPIS_GEOCODE_URL')]);
                    $url = "json?address=" . $direccion . "-" . $value->district . "&components=country:PE&key=" . env('GOOGLEAPIS_GEOCODE_KEY');

                    $req = $client->request('GET', $url);
                    $resp = json_decode($req->getBody()->getContents());

                    if (empty($resp->results)) {
                        Log::warning('Obtener coordenadas nula de google.', ['direccion' => $direccion , 'response' => array($resp), 'url' => $url]);
                        $lat = null;
                        $lng = null;
                    } else {
                        $lat = $resp->results[0]->geometry->location->lat;
                        $lng = $resp->results[0]->geometry->location->lng;
                    }
                } catch (RequestException $e) {
                    Log::warning('Obtener coordenadas: hubo un problema con la api de google.', [
                        'endpoint' => $url,
                        'id_address' => $value->id_address,
                        'direccion' => $direccion
                    ]);
                    $lat = null;
                    $lng = null;
                }

                array_push($coordenadas, [
                    'id_address' => $value->id_address,
                    'latitude' => $lat,
                    'longitude' => $lng
                ]);
            }
        }

        try {
            $this->repo->actualizarCoordenadas($coordenadas);

            $res['success'] = true;
            Log::info('Obtener coordenadas con exito', [
                'id_massive_load' => $id_massive_load,
                'nro_registros_actualizados' => count($coordenadas)
            ]);
        } catch (Exception $e) {
            Log::warning('Obtener coordenadas error', ['exception' => $e->getMessage()]);
            $res['mensaje'] = 'Error al actualizar las coordenadas de los envios.';
        }

        return $res;
    }

    public function sanitizeAdress($adress)
    {
        $dont = ['$', '#', '&', '"', '/', '(', ')', '-'];
        return str_replace($dont, '', $adress);
    }

    public function print_cargo($request)
    {
        $massive_load = $this->repo->get($request->get('id_massive_load'));

        $disk = Storage::disk('cargo');
        $ruta = url('storage/cargo/');

        $file_exists = (Storage::disk('cargo')->exists($massive_load->ruta_doc_cargo));
        if (!$massive_load->ruta_doc_cargo || !$file_exists) {
            if ($massive_load->id_corporation === 4) {
                $data = $this->repo->get_datos_ruta_cargo_oechsle($massive_load->id_massive_load);
                $motivos = $this->repo->get_motivos();
                $doc = $this->generar_doc_cargo_tipo2($data, $motivos);
            } elseif ($massive_load->id_organization === 66) {
                $data = $this->repo->get_datos_ripley_reversa($massive_load->id_massive_load);
                $doc = $this->generar_doc_cargo_tipo3($data);
            } elseif ($massive_load->id_organization === 74) {
                $data = $this->repo->get_datos_ruta_cargo_ripley_seller($massive_load->id_massive_load);
                $seller_data = $this->repo->get_datos_ripley_seller($data[0]->client_name);
                $doc = $this->generar_doc_cargo_tipo4($data, $seller_data);
            } else {
                $data = $this->repo->get_datos_ruta_cargo_ripley($massive_load->id_massive_load);
                $doc = $this->generar_doc_cargo_tipo1($data);
            }
            $this->repo->actualizar_doc_ruta($massive_load->id_massive_load, $doc['file_name']);
            $massive_load->ruta_doc_cargo = $doc['file_name'];
        }

        return Res::success(['hoja_ruta' => $ruta .'/'. $massive_load->ruta_doc_cargo]);
    }

    public function print_cargo_guide($request)
    {
        $massive_load = $this->repo->get($request->id_guide);

        $disk = Storage::disk('cargo');
        $ruta = url('storage/cargo/');

        $file_exists = (Storage::disk('cargo')->exists($massive_load->ruta_doc_cargo));
        if (!$massive_load->ruta_doc_cargo || !$file_exists) {
            if ($massive_load->id_corporation === 4) {
                $data = $this->repo->get_datos_ruta_cargo_oechsle($massive_load->id_massive_load);
                $motivos = $this->repo->get_motivos();
                $doc = $this->generar_doc_cargo_tipo2($data, $motivos);
            } elseif ($massive_load->id_organization === 66) {
                $data = $this->repo->get_datos_ripley_reversa($massive_load->id_massive_load);
                $doc = $this->generar_doc_cargo_tipo3($data);
            } elseif ($massive_load->id_organization === 74) {
                $data = $this->repo->get_datos_ruta_cargo_ripley($massive_load->id_massive_load);
                $seller_data = $this->repo->get_datos_ripley_seller($data[0]->client_name);
                $doc = $this->generar_doc_cargo_tipo4($data. $seller_data);
            } else {
                $data = $this->repo->get_datos_ruta_cargo_ripley($massive_load->id_massive_load);
                $doc = $this->generar_doc_cargo_tipo1($data);
            }
            // $this->repo->actualizar_doc_ruta($massive_load->id_massive_load, $doc['file_name']);
            $massive_load->ruta_doc_cargo = $doc['file_name'];
        }

        return Res::success(['hoja_ruta' => $ruta .'/'. $massive_load->ruta_doc_cargo]);
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
                    $pdf->MultiCell(85,6,'FECHA: '. Carbon::createFromFormat('Y-m-d H:i:s', $guide->date_loaded)->format('Y-m-d'),0,'J');
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

    public function generar_doc_cargo_tipo2($data, $motivos)
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
                if ($i  % 2 == 0 && $i != 0) {
                    $pdf->AddPage();
                    $box_y = 5;
                }
                // cuadro principal
                $pdf->Rect($box_x, $box_y, 200, 119);
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
                    $pdf->MultiCell(85,6,'CIUDAD: LIMA',0,'J');
                    $pdf->SetX($box_x+6);
                    $pdf->MultiCell(85,6,'FECHA: '. Carbon::createFromFormat('Y-m-d H:i:s', $guide->date_created)->format('Y-m-d'),0,'J');
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
                    $pdf->MultiCell(45,4,'NRO. PIEZAS: '. 0,0,'J');
                    $pdf->SetXY($box_x+8+45, ($box_y + 66 + 2));
                    $pdf->MultiCell(45,4,'PESO SECO: '. 0,0,'J');
                    $pdf->Line($box_x+8+41, ($box_y + 59 + 2), $box_x+8+41, ($box_y + 76 + 2));

                    $pdf->SetX($box_x+8);
                // cuadro 1.2 DESTINATARIO
                    //header
                    $pdf->Rect($box_x + 93, $box_y + 0, 6, 41);
                    $pdf->SetFont('Times', 'B', 11);
                    $pdf->TextWithDirection($box_x + 93 + 4, $box_y + 35, 'DESTINATARIO', 'U');

                    // body
                    $nombre = utf8_decode(ucwords(strtolower($guide->client_name)));
                    $distrito = utf8_decode(ucwords(strtolower($guide->district)));
                    $direccion = utf8_decode(ucwords(strtolower($guide->address)));
                    $pdf->Rect($box_x + 93 + 6, $box_y + 0, 101, 41);
                    $pdf->SetFont('Times', '', 11);
                    $pdf->SetXY($box_x + 92 + 7, $box_y + 1);
                    $pdf->MultiCell(101,5,'NOMBRE: '. $nombre,0,'L');
                    $pdf->SetX($box_x + 92 + 7);
                    $pdf->MultiCell(101,5,'DNI: '. $guide->client_dni,0,'L');
                    $pdf->SetX($box_x + 92 + 7);
                    $pdf->MultiCell(101,5,'DIST.: ' . $distrito,0,'J');
                    $pdf->SetX($box_x + 92 + 7);
                    $pdf->MultiCell(101,5,'TLF.: ' . $guide->client_phone1,0,'J');
                    $pdf->SetX($box_x + 92 + 7);
                    $pdf->MultiCell(101,5,'EMAIL.: ' .utf8_decode(strtolower($guide->client_email)),0,'J');
                    $pdf->SetX($box_x + 92 + 7);
                    $pdf->SetFont('Times', 'B', 10);
                    $pdf->MultiCell(100,5,'DIRECCION: '. $direccion,0,'L');
                    $pdf->SetFont('Times', '', 11);

                // cuadro 3.1 DATOS DE ENTREGA
                    //header
                    $pdf->Rect($box_x + 0, ($box_y + 77 + 2) , 6, 40);
                    $pdf->SetFont('Times', 'B', 10);
                    $pdf->TextWithDirection($box_x + 5, $box_y + 118, 'DATOS DE ENTREGA', 'U');

                    // body
                    $pdf->Rect($box_x + 6, ($box_y + 77 + 2), 85, 40);
                    $pdf->SetFont('Times', '', 9);
                    $pdf->SetXY($box_x+7, ($box_y + 80 + 2));
                    $pdf->MultiCell(85,6,'FIRMA:  ____________________________________________',0,'J');
                    $pdf->SetX($box_x+7);
                    $pdf->MultiCell(85,6,'NOMBRE:  __________________________________________',0,'J');
                    $pdf->SetX($box_x+7);
                    $pdf->MultiCell(85,6,'VINCULO:  _________________________________________',0,'J');
                    $pdf->SetX($box_x+7);
                    $pdf->MultiCell(85,6,'DNI:  ______________________________________________',0,'J');
                    $pdf->SetX($box_x+7);
                    $pdf->MultiCell(85,6,'FECHA: ________ / ________ / ________',0,'J');
                    $pdf->SetX($box_x+7);
                    $pdf->MultiCell(85,6,'HORA: ______________:______________',0,'J');
                    $pdf->SetX($box_x+7);

                // cuadro 2.2 CONTENIDO
                    //header
                    $pdf->Rect($box_x + 93, $box_y + 42, 6, 36);
                    $pdf->SetFont('Times', 'B', 10);
                    $pdf->TextWithDirection($box_x + 93 + 4, $box_y + 70, 'CONTENIDO', 'U');

                    // body
                    $pdf->Rect($box_x + 93 + 6, $box_y + 42, 101, 36);
                    $pdf->SetFont('Times', '', 9);
                    $pdf->SetXY($box_x + 93 + 6, $box_y + 44);

                    $contenidoArray = explode(",", $guide->contenido);
                    foreach ($contenidoArray as $key => $product) {
                        $pdf->MultiCell(101,3,utf8_decode(ucwords(strtolower($product))),0,'L');
                        $pdf->SetX($box_x + 93 + 6);
                    }

                // cuadro 2.3 OBSERVACIONES
                    //header
                    $pdf->SetFont('Times', '', 9);
                    $pdf->SetXY($box_x + 86 + 7, $box_y + 79);
                    $pdf->Cell(28,4,'PRIMERA VISITA',1,0,'L');
                    $pdf->Cell(28,4,'SEGUNDA VISITA',1,0,'L');
                    $pdf->Cell(4,4,'1',1,0,'L');
                    $pdf->Cell(4,4,'2',1,0,'L');
                    $pdf->Cell(43,4,'MOTIVO DE REZAGO',1,1,'L');

                    $pdf->SetX($box_x + 86 + 7);
                    $pdf->Cell(28,5,'FECHA:',1,0,'L');
                    $pdf->Cell(28,5,'FECHA:',1,1,'L');

                    $pdf->SetX($box_x + 86 + 7);
                    $pdf->Cell(28,5,'HORA:',1,0,'L');
                    $pdf->Cell(28,5,'HORA:',1,1,'L');

                    $pdf->SetX($box_x + 86 + 7);
                    $pdf->Cell(28,5,'CODIGO:',1,0,'L');
                    $pdf->Cell(28,5,'CODIGO:',1,1,'L');

                    $pdf->SetX($box_x + 86 + 7);
                    $pdf->Cell(56,5,'OBSERVACIONES',1,1,'C');

                    $pdf->SetX($box_x + 86 + 7);
                    if (is_null($guide->observaciones)) {
                        $pdf->Cell(56,16,'',1,1,'C');
                    } else {
                        $pdf->SetFont('Times', '', 10);
                        $observaciones = explode(";", $guide->observaciones);
                        $detalle = explode("||", $observaciones[0]);
                        $pdf->Cell(56,5,utf8_decode($detalle[0]),'LR',1,'L');
                        $pdf->SetX($box_x + 86 + 7);
                        $pdf->Cell(56,5,utf8_decode(strtolower($detalle[1])),'LR',1,'L');
                        $pdf->SetX($box_x + 86 + 7);
                        $pdf->Cell(56,6,utf8_decode(strtolower($detalle[2])),'LR',1,'L');
                    }

                    $pdf->SetFont('Times', '', 9);
                    $pdf->SetXY($box_x + 142 + 7, $box_y + 83);
                    foreach ($motivos as $key => $motivo) {
                        $pdf->Cell(4,5,'',1,0,'L');
                        $pdf->Cell(4,5,'',1,0,'L');
                        $pdf->Cell(43,5,utf8_decode($motivo->name),1,1,'L');
                        $pdf->SetX($box_x + 142 + 7);
                    }
                    $pdf->Cell(4,6,'',1,0,'L');
                    $pdf->Cell(4,6,'',1,0,'L');
                    $pdf->Cell(43,6,'OTROS',1,1,'L');

                $box_y = 119+ $box_y + 3;
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

    public function print_marathon($request)
    {
        try {
            $massive_load = $this->repo->get($request->get('id_massive_load'));

            $disk = Storage::disk('marathon');
            $ruta = url('storage/marathon/');

            $file_exists = (Storage::disk('cargo')->exists($massive_load->ruta_doc_cargo));

            if (!$massive_load->ruta_marathon || !$file_exists) {
                if ($massive_load->id_corporation === 4) {
                    $data = $this->repo->get_datos_ruta_cargo_oechsle($massive_load->id_massive_load);
                } else {
                    $data = $this->repo->get_datos_ruta_cargo_ripley($massive_load->id_massive_load);
                }
                $doc = $this->generate_doc_marathon($data);
                $this->repo->actualizar_doc_marathon($massive_load->id_massive_load, $doc['file_name']);
                $massive_load->ruta_marathon = $doc['file_name'];
            }

            Log::info('Obtener documento Marathon exitoso', ['id_massive_load' => $massive_load->id_massive_load]);
        } catch (CustomException $e) {
            Log::warning('Obtener documento Marathon', ['expcetion' => $e->getData()[0], 'id_massive_load' => $massive_load->id_massive_load]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Obtener documento Marathon', ['expcetion' => $e->getMessage(), 'id_massive_load' => $massive_load->id_massive_load]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Obtener documento Marathon', ['exception' => $e->getMessage(), 'id_massive_load' => $massive_load->id_massive_load]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success(['hoja_ruta' => $ruta .'/'. $massive_load->ruta_marathon]);
    }

    public function generate_doc_marathon($data)
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
            $pdf->SetFont('Times', '', 6);
            $y = $pdf->GetY();
            $pdf->SetAutoPageBreak(false);

            $box_x = 5;
            $box_y = 5;
            $fila = 1;

            foreach ($data as $i => $guide) {
                if ($i  % 3 == 0 && $i !== 0) {
                    $box_y = 27 + $box_y + 1;
                    $box_x = 5;
                    if ($fila % 10 === 0) {
                        $pdf->AddPage();
                        $box_y = 5;
                        $box_x = 5;
                    }
                    $fila+=1;
                }
                // cuadro principal
                $pdf->Rect($box_x, $box_y, 65, 27);
                // codigo de barra
                    if (isset($guide->client_barcode)) {
                        $cod_barra = $guide->client_barcode;
                    } else {
                        $cod_barra = $guide->guide_number;
                    }

                    $pdf->code128($box_x + 8, ($box_y + 6 + 2), $cod_barra, 50, 6, false);
                    $pdf->SetY($box_y);
                    $pdf->SetX($box_x);
                    $pdf->Cell(32,4, $guide->name . ' - ' . ($i+1) ,0,0,'L');
                    $pdf->Cell(33,4,'TELF: '. $guide->client_phone1,0,1,'R');
                    $pdf->SetX($box_x);
                    $pdf->Cell(65,4, $guide->client_name,0,1,'L');
                    $pdf->SetX($box_x);
                    $pdf->Cell(65,6,'',0,1,'L');
                    $pdf->SetX($box_x);
                    $pdf->SetFont('Times', '', 8);
                    $pdf->Cell(65,5,$cod_barra,0,1,'C');
                    $pdf->SetX($box_x);
                    $pdf->SetFont('Times', '', 6);
                    $pdf->MultiCell(65,3,utf8_decode($guide->address),0,'C');
                    $pdf->Ln(2);



                $box_x = 65 + $box_x + 2;
            }

            $disk = Storage::disk('marathon');
            $fileName = date('YmdHis') . '_cc_' . $cod_barra . '_' . rand(1, 100) . '.pdf';
            $save = $disk->put($fileName, $pdf->Output('S', '', true));
            if (!$save) {
                throw new Exception('No se pudo grabar la hoja marathon');
            }
            $res['file_name'] = $fileName;
        } catch (Exception $e) {
            Log::warning('Generar Documento Marathon', ['exception' => $e->getMessage()]);
            throw $e;
        }
        return $res;
    }

    public function generar_doc_cargo_tipo3($data)
    {
        // $data = $this->repo->get_datos_ripley_reversa(13840);
        // dd($data);
        try {
            $pdf = new CustomPDF();
            $cellMargin = 2 * 1.000125;
            $lmargin = 5;
            $rmargin = 5;
            $pdf->AliasNbPages();
            $pdf->AddPage();
            $pdf->SetMargins($lmargin, $rmargin);
            $pdf->Ln(0);

            $x = $pdf->GetX();
            $pdf->SetAutoPageBreak(false);

            $y = 10;
            $x = 5;

            $fila = 0;
            $columna = 0;
            $client_info_json = '{"address": "direccion prueba", "district": "LIMA", "province": "LIMA", "client_dni": "99999", "department": "LIMA", "client_name": "cliente prueba", "client_phone1": "0239239", "id_organization": 10, "id_ripley_seller": 1}';
            foreach ($data as $i => $item) {
                $client_info = json_decode($item->client_info);
                if ($columna % 2 == 0 && $columna != 0) {
                    $columna = 0;
                    $fila++;
                }
                if ($fila % 2 == 0 && $fila != 0) {
                    $fila = 0;
                    $pdf->AddPage();
                }

                $x = 8 + (100 * $columna);
                $y = 10 + (130 * $fila);

                $pdf->SetFont('Times', 'B', 10);
                $pdf->SetXY($x,$y);
                $pdf->Cell(95, 8, 'Datos de Destinatario',1,1,'C');
                $pdf->SetFont('Times', '', 10);
                $pdf->SetX($x);
                $pdf->Cell(95, 8, 'Seller: ' . $item->seller_name,1,1);
                $pdf->SetX($x);
                $pdf->MultiCell(95, 8, 'Direccion: ' .$item->address,1);
                $pdf->SetX($x);
                $pdf->Cell(95, 8, 'Telefono: '.$item->client_phone1,1,1);
                $pdf->SetX($x);
                $pdf->Cell(95, 8, 'Contacto: '.$item->client_name,1,1);

                $pdf->SetFont('Times', 'B', 10);
                $pdf->SetX($x);
                $pdf->Cell(95, 8, 'Datos de Producto',1,1,'C');
                $pdf->SetFont('Times', '', 10);
                $pdf->SetX($x);
                $pdf->Cell(95, 15, '','TLR',1,'C');
                $pdf->code128($x + 25, ($y + 50), $item->client_barcode , 50, 12, false);
                $pdf->SetX($x);
                $pdf->Cell(95, 7, $item->client_barcode,'LRB',1,'C');
                $pdf->SetX($x);
                $pdf->Cell(95, 8, 'CUD: '.$item->seg_code,1,1,'C');
                $pdf->SetX($x);
                $pdf->Cell(95, 8, 'Guia: '.$item->guide_number,1,1,'C');
                $pdf->SetX($x);
                $pdf->Cell(95, 8, $item->sku_description,1,1,'');
                $pdf->SetX($x);
                $pdf->Cell(95, 8, 'Cantidad: ' . $item->sku_pieces,1,1,'');
                $pdf->SetX($x);
                $pdf->Cell(95, 8, 'Cliente: '.$client_info->client_name,1,1,'');
                $pdf->SetX($x);
                $pdf->Cell(95, 8, 'DNI: '.$client_info->client_dni,1,1,'');

                $columna++;
            }

            // $pdf->Output();
            $disk = Storage::disk('cargo');
            $fileName = date('YmdHis') . '_cc_' . 'reporte_inversa' . '_' . rand(1, 100) . '.pdf';
            $save = $disk->put($fileName, $pdf->Output('S', '', true));
            if (!$save) {
                throw new Exception('No se pudo grabar la hoja de ruta');
            }
            $res['file_name'] = $fileName;
        } catch (Exception $e) {
            Log::warning('Generar Documento Marathon', ['exception' => $e->getMessage()]);
            throw $e;
        }
        return $res;
    }

    public function generar_doc_cargo_tipo4($data, $seller_data)
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
                    $pdf->MultiCell(85,5,'NOMBRE: '. utf8_decode($guide->ripley_name),0,'J');
                    $pdf->SetX($box_x+6);
                    if ($guide->name === 'InRetail') {
                        $pdf->Cell(34,5,'CIUDAD: LIMA',0,0,'L');
                        $pdf->Cell(51,5,'COD.: '.$guide->alt_code1,0,1,'L');
                    } else {
                        $pdf->Cell(85,5,'DISTRITO:'.$guide->rdistrict,0,1,'L');
                    }
                    $pdf->SetX($box_x+6);
                    $pdf->MultiCell(85,5,'FECHA: '. Carbon::createFromFormat('Y-m-d H:i:s', $guide->date_loaded)->format('Y-m-d'),0,'J');
                    $pdf->SetX($box_x+6);
                    $pdf->SetFont('Times', 'B', 11);
                    $pdf->MultiCell(85,5,utf8_decode('Nº de Guía: ' . $guide->guide_number),0,'J');
                    $pdf->SetFont('Times', '', 11);
                    $pdf->SetX($box_x+6);
                    $pdf->MultiCell(84,5,'DIRECCION: ' . utf8_decode(ucwords(strtolower($guide->raddress))),0,'L');

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
