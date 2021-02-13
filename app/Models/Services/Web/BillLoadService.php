<?php

namespace App\Models\Services\Web;

use App\Exceptions\CustomException;
use App\Models\Repositories\Web\BillLoadRepository;
use App\Helpers\ResponseHelper as Res;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class BillLoadService
{
    protected $repo;
    public function __construct(BillLoadRepository $repository)
    {
        $this->repo = $repository;
    }

    public function index($request)
    {
        try {
            $user = auth()->user();
            $req = $request->all();
            $data['count'] = count($req['data']);
            $data['username'] = $user->username;
            $data['data'] = $req['data'];
            $user_data = $user->getIdentifierData();
            $data['data'] = $req['data'];
            $data['id_corporation'] = $user->id_corporation;
            $data['id_organization'] = $user_data['id_organization'];
            $data['id_client'] = $req['id_client'];
            $data['id_client_store'] = $req['id_client_store'];
            $data['id_load_template'] = $req['id_load_template'];

            $id = $this->repo->insertBillLoad($data);
            
            $res =[
                'id_bill_load' => $id
            ];
            Log::info('Bill Load Service exito', ['id_bill_load' => $id, 'id_client' => $req['id_client'], 'client_store' => $req['id_client_store'], 'numero registros' => $data['count']]);
        } catch (CustomException $e) {
            Log::warning('Bill Load Service error', ['expcetion' => $e->getData()[0], 'request' => $req]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Bill Load Service Query', ['expcetion' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Bill Load Service error', ['exception' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success($res);
    }

    public function process($request)
    {
        $res = [];
        try {
            $user = auth()->user();
            $req = $request->all();
            $data['username'] = $user->username;
            $data['data'] = $req['data'];
            $data['id_bill_load'] = $req['id_bill_load'];
            $req_detalle = $req['data'];
            
            $load = $this->repo->get($req['id_bill_load']);
            if (!$load) {
                throw new CustomException(['Registro no encontrado.', 2121], 404);
            }
            if ($load->status !== "PENDIENTE") {
                throw new CustomException(['La carga ya fue procesada.', 2120], 400);
            }
            $data['id_corporation'] = $load->id_corporation;
            $data['id_organization'] = $load->id_organization;

            $detalle = $this->repo->getDetail($req['id_bill_load']);
            foreach ($detalle as &$value) {
                $key = array_search($value->id_bill_load_detail, array_column($req_detalle, 'id_bill_load_detail'));
                if (is_null($key)) {
                    throw new CustomException(['Data invalida, numero de registros no coinciden.', 2020], 400);
                }
                $value->shrinkage = $req_detalle[$key]['shrinkage'];
                $value->quarantine = $req_detalle[$key]['quarantine'];
                $value->hallway = $req_detalle[$key]['hallway'];
                $value->level = $req_detalle[$key]['level'];
                $value->column = $req_detalle[$key]['column'];
            }
           
            $data['detalle'] = $detalle;
            dd($data);
            $adresses = $this->repo->process($data);

            $propiedad = $this->repo->getPropiedad('apigmaps_call');
            if ($propiedad && $propiedad->value === '1') {
                $this->obtenerCoordenadas($adresses, $data['id_massive_load']);
            }

            Log::info('Bill Load procecss exito', ['id_bill_load' => $request->get('id_bill_load'), 'num_registros' => count($request->get('data'))]);
        } catch (CustomException $e) {
            Log::warning('Bill Load process error', ['exception' => $e->getMessage(), 'request' => $request->all()]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Bill Load process error', ['exception' => $e->$e->getData()[0], 'request' => $request->all()]);
            return Res::error(['Error de conexion', 3000], 500);
        } catch (Exception $e) {
            Log::warning('Bill Load process error', ['exception' => $e->getMessage(), 'request' => $request->all()]);
            return Res::error(['Error de conexion', 3000], 500);
        }
        return Res::success($res);
    }
}
