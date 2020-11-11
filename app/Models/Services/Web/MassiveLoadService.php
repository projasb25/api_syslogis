<?php

namespace App\Models\Services\Web;

use Exception;
use Log;

use App\Exceptions\CustomException;
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
            Log::warning('Massive Load Service error', ['expcetion' => $e->getData()[0], 'request' => $req]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Massive Load Service Query', ['expcetion' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Massive Load Service error', ['exception' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success('Exito');
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

        if (!$massive_load->ruta_doc_cargo) {
            $data = $this->repo->get_datos_ruta_cargo($massive_load->id_massive_load);
            $motivos = $this->repo->get_motivos();
            $doc = $this->generate_doc_ruta($data, $motivos);
            $this->repo->actualizar_doc_ruta($massive_load->id_massive_load, $doc['file_name']);
            $massive_load->ruta_doc_cargo = $doc['file_name'];
        }

        return Res::success(['hoja_ruta' => $ruta .'/'. $massive_load->ruta_doc_cargo]);
    }

    public function generate_doc_ruta($data, $motivos)
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
                if ($i  % 4 == 0 && $i != 0) {
                    $pdf->AddPage();
                    $box_y = 5;
                }
                // cuadro principal
                $pdf->Rect($box_x, $box_y, 190, 59);

                // cuadro 1.1 REMITENTE
                    //header
                    $pdf->Rect($box_x + 1, $box_y + 1, 6, 28);
                    $pdf->SetFont('Times', 'B', 6);
                    $pdf->TextWithDirection($box_x + 5, $box_y + 21, 'REMITENTE', 'U');

                    // body
                    $pdf->Rect($box_x + 7, $box_y + 1, 90, 28);
                    $pdf->SetFont('Times', '', 6);
                    $pdf->SetXY($box_x+8, $box_y + 1);
                    $pdf->MultiCell(89,4,'NOMBRE: '. $guide->name,0,'J');
                    $pdf->SetX($box_x+8);
                    $pdf->MultiCell(89,4,'CIUDAD: LIMA',0,'J');
                    $pdf->SetX($box_x+8);
                    $pdf->MultiCell(89,4,'FECHA: '. Carbon::createFromFormat('Y-m-d H:i:s', $guide->date_created)->format('Y-m-d'),0,'J');
                    $pdf->SetX($box_x+8);
                    $pdf->MultiCell(89,4,utf8_decode('Nº de Guía: ' . $guide->guide_number),0,'J');
                    $pdf->SetX($box_x+8);
                    $pdf->MultiCell(89,4,'DIRECCION: ' . $guide->org_address,0,'L');

                // codigo de barra
                    $pdf->code128($box_x + 20, ($box_y + 28 + 2), $guide->client_barcode , 50, 12, false);
                    $pdf->SetXY($box_x+8, ($box_y + 42 + 2));
                    $pdf->MultiCell(90,4,$guide->client_barcode. 0,0,'C');
                    $pdf->Ln(2);
                
                // cuadro 2.1 DATOS
                    //header
                    $pdf->Rect($box_x + 1, ($box_y + 41 + 2), 6, 9);
                    $pdf->SetFont('Times', 'B', 6);
                    $pdf->TextWithDirection($box_x + 5, $box_y + 56, 'DATOS', 'U');
                    
                    // body
                    $pdf->Rect($box_x + 7, ($box_y + 46 + 2), 90, 9);
                    $pdf->SetFont('Times', 'B', 10);
                    $pdf->SetXY($box_x+8, ($box_y + 49 + 2));
                    $pdf->MultiCell(45,4,'NRO. DE PIEZAS: '. 0,0,'J');
                    $pdf->SetXY($box_x+8+45, ($box_y + 49 + 2));
                    $pdf->MultiCell(45,4,'PESO SECO: '. 0,0,'J');
                    $pdf->Line($box_x+8+41, ($box_y + 46 + 2), $box_x+8+41, ($box_y + 55 + 2));
                    
                    $pdf->SetX($box_x+8);

                // // cuadro 3.1 DATOS DE ENTREGA
                //     //header
                //     $pdf->Rect($box_x + 1, ($box_y + 52 + 2) , 6, 37);
                //     $pdf->SetFont('Times', 'B', 6);
                //     $pdf->TextWithDirection($box_x + 5, $box_y + 83, 'DATOS DE ENTREGA', 'U');

                //     // body
                //     $pdf->Rect($box_x + 7, ($box_y + 52 + 2), 90, 37);
                //     $pdf->SetFont('Times', '', 6);
                //     $pdf->SetXY($box_x+8, ($box_y + 57 + 2));
                //     $pdf->MultiCell(89,6,'FIRMA:  ____________________________________________________________',0,'J');
                //     $pdf->SetX($box_x+8);
                //     $pdf->MultiCell(89,6,'NOMBRE:  _________________________________________________________',0,'J');
                //     $pdf->SetX($box_x+8);
                //     $pdf->MultiCell(89,6,'VINCULO:  _________________________________________________________',0,'J');
                //     $pdf->SetX($box_x+8);
                //     $pdf->MultiCell(89,6,'DNI:  __________________________________',0,'J');
                //     $pdf->SetX($box_x+8);
                //     $pdf->Cell(44,6,'FECHA: _______ / _______ / _______',0,0,'L');
                //     $pdf->Cell(44,6,'Hora: __________________',0,0,'L');
                //     $pdf->SetX($box_x+8);

                // cuadro 1.2 DESTINATARIO
                    //header
                    $pdf->Rect($box_x + 99, $box_y + 1, 6, 19);
                    $pdf->SetFont('Times', 'B', 6);
                    $pdf->TextWithDirection($box_x + 99 + 4, $box_y + 19, 'DESTINATARIO', 'U');

                    // body
                    $pdf->Rect($box_x + 99 + 6, $box_y + 1, 84, 19);
                    $pdf->SetFont('Times', '', 6);
                    $pdf->SetXY($box_x + 99 + 7, $box_y + 1);
                    $pdf->MultiCell(89,4,'NOMBRE: '. $guide->client_name,0,'J');
                    $pdf->SetX($box_x + 99 + 7);
                    $pdf->MultiCell(89,4,'CIUDAD: '. $guide->province,0,'J');
                    $pdf->SetX($box_x + 99 + 7);
                    $pdf->Cell(27,4,'TELEFONO: '. $guide->client_phone1 ,0,0,'L');
                    $pdf->Cell(52,4,'EMAIL: '. utf8_decode(strtolower($guide->client_email)),0,1,'L'); //lower to space
                    $pdf->SetX($box_x + 99 + 7);
                    $pdf->MultiCell(89,4,'DIRECCION: '. utf8_decode(strtolower($guide->address)),0,'L');

                // cuadro 2.2 CONTENIDO
                    //header
                    $pdf->Rect($box_x + 99, $box_y + 21, 6, 37);
                    $pdf->SetFont('Times', 'B', 7);
                    $pdf->TextWithDirection($box_x + 99 + 4, $box_y + 47, 'CONTENIDO', 'U');

                    // body
                    $pdf->Rect($box_x + 99 + 6, $box_y + 21, 84, 37);
                    $pdf->SetFont('Times', 'B', 5);
                    $pdf->SetXY($box_x + 99 + 7, $box_y + 22);

                    $contenidoArray = explode(",", $guide->contenido);
                    foreach ($contenidoArray as $key => $product) {
                        // $pdf->MultiCell(89,2,$product->sku_code.' - '.$product->sku_description,0,'J');
                        $pdf->MultiCell(89,2,$product,0,'J');
                        $pdf->SetX($box_x + 99 + 7);
                    }

                // // cuadro 2.3 OBSERVACIONES
                //     //header
                //     // $pdf->Rect($box_x + 99, $box_y + 66, 6, 25);
                //     $pdf->SetFont('Times', 'B', 6);
                //     $pdf->SetXY($box_x + 92 + 7, $box_y + 59);
                //     $pdf->Cell(26,4,'PRIMERA VISITA',1,0,'L');
                //     $pdf->Cell(26,4,'SEGUNDA VISITA',1,0,'L');
                //     $pdf->Cell(4,4,'1',1,0,'L');
                //     $pdf->Cell(4,4,'2',1,0,'L');
                //     $pdf->Cell(30,4,'MOTIVO DE REZAGO',1,1,'L');

                //     $pdf->SetX($box_x + 92 + 7);
                //     $pdf->Cell(26,4,'FECHA:',1,0,'L');
                //     $pdf->Cell(26,4,'FECHA:',1,1,'L');
                //     // $pdf->Cell(4,4,'',1,0,'L');
                //     // $pdf->Cell(4,4,'',1,1,'L');

                //     $pdf->SetX($box_x + 92 + 7);
                //     $pdf->Cell(26,4,'HORA:',1,0,'L');
                //     $pdf->Cell(26,4,'HORA:',1,1,'L');
                //     // $pdf->Cell(4,4,'',1,0,'L');
                //     // $pdf->Cell(4,4,'',1,1,'L');

                //     $pdf->SetX($box_x + 92 + 7);
                //     $pdf->Cell(26,4,'CODIGO:',1,0,'L');
                //     $pdf->Cell(26,4,'CODIGO:',1,1,'L');
                //     // $pdf->Cell(4,4,'',1,0,'L');
                //     // $pdf->Cell(4,4,'',1,1,'L');

                //     $pdf->SetX($box_x + 92 + 7);
                //     $pdf->Cell(52,4,'OBSERVACIONES',1,1,'C');
                    
                //     $pdf->SetX($box_x + 92 + 7);
                //     $pdf->Cell(52,12,'',1,1,'C');

                //     $pdf->SetXY($box_x + 144 + 7, $box_y + 63);
                //     foreach ($motivos as $key => $motivo) {
                //         $pdf->Cell(4,4,'',1,0,'L');
                //         $pdf->Cell(4,4,'',1,0,'L');
                //         $pdf->Cell(30,4,utf8_decode($motivo->name),1,1,'L');
                //         $pdf->SetX($box_x + 144 + 7);
                //     }
                //     $pdf->Cell(4,4,'',1,0,'L');
                //     $pdf->Cell(4,4,'',1,0,'L');
                //     $pdf->Cell(30,4,'OTROS',1,1,'L');

                $box_y = 59 + $box_y + 2;
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

            if (!$massive_load->ruta_marathon) {
                $data = $this->repo->get_datos_ruta_cargo($massive_load->id_massive_load);
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
                    $pdf->code128($box_x + 8, ($box_y + 6 + 2), $guide->client_barcode, 50, 6, false);
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
                    $pdf->Cell(65,5,$guide->client_barcode,0,1,'C');
                    $pdf->SetX($box_x);
                    $pdf->SetFont('Times', '', 6);
                    $pdf->MultiCell(65,3,utf8_decode($guide->address),0,'C');
                    $pdf->Ln(2);

                $box_x = 65 + $box_x + 2;
            }

            $disk = Storage::disk('marathon');
            $fileName = date('YmdHis') . '_cc_' . $guide->client_barcode . '_' . rand(1, 100) . '.pdf';
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
}
