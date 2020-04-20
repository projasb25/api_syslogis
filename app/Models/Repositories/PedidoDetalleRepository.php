<?php

namespace App\Models\Repositories;

use App\Models\Entities\PedidoDetalle;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PedidoDetalleRepository
{
    public function get($id)
    {
        return PedidoDetalle::find($id);
    }

    public function all()
    {
        return PedidoDetalle::all();
    }

    public function delete($id)
    {
        PedidoDetalle::destroy($id);
    }

    public function update($id, array $data)
    {
        PedidoDetalle::find($id)->update($data);
    }

    public function register($input)
    {
        return PedidoDetalle::create($input);
    }

    public function getPedidos($id)
    {
        return PedidoDetalle::where('idofertaenvio', $id)
            ->whereIn('estado', ['PREASIGNADO', 'ESPERA'])
            ->get();
    }

    public function actualizarCoordenadas($data)
    {
        DB::beginTransaction();
        try {
            foreach ($data as $key => $value) {
                PedidoDetalle::where('idpedido_detalle', $value['idpedido_detalle'])
                    ->update([
                        'punto_latitud_descarga' => $value['punto_latitud_descarga'],
                        'punto_longitud_descarga' => $value['punto_longitud_descarga']
                    ]);
            }
        } catch (Exception $e) {
            Log::warning("Actualizar coordenadas " . $e->getMessage());
            DB::rollback();
        }
        DB::commit();
    }

    public function getPedidosApp($id)
    {
        return PedidoDetalle::select(
            'idpedido_detalle',
            'direccion_descarga',
            'cli.nombres as descripcion',
            'punto_latitud_descarga',
            'punto_longitud_descarga',
            'responsable_nombre_descarga as destinatario',
            'pd.estado',
            'idenvio',
            DB::raw('(select count(*) from imagenes_pedidodetalle ip where ip.idpedido_detalle = pd.idpedido_detalle) as nroImagenes'),
            'contacto_telefono_descarga',
            'pe.nro_guia_sistema',
            'pe.idcliente',
            DB::raw('(select nombre from estado_pedido_detalle epd where idestado_pedido_detalle = (select idestado_pedido_detalle from pedido_detalle_estado_pedido_detalle pdepd where idpedido_detalle = pd.idpedido_detalle order by idpedido_detalle_estado_pedido_detalle desc limit 1)) as estadoProducto')
        )
            ->from('pedido_detalle as pd')
            ->join('pedido as pe', 'pe.idpedido', '=', 'pd.idpedido')
            ->join('cliente as cli','cli.idcliente','=','pe.idcliente')
            ->where('idofertaenvio', $id)
            ->whereIn('pd.estado', ['PREASIGNADO', 'ESPERA', 'ASIGNADO', 'CURSO', 'FINALIZADO'])
            ->get();
    }

    public function insertarImagen($id, $nombre_imagen, $descripcion, $tipo)
    {
        DB::table('imagenes_pedidodetalle')->insert([
            'url' => $nombre_imagen, 'descripcion' => $descripcion,
            'idpedido_detalle' => $id, 'tipo_imagen' => $tipo,
            'eliminado' => 0, 'fecha' => date("Y-m-d H:i:s")
        ]);
    }

    public function getImagen($idpedido_detalle)
    {
        return DB::table('imagenes_pedidodetalle')
            ->select('*')
            ->where('idpedido_detalle', $idpedido_detalle)
            ->get();
    }

    public function actualizarPedido($data)
    {
        DB::beginTransaction();
        try {

            switch ($data['estado']) {
                case 'ENTREGADO':
                    $estado = 16;
                    $observaciones = 'Registro automático';
                    break;
                case 'NO ENTREGADO':
                    $estado = 17;
                    $observaciones = $data['observacion'];
                    break;
                case 'ENTREGA EN AGENCIA':
                    $estado = 20;
                    $observaciones = $data['observacion'];
                    break;
                default:
                    break;
            }

            # pedido_detalle a finalizado
            PedidoDetalle::where('idpedido_detalle', $data['idpedido_detalle'])->update(
                [
                    'estado' => 'FINALIZADO',
                    'punto_latitud_descarga' => $data['latitud'],
                    'punto_longitud_descarga' => $data['longitud']
                ]
            );

            # insertar en pedido_detalle_estado_pedido_detalle 16 Registro automático
            DB::table('pedido_detalle_estado_pedido_detalle')->insert([
                'fecha' => date("Y-m-d H:i:s"), 'observaciones' => $observaciones,
                'idpedido_detalle' => $data['idpedido_detalle'], 'idestado_pedido_detalle' => $estado
            ]);
        } catch (Exception $e) {
            Log::warning("Actualizar estado pedido " . $e->getMessage());
            DB::rollback();
        }
        DB::commit();
    }

    public function getPedidosxEnvio($idenvio)
    {
        return PedidoDetalle::where('idenvio', $idenvio)->get();
    }

    public function getMotivos($idcliente)
    {
        return DB::table('tipologia_envio')->where('idcliente', $idcliente)->get();
    }

    public function getAgencias($idcliente)
    {
        return DB::table('agencias_cliente')->where('idcliente', $idcliente)->get();
    }
}
