<?php

namespace App\Models\Services;

use App\Exceptions\CustomException;
use App\Http\Requests\Pedido\grabarImagen;
use App\Helpers\ResponseHelper as Res;
use App\Models\Repositories\PedidoDetalleRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class PedidoService
{
    protected $pedidoDetalleRepo;

    public function __construct(PedidoDetalleRepository $pedidoDetalleRepo)
    {
        $this->pedidoDetalleRepo = $pedidoDetalleRepo;
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

            $imagen = $request->file('imagen');
            $nombre_imagen = $pedido->idpedido_detalle . '_' . time() . '.jpg';
            $destination_path = env('RUTA_IMAGEN');
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

            # Guardamos los datos en la BD
            $this->pedidoDetalleRepo->insertarImagen(
                $pedido->idpedido_detalle,
                $nombre_imagen,
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
                array_push($data, url('/imagenes/thumbnail/' . $img->url));
            }
        } catch (Exception $e) {
            Log::warning('Obtener imagen', ['exception' => $e->getMessage(), 'idpedido_detalle' => $idpedido_detalle]);
        }
        return Res::success($data);
    }
}
