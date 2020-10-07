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
            $this->obtenerCoordenadas($adresses, $data['id_massive_load']);
            
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
                    $url = "json?address=" . $direccion . "&components=country:PE&key=" . env('GOOGLEAPIS_GEOCODE_KEY');

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

        $disk = Storage::disk('public');
        $ruta = $disk->getDriver()->getAdapter()->getPathPrefix();

        if (!$massive_load->ruta_doc_cargo) {
            $data = $this->repo->get_datos_ruta_cargo($massive_load->id_massive_load);
            $doc = $this->generate_doc_ruta($data);
            $this->repo->actualizar_doc_ruta($massive_load->id_massive_load, $doc['file_name']);
            $massive_load->ruta_doc_cargo = $doc['file_name'];
        }

        return Res::success(['hoja_ruta' => $ruta . $massive_load->ruta_doc_cargo ]);
    }

    public function generate_doc_ruta($data)
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
                $pdf->Rect($box_x, $box_y, 190, 92);

                // cuadro 1.1 REMITENTE
                    //header
                    $pdf->Rect($box_x + 1, $box_y + 1, 6, 28);
                    $pdf->SetFont('Times', 'B', 6);
                    $pdf->TextWithDirection($box_x + 5, $box_y + 21, 'REMITENTE', 'U');

                    // body
                    $pdf->Rect($box_x + 7, $box_y + 1, 90, 28);
                    $pdf->SetFont('Times', '', 6);
                    $pdf->SetXY($box_x+8, $box_y + 1);
                    $pdf->MultiCell(89,4,'NOMBRE: $adfajlsdfjlkasjdf',0,'J');
                    $pdf->SetX($box_x+8);
                    $pdf->MultiCell(89,4,'CIUDAD: $asdfasdfasdfasdfasdfasdfasdfasd',0,'J');
                    $pdf->SetX($box_x+8);
                    $pdf->MultiCell(89,4,'FECHA: '. Carbon::createFromFormat('Y-m-d H:i:s', $guide->date_created)->format('Y-m-d'),0,'J');
                    $pdf->SetX($box_x+8);
                    $pdf->MultiCell(89,4,'SEGUIMIENTO: ' . $guide->seg_code,0,'J');
                    $pdf->SetX($box_x+8);
                    $pdf->MultiCell(89,4,'COD.ALTERNO: '. $guide->alt_code1,0,'J');
                    $pdf->SetX($box_x+8);
                    $pdf->MultiCell(89,4,'DIRECCION: Direccion?',0,'L');

                // codigo de barra
                    $pdf->code128($box_x + 20, ($box_y + 28 + 2), $guide->client_barcode , 50, 9, false);
                    $pdf->Ln(2);
                
                // cuadro 2.1 DATOS
                    //header
                    $pdf->Rect($box_x + 1, ($box_y + 38 + 2), 6, 13);
                    $pdf->SetFont('Times', 'B', 6);
                    $pdf->TextWithDirection($box_x + 5, $box_y + 50, 'DATOS', 'U');
                    
                    // body
                    $pdf->Rect($box_x + 7, ($box_y + 38 + 2), 90, 13);
                    $pdf->SetFont('Times', 'B', 10);
                    $pdf->SetXY($box_x+8, ($box_y + 43 + 2));
                    $pdf->MultiCell(45,4,'NRO. DE PIEZAS: '. $guide->total_pieces,0,'J');
                    $pdf->SetXY($box_x+8+45, ($box_y + 43 + 2));
                    $pdf->MultiCell(45,4,'PESO SECO: '. $guide->total_weight,0,'J');
                    $pdf->Line($box_x+8+41, ($box_y + 38 + 2), $box_x+8+41, ($box_y + 51 + 2));
                    
                    $pdf->SetX($box_x+8);

                // cuadro 3.1 DATOS DE ENTREGA
                    //header
                    $pdf->Rect($box_x + 1, ($box_y + 52 + 2) , 6, 37);
                    $pdf->SetFont('Times', 'B', 6);
                    $pdf->TextWithDirection($box_x + 5, $box_y + 83, 'DATOS DE ENTREGA', 'U');

                    // body
                    $pdf->Rect($box_x + 7, ($box_y + 52 + 2), 90, 37);
                    $pdf->SetFont('Times', '', 6);
                    $pdf->SetXY($box_x+8, ($box_y + 57 + 2));
                    $pdf->MultiCell(89,6,'FIRMA:  ____________________________________________________________',0,'J');
                    $pdf->SetX($box_x+8);
                    $pdf->MultiCell(89,6,'NOMBRE:  _________________________________________________________',0,'J');
                    $pdf->SetX($box_x+8);
                    $pdf->MultiCell(89,6,'VINCULO:  _________________________________________________________',0,'J');
                    $pdf->SetX($box_x+8);
                    $pdf->MultiCell(89,6,'DNI:  __________________________________',0,'J');
                    $pdf->SetX($box_x+8);
                    $pdf->Cell(44,6,'FECHA: _______ / _______ / _______',0,0,'L');
                    $pdf->Cell(44,6,'Hora: __________________',0,0,'L');
                    $pdf->SetX($box_x+8);

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
                    $pdf->MultiCell(89,4,'DIRECCION: '. utf8_decode(strtolower($guide->address->address)),0,'L');

                // cuadro 2.2 CONTENIDOs
                    //header
                    $pdf->Rect($box_x + 99, $box_y + 21, 6, 37);
                    $pdf->SetFont('Times', 'B', 7);
                    $pdf->TextWithDirection($box_x + 99 + 4, $box_y + 47, 'CONTENIDO', 'U');

                    // body
                    $pdf->Rect($box_x + 99 + 6, $box_y + 21, 84, 37);
                    $pdf->SetFont('Times', 'B', 5);
                    $pdf->SetXY($box_x + 99 + 7, $box_y + 22);

                    foreach ($guide->sku_product as $key => $product) {
                        $pdf->MultiCell(89,2,$product->sku_code.' - '.$product->sku_description,0,'J');
                        $pdf->SetX($box_x + 99 + 7);
                    }

                // cuadro 2.3 OBSERVACIONES
                    //header
                    // $pdf->Rect($box_x + 99, $box_y + 66, 6, 25);
                    $pdf->SetFont('Times', 'B', 6);
                    $pdf->SetXY($box_x + 92 + 7, $box_y + 59);
                    $pdf->Cell(26,4,'PRIMERA VISITA',1,0,'L');
                    $pdf->Cell(26,4,'SEGUNDA VISITA',1,0,'L');
                    $pdf->Cell(4,4,'1',1,0,'L');
                    $pdf->Cell(4,4,'2',1,0,'L');
                    $pdf->Cell(30,4,'MOTIVO DE REZAGO',1,1,'L');

                    $pdf->SetX($box_x + 92 + 7);
                    $pdf->Cell(26,4,'FECHA:',1,0,'L');
                    $pdf->Cell(26,4,'FECHA:',1,1,'L');
                    // $pdf->Cell(4,4,'',1,0,'L');
                    // $pdf->Cell(4,4,'',1,1,'L');

                    $pdf->SetX($box_x + 92 + 7);
                    $pdf->Cell(26,4,'HORA:',1,0,'L');
                    $pdf->Cell(26,4,'HORA:',1,1,'L');
                    // $pdf->Cell(4,4,'',1,0,'L');
                    // $pdf->Cell(4,4,'',1,1,'L');

                    $pdf->SetX($box_x + 92 + 7);
                    $pdf->Cell(26,4,'CODIGO:',1,0,'L');
                    $pdf->Cell(26,4,'CODIGO:',1,1,'L');
                    // $pdf->Cell(4,4,'',1,0,'L');
                    // $pdf->Cell(4,4,'',1,1,'L');

                    $pdf->SetX($box_x + 92 + 7);
                    $pdf->Cell(52,4,'OBSERVACIONES',1,1,'C');
                    
                    $pdf->SetX($box_x + 92 + 7);
                    $pdf->Cell(52,12,'',1,1,'C');

                    $pdf->SetXY($box_x + 144 + 7, $box_y + 63);
                    for ($zi=0; $zi < 6; $zi++) { 
                        $pdf->Cell(4,4,'',1,0,'L');
                        $pdf->Cell(4,4,'',1,0,'L');
                        $pdf->Cell(30,4,'TEST',1,1,'L');
                        $pdf->SetX($box_x + 144 + 7);
                    }
                    $pdf->Cell(4,4,'',1,0,'L');
                    $pdf->Cell(4,4,'',1,0,'L');
                    $pdf->Cell(30,4,'OTROS',1,1,'L');

                $box_y = 92 + $box_y + 2;
            }

            $disk = Storage::disk('public');
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
