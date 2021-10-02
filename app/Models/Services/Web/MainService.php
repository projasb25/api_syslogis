<?php

namespace App\Models\Services\Web;

use App\Exceptions\CustomException;
use App\Helpers\QueryHelper;
use App\Models\Functions\FunctionModel;
use App\Helpers\ResponseHelper as Res;
use App\Models\Repositories\Web\MainRepository;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
            // $req['data'] = array_merge($req['data'], $user->getIdentifierData());
            $fields = array_merge($req['data'], $user->getIdentifierData());
            $fun = $this->functions->getFunctions();
            if (!array_key_exists($req['method'], $fun)) {
                throw new CustomException(['metodo no existe.', 2100], 400);
            }

            $query = $fun[$req['method']]['query'];
            $params = $fun[$req['method']]['params'];
            $bindings = [];

             // Si existe password se hashea
             if (array_key_exists('password', $fields) && !empty($fields['password'])) {
                $fields['password'] = Hash::make($fields['password']);
            }

            // Si existe un id_user en params, no usar el de la sesion
            if (in_array('id_user', $params) && array_key_exists('id_user', $fields)) {
                $fields['id_user'] = $req['data']['id_user'];
            }

            dd($fields);
            if (count($params)) {
                foreach ($params as $key => $value) {
                    if (array_key_exists($value, $fields)) {
                        $bindings[$value] = $fields[$value];
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
            if ((int) $e->getCode() >= 60000) {
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

    public function simpleTransaction(Request $request)
    {
        try {
            $user = auth()->user();
            $req = $request->all();
            $header = $req['header'];
            $details = $req['details'];
            $fun = $this->functions->getFunctions();
            $missing_param = [];
            if (!array_key_exists($req['method'], $fun)) {
                throw new CustomException(['metodo no existe.', 2100], 400);
            }

            $query = $fun[$req['method']]['query'];
            $headerParams = $fun[$req['method']]['headers_params'];
            $detailsParams = $fun[$req['method']]['details_params'];
            $bindings = [];

            // Si existe password se hashea
            if (array_key_exists('password', $header['data']) && !empty($header['data']['password'])) {
                $header['data']['password'] = Hash::make($header['data']['password']);
            }

            foreach ($headerParams as $key => $param) {
                if (array_key_exists($param, $header['data'])) {
                    $bindings[$param] = $header['data'][$param];
                } else {
                    array_push($missing_param, $param);
                }
            }
            if (count($bindings) < count($headerParams)) {
                throw new CustomException(['parametros incorrectos.', 2100], 400);
            }
            $bindings = [];
            foreach ($details['data'] as $key => $detail) {
                foreach ($detailsParams as $key => $param) {
                    if (array_key_exists($param, $detail)) {
                        $bindings[$param] = $detail[$param];
                    } else {
                        array_push($missing_param, $param);
                    }
                }

                if (count($bindings) < count($detailsParams)) {
                    throw new CustomException(['parametros incorrectos', 2100], 400);
                }
                $bindings = [];
            }

            $data['header'] = json_encode($header['data']);
            $data['details'] = json_encode($req['details']['data']);
            $data['username'] = json_encode($user->getIdentifierData());
            Log::info('data', ['data' => $data]);
            $data = $this->repository->execute_store($query, $data);
            Log::info('debug ', ['data' => $data]);
        } catch (CustomException $e) {
            Log::warning('Main Service Transaction error', ['expcetion' => $e->getData()[0], 'request' => $req, 'missing_params' => $missing_param]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            if ((int) $e->getCode() >= 60000) {
                Log::warning('Main Service Transaction Query error', ['expcetion' => $e->errorInfo[2], 'request' => $req]);
                return Res::error([$e->errorInfo[2], (int) $e->getCode()], 400);
            }
            Log::warning('Main Service Transaction error', ['expcetion' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Main Service Transaction error', ['exception' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success('Exito');
    }

    public function paginated($request)
    {
        $data= [];
        try {
            $filtros = $request->get('filters');
            $origen = $request->get('origin');
            $daterange = $request->get('daterange');
            $skip = $request->get('skip');
            $take = $request->get('take');
            $where = QueryHelper::generarFiltro($filtros, $origen, $daterange);

            $user = auth()->user();
            $user_data= json_encode($user->getIdentifierData());
            $req = $request->all();
            // $req['data'] = array_merge($req['data'], $user->getIdentifierData());
            $fun = $this->functions->getFunctions();
            if (!isset($fun[$req['methodcollection']]) || !isset($fun[$req['methodcount']]) ) {
                throw new CustomException(['metodo no existe.', 2100], 400);
            }

            $query_collection = $fun[$req['methodcollection']]['query'];
            $query_count = $fun[$req['methodcount']]['query'];

            $data['collection'] = $this->repository->execute_store($query_collection, [$where, $take, $skip,$user_data]);
            $data['count'] = $this->repository->execute_store($query_count, [$where,$user_data])[0]->count;
        } catch (CustomException $e) {
            Log::warning('Main Service paginated error', ['expcetion' => $e->getData()[0], 'request' => $request->all()]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            if ((int) $e->getCode() >= 60000) {
                Log::warning('Main Service paginated Query error', ['expcetion' => $e->errorInfo[2], 'request' => $request->all()]);
                return Res::error([$e->errorInfo[2], 3000], 400);
            }
            Log::warning('Main Service paginated error', ['expcetion' => $e->getMessage(), 'request' => $request->all()]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Main Service paginated error', ['exception' => $e->getMessage(), 'request' => $request->all()]);
            return Res::error(['Unxpected error', 3000], 400);
        }

        return Res::success($data);
    }
}
