<?php

namespace App\Models\Services;

use App\Exceptions\CustomException;
use App\Models\Repositories\Web\ShippingRepository;
use Exception;
use Illuminate\Database\QueryException;
use App\Helpers\ResponseHelper as Res;
use Illuminate\Support\Facades\Log;

class ShippingService
{
    protected $repository;

    public function __construct(ShippingRepository $shippingRepository)
    {
        $this->repository = $shippingRepository;
    }

    public function aceptarOferta($request)
    {
        try {
            $orden = $this->repository->getShippingOrder($request->idofertaenvio);
            if (!$orden) {
                throw new CustomException(['Oferta no encontrada.', 2001], 404);
            } elseif ($orden->status !== 'PENDIENTE') {
                throw new CustomException(['La oferta se cancelo u otro conductor ya la acepto', 2002], 400);
            }
            $this->repository->aceptarEnvio($orden->id_shipping_order);
            Log::info('Aceptar oferta exitoso', ['orden' => (array) $orden, 'estado' => 'ACEPTADO']);
        } catch (CustomException $e) {
            Log::warning('Aceptar oferta', ['expcetion' => $e->getData()[0]]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Aceptar oferta', ['expcetion' => $e->getMessage()]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Aceptar oferta', ['exception' => $e->getMessage()]);
            return Res::error(['Unxpected error', 3000], 400);
        }

        return Res::success(['mensaje' => 'Estado actualizado']);
    }

    public function rechazarOferta($request)
    {
        try {
            $orden = $this->repository->getShippingOrder($request->idofertaenvio);
            if (!$orden) {
                throw new CustomException(['Oferta no encontrada.', 2001], 404);
            } elseif ($orden->status !== 'PENDIENTE') {
                throw new CustomException(['La oferta se cancelo u otro conductor ya la acepto', 2002], 400);
            }
            $this->repository->rechazarEnvio($orden->id_shipping_order);
            Log::info('Rechazar oferta exitoso', ['orden' => (array) $orden, 'estado' => 'ACEPTADO']);
        } catch (CustomException $e) {
            Log::warning('Rechazar oferta', ['expcetion' => $e->getData()[0]]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Rechazar oferta', ['expcetion' => $e->getMessage()]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Rechazar oferta', ['exception' => $e->getMessage()]);
            return Res::error(['Unxpected error', 3000], 400);
        }

        return Res::success(['mensaje' => 'Estado actualizado']);
    }

    public function listarRutas($request)
    {
        try {
            $rutas = $this->repository->listarRutas($request->idofertaenvio);
            if (!count($rutas)) {
                throw new CustomException(['No existen rutas ascociadas a este id.', 2007], 404);
            }
            Log::info('Listar Rutas', ['id_shipping_order' => $request->idofertaenvio, 'nro_registros' => count($rutas)]);
        } catch (CustomException $e) {
            Log::warning('Listar Rutas', ['expcetion' => $e->getData()[0], 'id_shipping_order' => $request->idofertaenvio]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Listar Rutas', ['expcetion' => $e->getMessage(), 'id_shipping_order' => $request->idofertaenvio]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Listar Rutas', ['exception' => $e->getMessage(), 'id_shipping_order' => $request->idofertaenvio]);
            return Res::error(['Unxpected error', 3000], 400);
        }

        return Res::success($rutas);
    }

    public function iniciarRuta($request)
    {
        try {
            $orden = $this->repository->getShippingOrder($request->idofertaenvio);
            if (!$orden) {
                throw new CustomException(['Envio no encontrado.', 2001], 404);
            } elseif ($orden->status !== 'ACEPTADO') {
                throw new CustomException(['Envio ya esta iniciado o fue cancelado.', 2002], 400);
            }

            $this->repository->iniciarRuta($request->idofertaenvio);
            Log::info('Iniciar Ruta', ['id_shipping_order' => $request->idofertaenvio]);
        } catch (CustomException $e) {
            Log::warning('Iniciar Ruta', ['expcetion' => $e->getData()[0], 'id_shipping_order' => $request->idofertaenvio]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Iniciar Ruta', ['expcetion' => $e->getMessage(), 'id_shipping_order' => $request->idofertaenvio]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Iniciar Ruta', ['exception' => $e->getMessage(), 'id_shipping_order' => $request->idofertaenvio]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success(['mensaje' => 'Envio iniciado correctamente.']);
    }

    public function motivos()
    {
        try {
            $motivos = $this->repository->getMotivos();
            $data = [];

            foreach ($motivos as $key => $motivo) {
                array_push($data, $motivo->name);
            }
            Log::info('Listar Motivos exitoso');
        } catch (CustomException $e) {
            Log::warning('Listar Motivos', ['expcetion' => $e->getData()[0]]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Listar Motivos', ['expcetion' => $e->getMessage()]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Listar Motivos', ['exception' => $e->getMessage()]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success($data);
    }
}
