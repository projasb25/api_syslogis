<?php

namespace App\Models\Repositories\Web;

use Illuminate\Support\Facades\DB;

class ReporteRepository
{
    public function sp_reporte_control($desde, $hasta)
    {
        return DB::select("CALL SP_REPORTE_CONTROL(?,?)",[$desde, $hasta]);
    }    
}
