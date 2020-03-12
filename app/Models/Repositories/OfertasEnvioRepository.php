<?php

namespace App\Models\Repositories;

use App\Models\Entities\OfertaEnvio;
use Exception;
use Illuminate\Support\Facades\DB;

class OfertasEnvioRepository
{
    public function get($id)
    {
        return OfertaEnvio::find($id);
    }

    public function all()
    {
        return OfertaEnvio::all();
    }

    public function delete($id)
    {
        OfertaEnvio::destroy($id);
    }

    public function update($id, array $data)
    {
        OfertaEnvio::find($id)->update($data);
    }

    public function register($input)
    {
        return OfertaEnvio::create($input);
    }

    public function getOferta($id)
    {
        $query = DB::table('ofertaenvio as oe')
            ->select('*', 'oe.estado as estado_ofertaenvio')
            ->join('ofertaenvio_conductor as oc', 'oc.idofertaenvio', '=', 'oe.idofertaenvio')
            ->where('oe.idofertaenvio', $id)
            ->first();
        return $query;
    }

    public function acpetarOferta($idofertaenvio, $datosVehiculo, $pedidos)
    {
        //         {#373 ▼
        //   +"idvehiculo": 8
        //   +"estado": "DISPONIBLE"
        //   +"tipo_vehiculo_asignacion": "VEHICULO_AFILIADO"
        //   +"marca": "pruebasaki"
        //   +"modelo": "prueba_model"
        //   +"numero_placa": "prueba123"
        //   +"color": "negro"
        //   +"capacidad": 1.0
        //   +"volumen": null
        //   +"numero_serie_motor": "1234567890"
        //   +"hp": "4"
        //   +"ejes": 2
        //   +"numero_neumaticos": 2
        //   +"tipo_llantas": "radiales"
        //   +"largo": null
        //   +"ancho": null
        //   +"alto": null
        //   +"soat": "321312"
        //   +"fecha_exp_soat": "2017-02-25 00:00:00"
        //   +"numero_poliza": "3432423"
        //   +"fecha_exp_poliza": "2017-02-25 00:00:00"
        //   +"certificado_mtc": null
        //   +"resolicion_matpel": null
        //   +"numero_rev_tecnica_regular": "1323"
        //   +"numero_rev_tecnica_matpel": null
        //   +"descripcion": null
        //   +"activo": 1
        //   +"observaciones": null
        //   +"eliminado": 0
        //   +"idpartner": null
        //   +"idtipo_vehiculo": 3
        //   +"idconductor": 5
        // }

        DB::beginTransaction();
        try {
            # Inactivamos la Oferta Envio
            OfertaEnvio::where('idofertaenvio', $idofertaenvio)->update(['estado' => 'INACTIVO']);

            # Aceptamos la Oferta Conductor
            DB::table('ofertaenvio_conductor as oc')->where('oc.idofertaenvio', $idofertaenvio)->update(['estado' => 'ACEPTADO']);

            # Crear un envio
            $idenvio = DB::table('envio')->insertGetId(
                [
                    'incidentes' => 0, 'fecha' => date("Y-m-d H:i:s"), 'estado_viaje_ida' => 0,
                    'estado_viaje_retorno' => 0, 'estado_viaje_finalizado' => 0, 'estado' => 'ASIGNADO',
                    'idvehiculo' => $datosVehiculo->idvehiculo, 'idconductor' => $datosVehiculo->idconductor,
                    'idofertaenvio' => $idofertaenvio, 'acuse_recibo' => 0
                ]
            );

            foreach ($pedidos as $key => $value) {
                # Actualizamos Pedido a Asignado
                DB::table('pedido as p')->where('idpedido', $value->idpedido)->update(['estado' => 'ASIGNADO']);

                # Actualizamos idenvio y estado Asignado los Pedidos detalle
                DB::table('pedido_detalle as pd')->where('idpedido_detalle', $value->idpedido_detalle)
                    ->update(
                        [
                            'estado' => 'ASIGNADO',
                            'idenvio' => $idenvio
                        ]
                    );
            }
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();
    }
}
