<?php

namespace App\Models\Services\Web;

use Exception;
use Log;

use App\Exceptions\CustomException;
use App\Helpers\ResponseHelper as Res;
use App\Models\Repositories\Web\MassiveLoadRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MassiveLoadService
{
    protected $repo;
    public function __construct(MassiveLoadRepository $repository)
    {
        $this->repo = $repository;
    }

    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $req = $request->all();
            $data['count'] = count($req['data']);
            $data['username'] = $user->username;
            $data['data'] = $req['data'];
            $data['id_corporation'] = $req['id_corporation'];
            $data['id_organization'] = $req['id_organization'];

            $id = $this->repo->insertMassiveLoad($data);
            
            $res =[
                'id_massive_load' => $id
            ];
        } catch (CustomException $e) {
            Log::warning('Massive Load Service error', ['expcetion' => $e->getData()[0], 'request' => $req]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Massive Load Service Query', ['expcetion' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Massive Load Service error', ['exception' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success($res);
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

            $this->repo->process($data);
            
        } catch (CustomException $e) {
            Log::warning('Massive Load Service error', ['expcetion' => $e->getData()[0], 'request' => $req]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Massive Load Service Query', ['expcetion' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Massive Load Service error', ['exception' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success('Exito');
    }
}
