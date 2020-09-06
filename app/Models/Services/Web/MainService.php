<?php

namespace App\Models\Services\Web;

use App\Exceptions\CustomException;
use App\Models\Functions\FunctionModel;
use App\Helpers\ResponseHelper as Res;
use App\Models\Repositories\Web\MainRepository;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MainService
{
    protected $functions;
    protected $repository;

    public function __construct(FunctionModel $functionModel, MainRepository $mainRepository)
    {
        $this->functions = $functionModel;
        $this->repository = $mainRepository;
    }

    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $req = $request->all();
            $req['data'] = array_merge($req['data'],$user->makeHidden('status')->toArray());
            $fun = $this->functions->getFunctions();
            if (!array_key_exists($req['method'], $fun)) {
                throw new CustomException(['metodo no existe.', 2100], 400);
            }

            $query = $fun[$req['method']]['query'];
            $params = $fun[$req['method']]['params'];
            $bindings = [];

            if (count($params)) {
                foreach ($params as $key => $value) {
                    if (isset($req['data'][$value])) {
                        $bindings[$value] = $req['data'][$value];
                    }
                }
                if (!count($bindings) || (count($bindings) < count($params))) {
                    throw new CustomException(['parametros incorrectos.', 2100], 400);
                }
            }
            $data = $this->repository->execute_store($query, $bindings);
        } catch (CustomException $e) {
            Log::warning('Main Service error', ['expcetion' => $e->getData()[0], 'request' => $req]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            if ((int) $e->getCode() >= 60000 ) {
                Log::warning('Main Service Query error', ['expcetion' => $e->errorInfo[2], 'request' => $req]);
                return Res::error([$e->errorInfo[2], 3000], 400);
            }
            Log::warning('Main Service error', ['expcetion' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Main Service error', ['exception' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success($data);
    }
}
