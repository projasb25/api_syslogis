<?php

namespace App\Models\Services;

use App\Exceptions\CustomException;
use App\Http\Requests\Pedido\grabarImagen;
use App\Helpers\ResponseHelper as Res;
use App\Models\Repositories\DriverRepository;
use App\Models\Repositories\PedidoDetalleRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class DriverService
{
    protected $repository;

    public function __construct(DriverRepository $driverRepository) {
        $this->repository = $driverRepository;
    }

    public function listarOfertas(Request $request)
    {
        try {
            $driver = auth()->user();
            $ordenes = $this->repository->getShippingOrders($driver->driverid);
            dd($ordenes);
            if ($ordenes->count()) {
    
                /* Si no hay ofertas aceptadas, disabled = false */
                $aceptadas = $ordenes->filter(function ($item) {
                    return $item->status === 'ACEPTADO';
                })->values();

                foreach ($ordenes as $key => $orden) {
                    if (!count($aceptadas)) {
                        $disabled = false;
                    } else {
                        $fecha_aceptada = Carbon::createFromTimeString($aceptadas[0]->date_created)->format('Y-m-d');
                        $fecha_orden = Carbon::createFromTimeString($orden->date_created)->format('Y-m-d');

                        $disabled = (Carbon::parse($fecha_aceptada)->diffInDays($fecha_orden) !== 0);
                    }
                    $orden->disabled = $disabled;
                }
            }

            Log::info('Listar Ofertas', ['id_driver' => $driver->id_dirver, 'ordenes' => (array) $ordenes]);
        } catch (CustomException $e) {
            Log::warning('Listar Ofertas', ['expcetion' => $e->getData()[0]]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Listar Ofertas', ['expcetion' => $e->getMessage()]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Listar Ofertas', ['exception' => $e->getMessage()]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success($ordenes);
    }

    public function actualizarEstado($request)
    {
        try {
            $driver = auth()->user();
            $estado = ($request->get('estado')) ? 'ACTIVO' : 'DESCONECTADO';
            $this->repository->actualizarEstado($estado, $driver->id_driver);
            
            Log::info('Actualizar estado', ['id_driver' => $driver->id_driver, 'estado' => $estado]);
        } catch (CustomException $e) {
            Log::warning('Actualizar Estado', ['expcetion' => $e->getData()[0]]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Actualizar Estado', ['expcetion' => $e->getMessage()]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Actualizar Estado', ['exception' => $e->getMessage()]);
            return Res::error(['Unxpected error', 3000], 400);
        }

        return Res::success('Estado actualizado');
    }
}
