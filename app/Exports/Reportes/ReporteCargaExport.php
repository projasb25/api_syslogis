<?php

namespace App\Exports\Reportes;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReporteCargaExport implements FromCollection, WithMapping, WithHeadings
{
    protected $fechaInicio;
    protected $fechaFin;
    protected $corpId;
    protected $orgId;

    public function __construct($fechaInicio, $fechaFin, $corpid, $orgid)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->orgId = $orgid;
        $this->corpId = $corpid;
    }

    public function headings(): array
    {
        return [
            'NAME', 'GUIDE_NUMBER', 'ORDER_NUMBER', 'SEG_CODE', 'ALT_CODE1', 'ALT_CODE2', 'CLIENT_DATE', 'CLIENT_BARCODE',
            'CLIENT_DATE2', 'CLIENT_DNI', 'CLIENT_NAME', 'CLIENT_PHONE1', 'CLIENT_PHONE2', 'CLIENT_PHONE3', 'CLIENT_EMAIL',
            'DELIVERY_TYPE', 'CONTACT_NAME', 'CONTACT_PHONE', 'STATUS', 'TIPO', 'ATTEMPT', 'COLLECT_TIME_RANGE', 'COLLECT_CONTACT_NAME',
            'PAYMENT_METHOD', 'AMOUNT', 'SELLER_NAME', 'FECHA_CARGA', 'ADDRESS', 'ADDRESS_REFERNCE', 'LATITUDE_DELIVERY',
            'LONGITUDE_DELIVERY', 'DEPARTMENT', 'DISTRICT', 'PROVINCE', 'SKU_CODE', 'SKU_DESCRIPTION', 'SKU_WEIGHT', 'SKU_PIECES',
            'SKU_BRAND', 'SKU_SIZE', 'BOX_CODE', 'ULTIMA_FECHA_ESTADO', 'ULTIMO_ESTADO', 'OBSERVACIONES'
        ];
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect(DB::select("CALL SP_REPORTE_DATA_CARGA(?,?,?,?)", [$this->fechaInicio, $this->fechaFin, $this->corpId, $this->orgId]));
    }

    public function map($collection): array
    {
        return [
            $collection->name,
            $collection->guide_number,
            $collection->order_number,
            $collection->seg_code,
            $collection->alt_code1,
            $collection->alt_code2,
            $collection->client_date,
            $collection->client_barcode,
            $collection->client_date2,
            $collection->client_dni,
            $collection->client_name,
            $collection->client_phone1,
            $collection->client_phone2,
            $collection->client_phone3,
            $collection->client_email,
            $collection->delivery_type,
            $collection->contact_name,
            $collection->contact_phone,
            $collection->status,
            $collection->type,
            $collection->attempt,
            $collection->collect_time_range,
            $collection->collect_contact_name,
            $collection->payment_method,
            $collection->amount,
            $collection->seller_name,
            $collection->fecha_carga,
            $collection->address,
            $collection->address_refernce,
            $collection->latitude_delivery,
            $collection->longitude_delivery,
            $collection->department,
            $collection->district,
            $collection->province,
            $collection->sku_code,
            $collection->sku_description,
            $collection->sku_weight,
            $collection->sku_pieces,
            $collection->sku_brand,
            $collection->sku_size,
            $collection->box_code,
            $collection->ultima_fecha_estado,
            $collection->ultimo_estado,
            $collection->observaciones,
        ];
    }
}
