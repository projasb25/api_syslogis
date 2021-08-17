<?php

namespace App\Models\Services;

use App\Exceptions\CustomException;
use App\Helpers\ResponseHelper as Res;
use App\Models\Repositories\OrderRepository;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderService
{
    protected $repository;

    public function __construct(OrderRepository $orderRepository) {
        $this->repository = $orderRepository;
    }

    public function massive_load(Request $request)
    {
        try {
            $user = auth()->user();
            $req = $request->all();
            $data['count'] = count($req['data']);
            $data['username'] = $user->username;
            $data['data'] = $req['data'];

            $id = $this->repository->insertMassiveLoad($data);
            $carga = $this->repository->process($id, $user->username, $user->id_user);
            $res = ['id_massive_load' => $id];
        } catch (CustomException $e) {
            Log::warning('Order Service Massive load error', ['expcetion' => $e->getData()[0], 'request' => $req]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Order Service Massive load Query', ['expcetion' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Order Service Massive load error', ['exception' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success($res);
    }

    public function insert($data)
    {
        try {
            

        } catch (CustomException $e) {
            Log::warning('Insert order error', ['expcetion' => $e->getData()[0], 'request' => $data]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Insert order query error', ['expcetion' => $e->getMessage(), 'request' => $data]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Insert order error', ['exception' => $e->getMessage(), 'request' => $data]);
            return Res::error(['Unxpected error', 3000], 400);
        }
    }

    public function getGeocode($place_id)
    {
        $res['success'] = false;
        $res['error'] = '';
        try {
            $client = new Client(['base_uri' => env('GOOGLEAPIS_GEOCODE_URL')]);
            $url = "json?place_id=" . $place_id . "&components=country:PE&key=" . env('GOOGLEAPIS_GEOCODE_KEY');

            $req = $client->request('GET', $url);
            $resp = json_decode($req->getBody()->getContents());
            if (empty($resp->result)) {
                throw new CustomException(['Direccion no encontrada',2000]);
            }

            $components = $resp->result->address_components;
            $administrative_area_level_1 = array_filter($components, function($e) {
                if (in_array('administrative_area_level_1', $e->types)) {
                    return $e;
                }
            });

            $administrative_area_level_2 = array_filter($components, function($e) {
                if (in_array('administrative_area_level_2', $e->types)) {
                    return $e;
                }   
            });

            $locality = array_filter($components, function($e){
                if (in_array('locality', $e->types)) {
                    return $e;
                }   
            });

            $departamento = ($administrative_area_level_1) ? array_values($administrative_area_level_1)[0]->long_name: '';
            $provincia = ($administrative_area_level_2) ? array_values($administrative_area_level_2)[0]->long_name: '';
            $distrito = ($locality) ? array_values($locality)[0]->long_name: '';

            $res['data'] = [
                'departamento' => $departamento,
                'provincia' => $provincia,
                'distrito' => $distrito
            ];
        } catch (CustomException $e) {
            $res['error'] = $e->getData()[0];
        } catch (Exception $e) {
            $res['error'] = $e->getMessage();
        }
        return $res;
    }
}