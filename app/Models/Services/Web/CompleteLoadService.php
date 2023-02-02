<?php

namespace App\Models\Services\Web;

use Exception;
// use Log;

use App\Exceptions\CustomException;
use App\Helpers\ArrayHelper;
use App\Helpers\ResponseHelper as Res;
use App\Models\Repositories\Web\CompleteLoadRepository;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CompleteLoadService
{
    protected $repo;
    public function __construct(CompleteLoadRepository $repository)
    {
        $this->repo = $repository;
    }

    public function load($request)
    {
        $res = [];
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

            $id = $this->repo->insertCompleteLoad($data);

            $res =[
                'id_massive_load' => $id
            ];

        } catch (CustomException $e) {
            Log::warning('[NEW] Complete massive load error', ['expcetion' => $e->getData()[0], 'request' => $req]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('[NEW] Complete massive load error', ['expcetion' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::error('[NEW] Complete massive load error', ['expcetion' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success($res);
    }

    public function process_load($request)
    {
        $res = [];
        try {
            $user = auth()->user();
            $req = $request->all();
            $data['username'] = $user->username;
            $data['id_complete_load'] = $req['id_complete_load'];

            $load = $this->repo->getCompleteLoad($data['id_complete_load']);
            if (!$load) {
                throw new CustomException(['Registro no encontrado.', 2121], 404);
            }
            if ($load->status !== "PENDIENTE") {
                throw new CustomException(['La carga masiva ya fue procesada.', 2120], 400);
            }

            $data = $this->repo->selDataCargaPorId($data['id_complete_load']);
            $id = $this->repo->insertCompleteCollectLoad($data);

            $res =[ 'id_massive_load' => $id ];
            Log::info('[RECOLECCION] Procesar carga completa exitoso', ['carga_id' => $id]);
        } catch (CustomException $e) {
            Log::warning('[RECOLECCION] Procesar carga completa error', ['expcetion' => $e->getData()[0], 'request' => $req]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('[RECOLECCION] Procesar carga completa error', ['expcetion' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::error('[RECOLECCION] Procesar carga completa error', ['expcetion' => $e->getMessage(), 'request' => $req]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success($res);
    }

    public function procesarRecoleccion()
    {
        $res['success'] = false;
        try {
            $cargas_organizacion = $this->repo->selCargasRecoleccion();
            if (!count($cargas_organizacion)) {
                Log::info('[RECOLECCION] Procesar carga masiva completa: nada que reportar');
                $res['success'] = true;
                return $res;
            }

            foreach ($cargas_organizacion as $key => $organizacion) {
                $cargas_data = $this->repo->selDataCargaRecoleccion($organizacion->id_organization);
                $carga_id = $this->repo->insertCompleteCollectLoad($cargas_data);
                Log::info('[RECOLECCION] Procesar carga completa exitoso', ['carga_id' => $carga_id]);
            }

            $res['success'] = true; 
        } catch (CustomException $e) {
            Log::warning('[RECOLECCION] Procesar carga masiva completa error', ['expcetion' => $e->getData()[0]]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('[RECOLECCION] Procesar carga masiva completa error', ['expcetion' => $e->getMessage()]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::error('[RECOLECCION] Procesar carga masiva completa error', ['expcetion' => $e->getMessage()]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return $res;
    }

    public function process_distribution()
    {
        $res['success'] = false;
        try {
            $cargas_organizacion = $this->repo->selRecolectadoOrganizacion();
            error_log(json_encode($cargas_organizacion));
            if (!count($cargas_organizacion)) {
                Log::info('[DISTRIBUCION] Procesar carga masiva completa: nada que reportar');
                $res['success'] = true;
                return $res;
            }

            foreach ($cargas_organizacion as $key => $organizacion) {
                $cargas_data = $this->repo->selDataCargaDistribucion($organizacion->id_organization);
                $carga_id = $this->repo->insertarCargaDistribucion($cargas_data);
                Log::info('[DISTRIBUCION] Procesar carga completa exitoso', ['carga_id' => $carga_id]);
            }
            die();

            $res['success'] = true; 
        } catch (CustomException $e) {
            Log::warning('[DISTRIBUCION] Procesar carga masiva completa error', ['expcetion' => $e->getData()[0]]);
            $res['mensaje'] = $e->getData()[0];
        } catch (QueryException $e) {
            Log::warning('[DISTRIBUCION] Procesar carga masiva completa error', ['expcetion' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();
        } catch (Exception $e) {
            Log::error('[DISTRIBUCION] Procesar carga masiva completa error', ['expcetion' => $e->getMessage()]);
            $res['mensaje'] = $e->getMessage();
        }
        return $res;
    }
}
