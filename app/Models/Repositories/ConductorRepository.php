<?php

namespace App\Models\Repositories;

use App\Models\Entities\Conductor;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConductorRepository
{
    public function get($id)
    {
        return Conductor::find($id);
    }

    public function all()
    {
        return Conductor::all();
    }

    public function delete($id)
    {
        Conductor::destroy($id);
    }

    public function update($id, array $data)
    {
        Conductor::find($id)->update($data);
    }

    public function register($input)
    {
        return Conductor::create($input);
    }

    public function get_ofertas($id)
    {
        $query = DB::table('ofertaenvio_conductor as oc')
            ->select(DB::raw(
                'oc.idofertaenvio_conductor, oc.idconductor,' .
                'oc.estado as ofertaconductor_estado,' .
                'oe.idofertaenvio , oe.fecha_creacion,' .
                '(select count(*) from pedido_detalle pd where pd.idofertaenvio = oe.idofertaenvio) as paradas'
            ))
            ->join('ofertaenvio as oe', function($join)
            {
                $join->on('oe.idofertaenvio', '=', 'oc.idofertaenvio');
                $join->whereNotIn('oc.estado', ['CANCELADO','FINALIZADO']);
            })
            ->join('pedido_detalle as pd', function($join)
            {
                $join->on('pd.idofertaenvio','=','oe.idofertaenvio');
                $join->whereNotIn('pd.estado', ['CANCELADO','FINALIZADO']);
            })
            ->where('oc.idconductor', $id)
            ->whereIn('oc.estado',['ESPERA','ACEPTADO'])
            ->groupBy('oc.idofertaenvio_conductor')
            ->get();
        return $query;
    }

    public function getOfertasActivas($idconductor)
    {
        $query = DB::table('ofertaenvio_conductor as oc')
            ->select(DB::raw(
                'oc.idofertaenvio_conductor, oc.idconductor,' .
                    'oc.estado as ofertaconductor_estado,' .
                    'oe.idofertaenvio , oe.fecha_creacion,' .
                    '(select count(*) from pedido_detalle pd where pd.idofertaenvio = oe.idofertaenvio) as paradas'
            ))
            ->join('ofertaenvio as oe', 'oe.idofertaenvio', '=', 'oc.idofertaenvio')
            ->leftJoin('envio as ev', 'ev.idofertaenvio', '=', 'oe.idofertaenvio')
            ->where('oc.idconductor', $idconductor)
            ->where('oc.estado', '=', 'ACEPTADO')
            ->whereIn('ev.estado', ['ASIGNADO', 'CURSO'])
            ->first();

        // $query = DB::table('ofertaenvio_conductor as oc')
        //     ->select(DB::raw(
        //         'oc.idofertaenvio_conductor, oc.idconductor,' .
        //             'oc.estado as ofertaconductor_estado,' .
        //             'oe.idofertaenvio, oe.estado , oe.fecha_creacion,' .
        //             '(select count(*) from pedido_detalle pd where pd.idofertaenvio = oe.idofertaenvio) as paradas'
        //     ))
        //     ->join('ofertaenvio as oe', 'oe.idofertaenvio', '=', 'oc.idofertaenvio')
        //     ->join('pedido_detalle as pd', 'pd.idofertaenvio', '=', 'oe.idofertaenvio')
        //     ->where('oc.idconductor', $idconductor)
        //     ->whereIn('oc.estado', ['ACEPTADO'])
        //     ->whereNotIn('pd.estado', ['FINALIZADO', 'CANCELADO'])
        //     ->groupBy('oc.idofertaenvio_conductor')
        //     ->first();
        return $query;
    }

    public function ActualizarEstado($idconductor, $estado)
    {
        DB::beginTransaction();
        try {
            # Actualizamos al conductor
            Conductor::where('idconductor', $idconductor)->update(['estado' => $estado]);

            # Actualizamos al vehiculo
            DB::table('vehiculo as vh')->where('idconductor', $idconductor)->update(['estado' => $estado]);
        } catch (Exception $e) {
            Log::warning("Actualizar estado conductor " . $e->getMessage());
            DB::rollback();
        }
        DB::commit();
    }

    public function getDatosVehiculo($idconductor)
    {
        return DB::table('vehiculo')->select('*')->where('idconductor', $idconductor)->first();
    }
}
