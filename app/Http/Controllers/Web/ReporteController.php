<?php

namespace App\Http\Controllers\Web;

use App\Exports\InvoicesExport;
use App\Exports\Reportes\ReporteEficienciaExport;
use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Models\Services\Web\ReporteService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    protected $service;

    public function __construct(ReporteService $reporteService)
    {
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

    public function control_proveedor(Request $request)
    {
        return $this->service->control_proveedor($request);
    }

    public function img_monitor(Request $request)
    {
        return $this->service->img_monitor($request);
    }

    public function reporte_recoleccion(Request $request)
    {
        return $this->service->reporte_recoleccion($request);
    }

    public function reporte_eficiencia(Request $request)
    {
        return $this->service->reporte_eficiencia($request);
    }

    public function export(Request $request)
    {
        return Excel::download(new ReporteEficienciaExport, 'users.xlsx');
        // return (new InvoicesExport(2018))->download('invoices.xlsx');
    }
}
