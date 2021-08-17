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
            $user = auth()->user();
            $header = $data['header']['data'];
            $detail = $data['details']['data'];

            $pickup_geo = $this->getGeocode($header['order_pickup_place_id']);
            if (!$pickup_geo['success']) {
                throw new CustomException([$pickup_geo['error'] ,400]);
            }

            $delivery_geo = $this->getGeocode($header['order_delivery_place_id']);
            if (!$delivery_geo['success']) {
                throw new CustomException([$delivery_geo['error'] ,400]);
            }

            $header['order_pickup_lat'] = $pickup_geo['data']['latitude'];
            $header['order_pickup_lng'] = $pickup_geo['data']['longitude'];
            $header['order_pickup_district'] = $pickup_geo['data']['distrito'];

            $header['order_delivery_lat'] = $delivery_geo['data']['latitude'];
            $header['order_delivery_lng'] = $delivery_geo['data']['longitude'];
            $header['order_delivery_district'] = $delivery_geo['data']['distrito'];

            $data['header'] = json_encode($header);
            $data['details'] = json_encode($detail);
            $user_data = json_encode($user->getIdentifierData());
            $id = $this->repository->createOrder($header, $detail, $user_data);
        } catch (CustomException $e) {
            Log::warning('Insert order error', ['expcetion' => $e->getData()[0], 'request' => $data]);
            dd($e);
            return Res::error( $e->getData()[0], $e->getCode());
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
            $geometry = $resp->result->geometry;
            // $administrative_area_level_1 = array_filter($components, function($e) {
            //     if (in_array('administrative_area_level_1', $e->types)) {
            //         return $e;
            //     }
            // });

            // $administrative_area_level_2 = array_filter($components, function($e) {
            //     if (in_array('administrative_area_level_2', $e->types)) {
            //         return $e;
            //     }   
            // });

            $locality = array_filter($components, function($e){
                if (in_array('locality', $e->types)) {
                    return $e;
                }
            });



            // $departamento = ($administrative_area_level_1) ? array_values($administrative_area_level_1)[0]->long_name: '';
            // $provincia = ($administrative_area_level_2) ? array_values($administrative_area_level_2)[0]->long_name: '';
            $distrito = ($locality) ? array_values($locality)[0]->long_name: '';

            $res['data'] = [
                'distrito' => $distrito,
                'latitude' => $geometry->location->lat,
                'longitude' => $geometry->location->lng
            ];
            $res['success'] = true;
        } catch (CustomException $e) {
            $res['error'] = $e->getData()[0];
        } catch (Exception $e) {
            $res['error'] = $e->getMessage();
        }
        return $res;
    }
}
