<?php

namespace App\Models\Repositories;

use App\Models\Entities\Conductor;
use Illuminate\Support\Facades\DB;

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
            ->select('*')
            ->join('ofertaenvio as oe', 'oe.idofertaenvio', '=', 'oc.idofertaenvio')
            ->where('oc.idconductor', $id)
            ->whereNotIn('oc.estado',['ACEPTADO','RECHAZADO'])
            ->where('oe.estado', 'ACTIVO')
            ->get();
        return $query;
    }
}
