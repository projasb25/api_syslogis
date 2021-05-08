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
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EficienciaDetalleSheet implements FromView, WithStyles, ShouldAutoSize, WithTitle
{
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
     * @return Builder
     */
    public function view(): View
    {
        $detalle = DB::select("CALL SP_REP_EFICIENCIA_DETALLE(?,?,?,?,?,'RECOLECCION')",[$this->corpId, $this->orgId, $this->fechaInicio, $this->fechaFin, $this->username]);
        return view('exports.reporte_eficiencia_detalle', [
            'detalle' => $detalle,
        ]);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Detalle';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
