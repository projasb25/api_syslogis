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
            'referencia_descarga as descripcion',
            'punto_latitud_descarga',
            'punto_longitud_descarga',
            'responsable_nombre_descarga as destinatario',
            'pd.estado',
            'idenvio',
            DB::raw('(select count(*) from imagenes_pedidodetalle ip where ip.idpedido_detalle = pd.idpedido_detalle) as nroImagenes'),
            'contacto_telefono_descarga',
            'pe.nro_guia_sistema'
        )
            ->from('pedido_detalle as pd')
            ->join('pedido as pe', 'pe.idpedido', '=', 'pd.idpedido')
            ->where('idofertaenvio', $id)
            ->whereIn('pd.estado', ['PREASIGNADO', 'ESPERA', 'ASIGNADO', 'CURSO'])
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
}
