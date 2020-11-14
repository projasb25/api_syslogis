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

    public function __construct(IntegracionRepository $integracionRepository) {
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
                    "URL" => env('WEB_APP_URL') . 'guidestatus/'.$guide->id_guide
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
                    $this->repository->LogInsert($guide->CUD, $guide->id_guide, $guide->Estado, $guide->SubEstado, 'ERROR', $req_body, $response);
                    $this->repository->updateReportado($guide->id_guide, 2);
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
            
            $req_body = [];
            $g = '';
            foreach ($guides as $key => $guide) {
                $items = [];
                $g .= $guide->ids_guias . ',';
                $productos = explode("|||", $guide->contenido);
                foreach($productos as $producto) {    
                    $detalle = explode("///", $producto);
                    array_push($items, [
                        'skuCode' => $detalle[0],
                        'deliveredQuantity' => $detalle[1]
                    ]);
                }

                array_push($req_body, [
                    "companyCode" => "OE",
                    "dispatchNumber" => $guide->alt_code1,
                    "items" => $items
                ]);
                // $items = [];
                // foreach ($guide->sku_product as $key => $product) {
                //     array_push($items, [
                //         'skuCode' => $product->sku_code,
                //         'deliveredQuantity' => $product->sku_pieces
                //     ]);
                // }

                // array_push($req_body, [
                //     "companyCode" => "OE",
                //     "dispatchNumber" => $guide->seg_code,
                //     "items" => $items
                // ]);
            }

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
                    $this->repository->LogInsertOechsle('ERROR', $req_body, $response);
                    return $res;
                }
    
                $response = json_decode($req->getBody()->getContents());
                $this->repository->updateReportadoOeschle($guides);
            } else {
                $response = rtrim($guides, ',');
            }

            $this->repository->LogInsertOechsle('EXITO', $req_body, $response);

            $res['success'] = true;
            Log::info('Proceso de integracion con Oechsle exitoso', ['nro_registros' => count($guides)]);
        } catch (Exception $e) {
            Log::error('Integracion Oechsle', ['cliente' => 'Oechsle', 'exception' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();    
        }
        return $res;
    }
}