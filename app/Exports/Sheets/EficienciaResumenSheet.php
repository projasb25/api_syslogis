<?php

namespace App\Exports\Sheets;

use App\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Contracts\View\View;

class EficienciaResumenSheet implements FromView, WithStyles, ShouldAutoSize, WithTitle
{

    protected $username;
    protected $fechaInicio;
    protected $fechaFin;
    protected $corpId;
    protected $orgId;
    protected $type;

    public function __construct($username, $fechaInicio, $fechaFin, $corpid, $orgid, $type)
    {
        $this->username = $username;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->orgId = $orgid;
        $this->corpId = $corpid;
        $this->type = $type;
    }

    /**
     * @return Builder
     */
    public function view(): View
    {
        if ($this->type == 'RECOLECCION') {
            $detalle = DB::select("CALL SP_REP_EFICIENCIA_RESUMEN_RECOLECCION(?,?,?,?,?,'RECOLECCION')",[$this->corpId, $this->orgId, $this->fechaInicio, $this->fechaFin, $this->username]);
            $motivos = DB::select("CALL SP_REP_EFICIENCIA_MOTIVOS_RECOLECCION(?,?,?,?,?,'RECOLECCION')",[$this->corpId, $this->orgId, $this->fechaInicio, $this->fechaFin, $this->username]);
        } else {
            $detalle = DB::select("CALL SP_REP_EFICIENCIA_RESUMEN(?,?,?,?,?,'RECOLECCION')",[$this->corpId, $this->orgId, $this->fechaInicio, $this->fechaFin, $this->username]);
            $motivos = DB::select("CALL SP_REP_EFICIENCIA_MOTIVOS(?,?,?,?,?,'RECOLECCION')",[$this->corpId, $this->orgId, $this->fechaInicio, $this->fechaFin, $this->username]);
        }
        return view('exports.reporte_eficiencia_resumen', [
            'detalle' => $detalle,
            'motivos' => $motivos,
            'type' => $this->type
        ]);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Resumen';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            2    => ['font' => ['bold' => true]],
            4    => ['font' => ['bold' => true]],
            5    => ['font' => ['bold' => true]],
        ];
    }
}
