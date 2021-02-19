<?php

namespace App\Services\Web;

use App\Exceptions\CustomException;
use App\Helpers\ResponseHelper as Res;
use App\Repositories\Web\PurchaseOrderRepository;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class PurchaseOrderService
{
    protected $repo;
    public function __construct(PurchaseOrderRepository $repository) {
        $this->repo = $repository;
    }

    public function index($request)
    {
        try {
            $user = auth()->user();
            $req = $request->all();
            $user_data = $user->getIdentifierData();
            $data['count'] = count($req['data']);
            $data['username'] = $user->username;
            $data['data'] = $req['data'];
            $data['id_corporation'] = $user->id_corporation;
            $data['id_organization'] = $user_data['id_organization'];
            $data['id_client'] = $req['id_client'];
            $data['id_client_store'] = $req['id_client_store'];
            $data['id_load_template'] = $req['id_load_template'];
            $data['id_buyer'] = $req['id_buyer'];
            $data['purchase_order_number'] = $req+['purchase_order_number'];

            $id = $this->repo->insertPurchaseOrder($data);
            
            $res =[
                'id_purchase_order' => $id
            ];
            Log::info('Purchase Order Load Service exito', ['id_purchase_order' => $id, 'id_client' => $req['id_client'], 'client_store' => $req['id_client_store'], 'numero registros' => $data['count']]);

        } catch (CustomException $e) {
            Log::warning('Purchase Order Load Service error', ['expcetion' => $e->getData()[0], 'request' => $req]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Purchase Order Load Service Query', ['expcetion' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Purchase Order Load Service error', ['exception' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success($res);
    }
}
