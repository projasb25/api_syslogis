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
                    "URL" => 'URL'
                ];

                // $cliente = new Client(['base_uri' => env('RIPLEY_INTEGRACION_API_URL')]);
                
                // try {
                //     $req = $cliente->request('POST', 'sendStateCourierOnline', [
                //         "headers" => [
                //             'Authorization' => 'Api-key-Ripley',
                //         ],
                //         "json" => $req_body
                //     ]);
                // } catch (\GuzzleHttp\Exception\RequestException $e) {
                //     $response = (array) json_decode($e->getResponse()->getBody()->getContents());
                //     Log::error('Reportar estado a ripley, ', ['req' => $req_body, 'exception' => $response]);
                //     $this->repository->LogInsert($guide->CUD, $guide->id_guide, 'ERROR', $req_body, $response);
                //     continue;
                // }


                print_r($req_body);


            }
            
            $res['success'] = true;
        } catch (Exception $e) {
            dd($e);
            Log::error('Integracion ripley', ['cliente' => 'Ripley', 'exception' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();    
        }

        return $res;
    }
}