<?php

namespace App\Models\Services\Integration;

use App\Exceptions\CustomException;
use App\Helpers\ResponseHelper as Res;
use App\Models\Repositories\Integration\MainRepository;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;

class MainService
{
    private $repo;

    public function __construct(MainRepository $mainRepository)
    {
        $this->repo = $mainRepository;
    }

    public function index($request)
    {
        try {
            $user = auth()->user();
            $request_data = $request->all();

            $insertar = $this->repo->insertData(json_encode($request_data), $user);

            Log::info('Integracion carga exito', ['id_carga' => $insertar, 'req' => $request->all()]);
        // } catch (CustomException $e) {
        //     Log::warning('Integracion carga error', ['expcetion' => $e->getData()[0], 'req' => $request->all()]);
        //     return Res::error($e->getData(), $e->getCode());
        // } catch (QueryException $e) {
        //     Log::warning('Integracion carga error', ['expcetion' => $e->getMessage(), 'req' => $request->all()]);
        //     return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Integracion carga error', ['exception' => $e->getMessage(), 'req' => $request->all()]);
            return response()->json([
                'codigo' => '3000',
                "tipoError" => "Connection Error",
                'mensaje'=> "Error en el proceso",
            ]);
        }
        
        return response()->json([
            'codigo' => '1',
            "tipoError" => "",
            'mensaje'=> "Se creó la guía correctamente",
            "numeroDeGuia" => $insertar
        ]);
    }
}
