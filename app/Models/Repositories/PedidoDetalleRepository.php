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
            'estado',
            'idenvio'
        )->where('idofertaenvio', $id)
            ->whereIn('estado', ['PREASIGNADO', 'ESPERA', 'ASIGNADO'])
            ->get();
    }
}
