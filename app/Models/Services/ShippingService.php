<?php

namespace App\Models\Services;

use App\Exceptions\CustomException;
use App\Models\Repositories\Web\ShippingRepository;
use Exception;
use Illuminate\Database\QueryException;
use App\Helpers\ResponseHelper as Res;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

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

    public function motivos($request)
    {
        try {
            $motivos = $this->repository->getMotivos($request->tipo);
            $data = [];

            foreach ($motivos as $key => $motivo) {
                array_push($data, $motivo->name);
            }
            Log::info('Listar Motivos exitoso', ['motivos' => $data]);
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

    public function motivosDist($request)
    {
        try {
            $motivos = $this->repository->getMotivos('distribucion');
            $data = [];

            foreach ($motivos as $key => $motivo) {
                array_push($data, $motivo->name);
            }
            Log::info('Listar Motivos exitoso', ['motivos' => $data]);
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

    public function grabarImagen($request)
    {
        try {
            $guide = $this->repository->getShippingDetailByGuideNumber($request->get('guide_number'), $request->get('id_shipping_order'));
            if (!count($guide)) {
                throw new CustomException(['Detalle no encontrado.', 2010], 400);
            }
            // elseif ($guide[0]->status !== 'CURSO') {
            //     throw new CustomException(['La guia no se encuentra en Curso.', 2011], 400);
            // }
            $destination_path = Storage::disk('imagenes')->getAdapter()->getPathPrefix() . $guide[0]->id_guide;
            # CHeck if folder exists before create one
            if (!file_exists($destination_path)) {
                File::makeDirectory($destination_path, $mode = 0777, true, true);
                File::makeDirectory($destination_path . '/thumbnail', $mode = 0777, true, true);
            }

            $imagen = $request->file('imagen');
            $nombre_imagen = $guide[0]->id_guide . '_' . time() . '.jpg';
            $thumbnail = Image::make($imagen->getRealPath());

            # Guardamos el thumnail primero
            $thumbnail->resize(250, 250, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destination_path . '/thumbnail/' . $nombre_imagen);

            # Redimesionamos la imagen a 720x720
            $resize = Image::make($imagen->getRealPath());
            $resize->resize(720, 720, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destination_path . '/' . $nombre_imagen);

            $ruta = url('storage/imagenes2/' . $guide[0]->id_guide . '/' . $nombre_imagen);
            foreach ($guide as $key => $gd) {
                $this->repository->insertarImagen($gd->id_guide, $gd->id_shipping_order, $ruta, $request->get('descripcion'), $request->get('tipo_imagen'));
            }

            Log::info('Grabar imagen exitoso', ['request' => $request->except('imagen'), 'nombre_imagen' => $ruta]);
        } catch (CustomException $e) {
            Log::warning('Grabar imagen', ['expcetion' => $e->getData()[0], 'request' => $request->except('imagen')]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Grabar imagen', ['expcetion' => $e->getMessage(), 'request' => $request->except('imagen')]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Grabar imagen', ['exception' => $e->getMessage(), 'request' => $request->except('imagen')]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success(['mensaje' => 'Imagen guardada con exito']);
    }

    public function getImagen($request)
    {
        try {
            $guide = $this->repository->getShippingDetailByGuideNumber($request->guide_number, $request->id_shipping_order);
            if (!count($guide)) {
                throw new CustomException(['Detalle no encontrado.', 2010], 400);
            }

            $imagenes = $this->repository->obtenerImagenes($guide[0]->id_guide, $guide[0]->id_shipping_order);
            $data = [];

            foreach ($imagenes as $key => $img) {
                $index = strrpos($img->url,"/");
                $thumb = substr($img->url,0,$index) . '/thumbnail' . substr($img->url, $index);
                array_push($data, $thumb);
            }
            Log::info("Obtener imagen exitoso", [
                'id_shipping_order' => $request->id_shipping_order,
                'guide_number' => $request->guide_number,
                'nro_imagenes' => $imagenes->count()
            ]);
        } catch (CustomException $e) {
            Log::warning('Obtener Imagen', [
                'expcetion' => $e->getData()[0],
                'guide_number' => $request->guide_number,
                'id_shipping_order' => $request->id_shipping_order
            ]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Obtener Imagen', [
                'expcetion' => $e->getMessage(),
                'guide_number' => $request->guide_number,
                'id_shipping_order' => $request->id_shipping_order
            ]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Obtener Imagen', [
                'exception' => $e->getMessage(),
                'guide_number' => $request->guide_number,
                'id_shipping_order' => $request->id_shipping_order
            ]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success($data);
    }

    public function actualizarPedido($request)
    {
        try {
            $guias = [];
            $data = $request->all();
            $pedido = $this->repository->getShippingDetailByGuideNumber($request->get('guide_number'), $request->get('id_shipping_order'));
            if (!count($pedido)) {
                throw new CustomException(['Pedido no encontrado.', 2012], 400);
            } elseif ($pedido[0]->status !== 'CURSO') {
                throw new CustomException(['El pedido no se encuentra en Curso.', 2013], 400);
            }

            foreach ($pedido as $key => $pd) {
                array_push($guias, [
                    'id_shipping_order_detail' => $pd->id_shipping_order_detail,
                    'estado' => $data['estado'],
                    'observacion' => $data['observacion'],
                    'latitud' => $data['latitud'],
                    'longitud' => $data['longitud']
                    ]);
            }

            $this->repository->actualizarPedido($guias);

            Log::info('Actualización pedido exitoso', ['request' => $data]);
        } catch (CustomException $e) {
            Log::warning('Actualizar pedido', ['expcetion' => $e->getData()[0], 'id_shipping_order_detail' => $request['id_shipping_order_detail']]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Actualizar pedido', ['expcetion' => $e->getMessage(), 'id_shipping_order_detail' => $request['id_shipping_order_detail']]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Actualizar pedido', ['exception' => $e->getMessage(), 'id_shipping_order_detail' => $request['id_shipping_order_detail']]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success([
            'mensaje' => 'Pedido actualizado con éxito',
            'finalizado' => false
        ]);
    }

    public function finalizarRuta($request)
    {
        try {
            $orden = $this->repository->getShippingOrder($request->id_shipping_order);
            if (!$orden) {
                throw new CustomException(['Envio no encontrado.', 2001], 404);
            } elseif ($orden->status !== 'CURSO') {
                throw new CustomException(['Envio ya esta finalizado o fue cancelado.', 2002], 400);
            }

            $this->repository->finalizarRuta($request->id_shipping_order);
            Log::info('Finalizar ruta exitoso', ['id_shipping_order' => $request->id_shipping_order]);
        } catch (CustomException $e) {
            Log::warning('finalizar Ruta', ['expcetion' => $e->getData()[0], 'id_shipping_order' => $request->id_shipping_order]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('finalizar Ruta', ['expcetion' => $e->getMessage(), 'id_shipping_order' => $request->id_shipping_order]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('finalizar Ruta', ['exception' => $e->getMessage(), 'id_shipping_order' => $request->id_shipping_order]);
            return Res::error(['Unxpected error', 3000], 400);
        }

        return Res::success(['mensaje' => 'Envio finalizado correctamente.']);
    }

    public function novedad_insertar($request)
    {
        try {
            $user = auth()->user();
            $guias = [];
            $data = $request->all();
            $pedido = $this->repository->getShippingDetailByGuideNumber($request->get('guide_number'), $request->get('id_shipping_order'));
            if (!count($pedido)) {
                throw new CustomException(['Pedido no encontrado.', 2012], 400);
            }

            $this->repository->novedad_insertar($request->get('id_shipping_order'), $request->get('guide_number'), $request->get('novedad'), $user->email);

            Log::info('Insertar novedad exitoso', ['request' => $data, 'user' => $user->username]);
        } catch (CustomException $e) {
            Log::warning('Insertar novedad', ['expcetion' => $e->getData()[0], 'id_shipping_order_detail' => $request['id_shipping_order_detail']]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Insertar novedad', ['expcetion' => $e->getMessage(), 'id_shipping_order_detail' => $request['id_shipping_order_detail']]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Insertar novedad', ['exception' => $e->getMessage(), 'id_shipping_order_detail' => $request['id_shipping_order_detail']]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success([
            'mensaje' => 'Pedido actualizado con éxito',
            'finalizado' => false
        ]);
    }
}
