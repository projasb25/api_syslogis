<?php

namespace App\Exports\Reportes;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReporteImgMonitorExport implements FromCollection, WithMapping, WithHeadings
{
    protected $username;
    protected $where;

    public function __construct($username, $where) {
        $this->username = $username;
        $this->where = $where;
    }

    public function headings(): array
    {
        return [
            'NRO ENVIO', 'ESTADO', 'NRO GUIA', 'FECHA ENVIO', 'NRO PLACA', 'PROVEEDOR', 'NRO IMAGENES'
        ];
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect(DB::select("CALL SP_REPORTE_MONITOR_IMAGEN(?,?)",[$this->username, $this->where]));
    }

    public function map($collection): array
    {
        return [
            $collection->id_shipping_order,
            $collection->status,
            $collection->guide_number,
            $collection->date_created,
            $collection->plate_number,
            $collection->name,
            $collection->images_count
        ];
    }
}