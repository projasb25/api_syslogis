<?php

namespace App\Exports\Reportes;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReporteControlExport implements FromCollection, WithMapping, WithHeadings
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
            'CLIENTE', 'CODIGO BARRA', 'CODIGO SEGUIMIENTO', 'NRO GUIA', 'FECHA PROMESA', 'ESTADO PEDIDO',
            'FECHA PEDIDO', 'HORA PEDIDO', 'FECHA ENVIO', 'NOMBRE CONDUCTOR', 'TIPO VEHICULO', 'NRO PLACA', 'PROVEEDOR',
            'ULTIMO ESTADO', 'NOMBRE CLIENTE', 'TELEFONO 1', 'TELEFONO 2', 'DIRECCION', 'DEPARTAMENTO', 'DISTRITO',
            'PROVINCIA', 'TIPO ZONA', 'FECHA ASIGNADO', 'ULTFECHA ESTADO', 'ESTADO DE DESCARGA', 'OBSERVACIONES',
            'FECHA VISITA1', 'RESULTADO 1', 'FECHA VISITA2', 'RESULTADO 2', 'FECHA VISITA3', 'RESULTADO 3',
            'CANTIDAD VISITAS', 'NRO IMAGENES'
        ];
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect(DB::select("CALL SP_REPORTE_CONTROL(?,?,?)",[$this->fechaInicio, $this->fechaFin, $this->username]));
    }

    public function map($collection): array
    {
        return [
            $collection->cliente,
            $collection->codigo_barra,
            $collection->codigo_seguimiento,
            $collection->nro_guia,
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
            $collection->tipo_zona,
            $collection->fecha_asignado,
            $collection->ultfecha_estado,
            $collection->estado_de_descarga,
            $collection->observaciones,
            $collection->fecha_visita1,
            $collection->resultado_1,
            $collection->fecha_visita2,
            $collection->resultado_2,
            $collection->fecha_visita3,
            $collection->resultado_3,
            $collection->cantidad_visitas,
            $collection->nro_imagenes
        ];
    }
}
