<?php

namespace App\Models\Services\Web;

use App\Exceptions\CustomException;
use App\Helpers\ResponseHelper as Res;
use App\Models\Repositories\Web\CollectRepository;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;

class CollectService
{
    private $repo;

    public function __construct(CollectRepository $collectRepository)
    {
        $this->repo = $collectRepository;
    }

    public function load($request)
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

            $id = $this->repo->insertCollectLoad($data);

            $res = [
                'id_massive_load' => $id
            ];
        } catch (CustomException $e) {
            Log::warning('Collect Load error', ['expcetion' => $e->getData()[0], 'request' => $req]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::error('Collect Load Query', ['expcetion' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::error('Collect Load error', ['exception' => $e->getMessage(), 'request' => $req]);
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
            $data['proc_integracion'] = $load->proc_integracion;

            $adresses = $this->repo->process($data);

            $propiedad = $this->repo->getPropiedad('apigmaps_call');
            if ($propiedad && $propiedad->value === '1') {
                $this->obtenerCoordenadas($adresses, $data['id_massive_load']);
            }
        } catch (CustomException $e) {
            Log::warning('Collect load procesar error', ['expcetion' => $e->getData()[0], 'request' => $req]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Collect load procesar Query', ['expcetion' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Collect load procesar error', ['exception' => $e->getMessage(), 'request' => $req]);
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
                        Log::warning('Obtener coordenadas nula de google.', ['direccion' => $direccion, 'response' => array($resp), 'url' => $url]);
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
}
