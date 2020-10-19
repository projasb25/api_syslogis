<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Services\Web\ReporteService;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    protected $service;
    
    public function __construct(ReporteService $reporteService) {
        $this->service = $reporteService;
    }

    public function reporte_control(Request $request)
    {
        return $this->service->reporte_control($request);
    }

    public function reporte_torre_control(Request $request)
    {
        return $this->service->reporte_torre_control($request);
    }

    public function reporte_control_sku(Request $request)
    {
        return $this->service->reporte_control_sku($request);
    }
}
