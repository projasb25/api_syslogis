<?php

namespace App\Exports\Reportes;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReporteControlProveedorExport implements FromCollection, WithMapping, WithHeadings
{
    protected $username;
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($username, $fechaInicio, $fechaFin) {
        $this->username = $username;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public function headings(): array
    {
        return [
            'CLIENTE', 'NUMERO GUIA', 'FECHA PROMESA', 'ESTADO PEDIDO', 'FECHA PEDIDO',
            'HORA PEDIDO', 'FECHA ENVIO', 'NOMBRE CONDUCTOR', 'TIPO VEHICULO', 'NRO PLACA', 'PROVEEDOR',
            'ULTIMO ESTADO', 'NOMBRE CLIENTE', 'TELEFONO 1', 'TELEFONO 2', 'DIRECCION', 'DEPARTAMENTO',
            'DISTRITO', 'PROVINCIA', 'FECHA ASIGNADO', 'ULTFECHA ESTADO', 'ESTADO DESCARGA', 'OBSERVACIONES',
            'VISITA'
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect(DB::select("CALL SP_REPORTE_ASIGNACION_POR_GUIA(?,?,?)",[$this->fechaInicio, $this->fechaFin, $this->username]));
    }

    public function map($collection): array
    {
        return [
            $collection->cliente,
            $collection->numero_guia,
            $collection->fecha_promesa,
            $collection->estado_pedido,
            $collection->fecha_pedido,
            $collection->hora_pedido,
            $collection->fecha_envio,
            $collection->nombre_conductor,
            $collection->tipo_vehiculo,
            $collection->nro_placa,
            $collection->proveedor,
            $collection->ultimo_estado,
            $collection->nombre_cliente,
            $collection->telefono_1,
            $collection->telefono_2,
            $collection->direccion,
            $collection->departamento,
            $collection->distrito,
            $collection->provincia,
            $collection->fecha_asignado,
            $collection->ultfecha_estado,
            $collection->estado_descarga,
            $collection->observaciones,
            $collection->visita
        ];
    }
}
