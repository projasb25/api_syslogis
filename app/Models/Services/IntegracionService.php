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
            $guides = $this->repository->getGuides();
            Log::info('Proceso de integracion con ripley', ['nro_registros' => count($guides)]);
            foreach ($guides as $key => $guide) {
                if ($guide->Estado === 'CURSO') {
                    $guide->Estado = 'En Transito';
                    $guide->SubEstado = 'En Ruta hacia el Cliente';
                }
                $req_body = [
                    "CUD" => $guide->CUD,
                    "Estado" => ucwords(strtolower($guide->Estado)),
                    "SubEstado" => $guide->SubEstado,
                    "Placa" => $guide->Placa,
                    "Courier" => $guide->Courier,
                    "Fecha" => $guide->Fecha,
                    "NombreReceptor" => $guide->NombreReceptor,
                    "IDReceptor" => $guide->IDReceptor,
                    "TrackNumber" => $guide->TrackNumber,
                    "URL" => 'http://144.217.253.15:3000/guidestatus/'.$guide->id_guide
                ];

                $cliente = new Client(['base_uri' => env('RIPLEY_INTEGRACION_API_URL')]);
                
                try {
                    $req = $cliente->request('POST', 'sendStateCourierOnline', [
                        "headers" => [
                            'x-api-key' => '2ECPcJU2hs6PAEsvj9K8BapnSt3bPNkg9GQlNAoU',
                        ],
                        "json" => $req_body
                    ]);
                } catch (\GuzzleHttp\Exception\RequestException $e) {
                    $response = (array) json_decode($e->getResponse()->getBody()->getContents());
                    Log::error('Reportar estado a ripley, ', ['req' => $req_body, 'exception' => $response]);
                    $this->repository->LogInsert($guide->CUD, $guide->id_guide, 'ERROR', $req_body, $response);
                    continue;
                }

                $response = json_decode($req->getBody()->getContents());
                $this->repository->LogInsert($guide->CUD, $guide->id_guide, 'SUCCESS', $req_body, $response);
                $this->repository->updateReportado($guide->id_guide);
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
            foreach ($guides as $key => $guide) {
                $items = [];
                foreach ($guide->sku_product as $key => $product) {
                    array_push($items, [
                        'skuCode' => $product->sku_code,
                        'deliveredQuantity' => $product->sku_pieces
                    ]);
                }

                array_push($req_body, [
                    "companyCode" => "OE",
                    "dispatchNumber" => $guide->seg_code,
                    "items" => $items
                ]);
            }

            // $cliente = new Client(['base_uri' => env('OESCHLE_INTEGRACION_API_URL')]);
                
            // try {
            //     $req = $cliente->request('POST', 'provider/delivery', [
            //         "headers" => [
            //             'client_id' => env('OESCHLE_INTEGRACION_API_KEY'),
            //         ],
            //         "json" => $req_body
            //     ]);
            // } catch (\GuzzleHttp\Exception\RequestException $e) {
            //     $response = (array) json_decode($e->getResponse()->getBody()->getContents());
            //     Log::error('Reportar estado a Oechsle, ', ['req' => $req_body, 'exception' => $response]);
            //     $this->repository->LogInsertOechsle('ERROR', $req_body, $response);
            //     return $res;
            // }

            // $response = json_decode($req->getBody()->getContents());
            $this->repository->LogInsertOechsle('EXITO', $req_body, 'EXITO');
            $this->repository->updateReportadoOeschle($guides);

            $res['success'] = true;
            Log::info('Proceso de integracion con Oechsle exitoso', ['nro_registros' => count($guides)]);
        } catch (Exception $e) {
            Log::error('Integracion Oechsle', ['cliente' => 'Oechsle', 'exception' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();    
        }
        return $res;
    }
}