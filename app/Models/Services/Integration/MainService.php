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
            $user = (object) [
                'id_integration_user' => 1,
                'id_corporation' => 15,
                'id_organization' => 53,
                'integration_user' => 'inretail'
            ];
            $request_data = $request->all();
            
            $insertar = $this->repo->insertData($request_data, $user);
            if (env('INRETAIL.FAKE')) {
                $response = json_decode('{
                    "Account": "1",
                    "OrderNumber": "12234-1",
                    "SellerName": "QAYARIX",
                    "GuideNumber": "WX334434",
                    "TrackingUrl": "urlseguimiento.com/web/WX334434"
                   }');
            } else {
                $req_body = [
                    "Account"=> $request_data['marketplaceId'],
                    "GuideNumber"=> $insertar,
                    "OrderNumber"=> $request_data['orderNumber'],
                    "SellerName"=> $request_data['sellerCorporateName'],
                    "TrackingUrl"=> ""
                ];

                $cliente = new Client(['base_uri' => env('INRETAIL.URL')]);

                $req = $cliente->request('POST', 'guide/create', [
                    "headers" => [
                        'client_id' => env('INRETAIL_API_CLIENT_ID'),
                    ],
                    "json" => $req_body
                ]);

                $response = json_decode($req->getBody()->getContents());
            }

            Log::info('Integracion carga exito', ['id_carga' => $insertar, 'resp' => (array) json_decode(json_encode($response), JSON_FORCE_OBJECT), 'req' => $request->all()]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('Integracion carga error', ['exception' => $e->getResponse()->getBody(true), 'req_body' => $req_body, 'req' => $request->all()]);
            return response()->json([
                'codigo' => '3000',
                "tipoError" => "Connection Error API",
                'mensaje'=> "Error en el proceso",
            ]);
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
}
