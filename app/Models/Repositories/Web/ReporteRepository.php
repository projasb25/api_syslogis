<?php

namespace App\Models\Repositories\Web;

use Illuminate\Support\Facades\DB;

class ReporteRepository
{
    public function sp_reporte_control($desde, $hasta, $username)
    {
        return DB::select("CALL SP_REPORTE_CONTROL(?,?,?)",[$desde, $hasta, $username]);
    }

    public function sp_reporte_torre_control($desde, $hasta)
    {
        return DB::select("CALL SP_REPORTE_TORRE_CONTROL(?,?)",[$desde, $hasta]);
    }

    public function sp_reporte_control_sku($desde, $hasta)
    {
        return DB::select("CALL SP_REPORTE_CONTROL_SKU(?,?)",[$desde, $hasta]);
    } 
}
