<?php

namespace App\Exports\Reportes;

use App\Exports\Sheets\EficienciaDetalleSheet;
use App\Exports\Sheets\EficienciaResumenSheet;
use App\Exports\Sheets\NuevaEficienciaSheet;
use App\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReporteEficienciaExport implements WithMultipleSheets
{
    use Exportable;

    protected $username;
    protected $fechaInicio;
    protected $fechaFin;
    protected $corpId;
    protected $orgId;

    public function __construct($username, $fechaInicio, $fechaFin, $corpid, $orgid)
    {
        $this->username = $username;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->orgId = $orgid;
        $this->corpId = $corpid;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return User::all();
    }

    public function sheets(): array
    {
        $sheets = [];

        array_push($sheets, new EficienciaResumenSheet($this->username, $this->fechaInicio, $this->fechaFin, $this->corpId, $this->orgId));
        array_push($sheets, new EficienciaDetalleSheet($this->username, $this->fechaInicio, $this->fechaFin, $this->corpId, $this->orgId));
        array_push($sheets, new NuevaEficienciaSheet($this->username, $this->fechaInicio, $this->fechaFin, $this->corpId, $this->orgId));

        return $sheets;
    }
}
