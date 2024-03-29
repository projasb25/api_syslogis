<?php

namespace App\Models\Repositories;

use App\Models\Entities\Envio;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnvioRepository
{
    public function get($id)
    {
        return Envio::find($id);
    }

    public function all()
    {
        return Envio::all();
    }

    public function delete($id)
    {
        Envio::destroy($id);
    }

    public function update($id, array $data)
    {
        Envio::find($id)->update($data);
    }

    public function register($input)
    {
        return Envio::create($input);
    }

    public function iniciar($idenvio)
    {
        DB::beginTransaction();
        try {
            Envio::where('idenvio', $idenvio)->update(
                [
                    'estado' => 'CURSO',
                    'estado_viaje_ida' => true,
                    'fecha_viaje_ida' => date("Y-m-d H:i:s")
                ]
            );

            $pedido_detalles = DB::table('pedido_detalle')
                ->select('*')
                ->where('idenvio', $idenvio)
                ->get();

            foreach ($pedido_detalles as $key => $value) {
                DB::table('pedido_detalle')->where('idpedido_detalle', $value->idpedido_detalle)->update(['estado' => 'CURSO']);
                DB::table('pedido')->where('idpedido', $value->idpedido)->update(['estado' => 'CURSO']);
                DB::table('pedido_detalle_estado_pedido_detalle')->insert([
                    [
                        'fecha' => date("Y-m-d H:i:s"), 'observaciones' => 'Registro automático',
                        'idpedido_detalle' => $value->idpedido_detalle, 'idestado_pedido_detalle' => 12
                    ]
                ]);
            }
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();
    }

    public function finalizar($idenvio)
    {
        DB::beginTransaction();
        try {
            # Finalizamos envio
            Envio::where('idenvio', $idenvio)->update(['estado' => 'FINALIZADO']);

            # Finalizamos los pedidos_detalle asociados al idenvio
            DB::table('pedido_detalle')->where('idenvio', $idenvio)->update(['estado' => 'FINALIZADO']);

            # Finalizamos los pedidos asociados a los pedidos detalle
            $pedidos_detalle = DB::table('pedido_detalle')->where('idenvio', $idenvio)->get();
            foreach ($pedidos_detalle as $pedido_detalle) {
                DB::table('pedido')->where('idpedido', $pedido_detalle->idpedido)->update(['estado' => 'FINALIZADO']);
            }
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();
    }
}
