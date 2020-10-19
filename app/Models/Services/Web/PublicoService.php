<?php

namespace App\Models\Services\Web;

use App\Exceptions\CustomException;
use App\Models\Repositories\Web\PublicoRepository;
use Exception;
use Illuminate\Database\QueryException;
use App\Helpers\ResponseHelper as Res;
use Log;

class PublicoService
{
    protected $repository;
    public function __construct(PublicoRepository $publicoRepository) {
        $this->repository = $publicoRepository;
    }

    public function guide_status($request)
    {
        try {
            $data = $this->repository->guide_status($request->id_guide);

            Log::info('Obtener Guia status', ['data' => (array) $data]);
        } catch (CustomException $e) {
            Log::warning('Obtener Guia status', ['expcetion' => $e->getData()[0], 'id_guide' => $request->id_guide]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Obtener Guia status', ['expcetion' => $e->getMessage(), 'id_guide' => $request->id_guide]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Obtener Guia status', ['exception' => $e->getMessage(), 'id_guide' => $request->id_guide]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success($data);
    }
}
