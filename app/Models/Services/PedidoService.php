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

class PedidoService
{
    protected $pedidoDetalleRepo;
    protected $envioServi;

    public function __construct(PedidoDetalleRepository $pedidoDetalleRepo, EnviosService $enviosService)
    {
        $this->pedidoDetalleRepo = $pedidoDetalleRepo;
        $this->envioServi = $enviosService;
    }

    public function grabarImagen(grabarImagen $request)
    {
        try {
            $pedido = $this->pedidoDetalleRepo->get($request->get('idpedido_detalle'));
            if (!$pedido) {
                throw new CustomException(['Pedido no encontrado.', 2010], 400);
            } elseif ($pedido->estado !== 'CURSO') {
                throw new CustomException(['El pedido no se encuentra en Curso.', 2011], 400);
            }

            $destination_path = env('RUTA_IMAGEN') . '/' . $request->get('idpedido_detalle');
            
            # CHeck if folder exists before create one
            if (!file_exists($destination_path)) {
                File::makeDirectory($destination_path, $mode = 0777, true, true);
                File::makeDirectory($destination_path . '/thumbnail', $mode = 0777, true, true);
            }
            
            $imagen = $request->file('imagen');
            $nombre_imagen = $pedido->idpedido_detalle . '_' . time() . '.jpg';
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

            $img_url = url('imagenes/' . $request->get('idpedido_detalle') . '/' . $nombre_imagen);
            # Guardamos los datos en la BD
            $this->pedidoDetalleRepo->insertarImagen(
                $pedido->idpedido_detalle,
                $img_url,
                $request->get('descripcion'),
                $request->get('tipo_imagen')
            );
        } catch (CustomException $e) {
            Log::warning('Grabar imagen', ['exception' => $e->getData()[0], 'res' => $request->except('imagen')]);
            return Res::error($e->getData(), $e->getCode());
        } catch (Exception $e) {
            Log::warning('Grabar imagen', ['exception' => $e->getMessage(), 'res' => $request->except('imagen')]);
            throw $e;
        }
        return Res::success(['mensaje' => 'Imagen guardada con exito']);
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
        } catch (Exception $e) {
            Log::warning('Obtener imagen', ['exception' => $e->getMessage(), 'idpedido_detalle' => $idpedido_detalle]);
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
            Log::warning('Actualizar Pedido', ['exception' => $e->getData()[0], 'res' => $data]);
            return Res::error($e->getData(), $e->getCode());
        } catch (Exception $e) {
            Log::warning('Actualizar Pedido', ['exception' => $e->getMessage(), 'res' => $data]);
            return Res::error([$e->getMessage(), $e->getCode()], 500);
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
}
