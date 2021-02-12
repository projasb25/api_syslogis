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
            $data['id_bill_load_template'] = $req['id_bill_load_template'];

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
}
