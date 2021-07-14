<?php

namespace App\Models\Services;

use App\Exceptions\CustomException;
use App\Http\Requests\Pedido\grabarImagen;
use App\Helpers\ResponseHelper as Res;
use App\Models\Repositories\PedidoDetalleRepository;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class PedidoService
{
    protected $pedidoDetalleRepo;
    protected $envioServi;

    public function __construct(PedidoDetalleRepository $pedidoDetalleRepo, EnviosService $enviosService)
    {
        $this->pedidoDetalleRepo = $pedidoDetalleRepo;
        $this->envioServi = $enviosService;
    }

    public function grabarImagen($request)
    {
        try {
            $destination_path = Storage::disk('imagenes')->getAdapter()->getPathPrefix();

            $imagen = $request->file('imagen');
            $nombre_imagen = 'ipm_' . time() . '.jpg';
            $thumbnail = Image::make($imagen->getRealPath());

            # Guardamos el thumnail primero
            $thumbnail->resize(250, 250, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destination_path . 'thumbnail/' . $nombre_imagen);

            # Redimesionamos la imagen a 720x720
            $resize = Image::make($imagen->getRealPath());
            $resize->resize(720, 720, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destination_path . '/' . $nombre_imagen);

            $ruta = url('storage/imagenes/' . $nombre_imagen);

            Log::info('Peido grabar imagen exitoso', ['request' => $request->except('imagen'), 'nombre_imagen' => $ruta]);
        } catch (CustomException $e) {
            Log::warning('Peido grabar imagen', ['expcetion' => $e->getData()[0], 'request' => $request->except('imagen')]);
            return Res::error($e->getData(), $e->getCode());
        } catch (QueryException $e) {
            Log::warning('Peido grabar imagen', ['expcetion' => $e->getMessage(), 'request' => $request->except('imagen')]);
            return Res::error(['Unxpected DB error', 3000], 400);
        } catch (Exception $e) {
            Log::warning('Peido grabar imagen', ['exception' => $e->getMessage(), 'request' => $request->except('imagen')]);
            return Res::error(['Unxpected error', 3000], 400);
        }
        return Res::success(['url' => $ruta]);
    }

    public function getImagen($idpedido_detalle)
    {
        try {
            $imagenes = $this->pedidoDetalleRepo->getImagen($idpedido_detalle);
            $data = [];
            foreach ($imagenes as $img) {
                $segmentos = explode('/',$img->url);
                array_push($data, url('/imagenes/' . $idpedido_detalle . '/thumbnail/' . end($segmentos)));
            }
            Log::info("Obtener imagen exitoso", ['idpedido_detalle' => $idpedido_detalle, 'nro_imagenes' => $imagenes->count()]);
        } catch (Exception $e) {
            Log::warning('Obtener imagen', ['exception' => $e->getMessage(), 'idpedido_detalle' => $idpedido_detalle]);
            return Res::error(['Error al Obtener la imagen', 2004], 400);
        }
        return Res::success($data);
    }

    public function actualizarPedido($request)
    {
        $finalizado = true;
        try {
            $data = $request->all();
            $pedido_detalle = $this->pedidoDetalleRepo->get($data['idpedido_detalle']);
            if (!$pedido_detalle) {
                throw new CustomException(['Pedido no encontrado.', 2012], 400);
            } elseif ($pedido_detalle->estado !== 'CURSO') {
                throw new CustomException(['El pedido no se encuentra en Curso.', 2013], 400);
            } elseif (!in_array($data['estado'], ['ENTREGADO', 'NO ENTREGADO', 'ENTREGA EN AGENCIA'])) {
                throw new CustomException(['Estado inválido', 2014], 400);
            }

            $this->pedidoDetalleRepo->actualizarPedido($data);

            $pedidos_finalizados = $this->pedidoDetalleRepo->getPedidosxEnvio($pedido_detalle->idenvio);
            foreach ($pedidos_finalizados as $pedido) {
                if ($pedido->estado !== 'FINALIZADO') {
                    $finalizado = false;
                    break;
                }
            }
            Log::info('Actualización con exito', ['res' => $data]);
        } catch (CustomException $e) {
            Log::warning('Actualizar Pedido error', ['exception' => $e->getData()[0], 'res' => $data]);
            return Res::error($e->getData(), $e->getCode());
        } catch (Exception $e) {
            Log::warning('Actualizar Pedido error', ['exception' => $e->getMessage(), 'res' => $data]);
            return Res::error(['Error al Actualizar el pedido', 3301], 400);
        }
        return Res::success([
            'mensaje' => 'Pedido actualizado con éxito',
            'finalizado' => $finalizado
        ]);
    }

    public function getMotivos($idcliente)
    {
        try {
            $motivos = $this->pedidoDetalleRepo->getMotivos($idcliente);
            $data = [];

            foreach ($motivos as $motivo) {
                array_push($data, $motivo->motivo);
            }
        } catch (Exception $e) {
            Log::warning('Get Motivos error', ['exception' => $e->getMessage(), 'idcliente' => $idcliente]);
            return Res::error([$e->getMessage(), $e->getCode()], 500);
        }
        return Res::success($data);
    }

    public function getAgencias($idcliente)
    {
        try {
            $agencias = $this->pedidoDetalleRepo->getAgencias($idcliente);
            $data = [];

            foreach ($agencias as $agencia) {
                array_push($data, $agencia->age_nombre);
            }
        } catch (Exception $e) {
            Log::warning('Get Agencias error', ['exception' => $e->getMessage(), 'idcliente' => $idcliente]);
            return Res::error([$e->getMessage(), $e->getCode()], 500);
        }
        return Res::success($data);
    }

    public function grabarImagenPedido($request)
    {
        try {
            $order = $this->pedidoDetalleRepo->getShippingOrder($request->get('id_order'));
            if (!$order) {
                throw new CustomException(['Orden no encontrada.', 2010], 400);
            } 
            elseif ($order->status !== 'CURSO') {
                throw new CustomException(['La orden no se encuentra en Curso.', 2011], 400);
            }

            $destination_path = Storage::disk('imagenes')->getAdapter()->getPathPrefix() . $order->id_order;
            # CHeck if folder exists before create one
            if (!file_exists($destination_path)) {
                File::makeDirectory($destination_path, $mode = 0777, true, true);
                File::makeDirectory($destination_path . '/thumbnail', $mode = 0777, true, true);
            }

            $imagen = $request->file('imagen');
            $nombre_imagen = $order->id_order . '_' . time() . '.jpg';
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

            $ruta = url('storage/imagenes/' . $order->id_order . '/' . $nombre_imagen);
            $this->pedidoDetalleRepo->insertarImagenOrden($order->id_order, $order->id_shipping_order, $ruta, $request->get('descripcion'), $request->get('tipo_imagen'));

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
        return Res::success(['url' => $ruta]);
    }
}
