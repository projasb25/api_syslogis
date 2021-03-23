<?php

namespace App\Exports\Reportes;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReporteRecoleccionExport implements FromCollection, WithMapping, WithHeadings
{
    protected $username;
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($username, $fechaInicio, $fechaFin)
    {
        $this->username = $username;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public function headings(): array
    {
        return [
            'FECHA_PEDIDO','CLIENTE','NRO PLACA','TIPO SERVICIO','ADDRESS','SEG CODE','TIPO SERVICIO',
            'CLIENT NAME','SALIDA CD','LLEGADA CLIENTE','CUMPLIMIENTO','OBSERVACIONES','ESTADO',
            'FOTOS'
        ];
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect(DB::select("CALL SP_REPORTE_RECOLECCION(?,?,?)", [$this->fechaInicio, $this->fechaFin, $this->username]));
    }

    public function map($collection): array
    {
        return [
            $collection->fecha_pedido,
            $collection->cliente,
            $collection->nro_placa,
            $collection->tipo_servicio,
            $collection->address,
            $collection->seg_code,
            $collection->tipo_servicio,
            $collection->client_name,
            $collection->salida_cd,
            $collection->llegada_cliente,
            $collection->cumplimiento,
            $collection->observaciones,
            $collection->estado,
            $collection->fotos
        ];
    }
}
