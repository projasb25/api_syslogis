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
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class NuevaEficienciaSheet implements FromView, WithStyles, ShouldAutoSize, WithTitle
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
            $cuadro_general = DB::select("CALL SP_REP_EFICIENCIA_RECOLECCION_PT1(?,?,?,?,?,'RECOLECCION')",[$this->corpId, $this->orgId, $this->fechaInicio, $this->fechaFin, $this->username]);
            $cuadro_detalle = DB::select("CALL SP_REP_EFICIENCIA_RECOLECCION_PT2(?,?,?,?,?,'RECOLECCION')",[$this->corpId, $this->orgId, $this->fechaInicio, $this->fechaFin, $this->username]);
        } else {
            $cuadro_general = DB::select("CALL SP_REP_EFICIENCIA_V2_PT1(?,?,?,?,?,'RECOLECCION')",[$this->corpId, $this->orgId, $this->fechaInicio, $this->fechaFin, $this->username]);
            $cuadro_detalle = DB::select("CALL SP_REP_EFICIENCIA_V2_PT2(?,?,?,?,?,'RECOLECCION')",[$this->corpId, $this->orgId, $this->fechaInicio, $this->fechaFin, $this->username]);
        }

        return view('exports.reporte_eficiencia_cuadro_resumen', [
            'cuadro_general' => $cuadro_general,
            'cuadro_detalle' => $cuadro_detalle,
            'type' => $this->type
        ]);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Eficiencia';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            2    => ['font' => ['bold' => true]],
            4    => ['font' => ['bold' => true]],
        ];
    }
}