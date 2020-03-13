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
            ->select('*', 'oc.estado as ofertaconductor_estado', DB::raw('(select count(*) from pedido_detalle pd where pd.idofertaenvio = oe.idofertaenvio) as paradas'))
            ->join('ofertaenvio as oe', 'oe.idofertaenvio', '=', 'oc.idofertaenvio')
            ->where('oc.idconductor', $id)
            ->whereNotIn('oc.estado', ['RECHAZADO'])
            ->get();
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
