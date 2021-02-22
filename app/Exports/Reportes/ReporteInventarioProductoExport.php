<?php

namespace App\Exports\Reportes;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReporteInventarioProductoExport implements FromCollection, WithMapping, WithHeadings
{
    protected $where;
    protected $user_data;

    public function __construct($where,$user_data) {
        $this->where = $where;
        $this->user_data = $user_data;
    }

    public function headings(): array
    {
        return [
            "CLIENT_NAME","STORE_NAME","PRODUCT_CODE","PRODUCT_QUANTITY","PRODUCT_QUARANTINE_TOTAL",
            "PRODUCT_SHRINKAGE_TOTAL","PRODUCT_AVAILABLE_TOTAL","PRODUCT_DESCRIPTION","PRODUCT_SERIE",
            "PRODUCT_LOTS","PRODUCT_EXP_DATE","PRODUCT_AVAILABLE","PRODUCT_COLOR","PRODUCT_SIZE",
            "PRODUCT_PACKAGE_NUMBER","PRODUCT_UNITP_BOX","PRODUCT_CMTR_PBOX",
            "PRODUCT_CMTR_QUANTITY"
        ];
    }

    public function collection()
    {
        return collect(DB::select("CALL SP_REPORTE_INVENTARIO_PRODUCTO(?,?,?,?)",
            [
                $this->where,
                0,
                0,
                $this->user_data
            ]
        ));
    }

    public function map($collection): array
    {
        return [
            $collection->client_name,
            $collection->store_name,
            (int) $collection->product_code,
            (int) $collection->quantity,
            (int) $collection->quarantine,
            (int) $collection->shrinkage,
            (int) $collection->available,
            $collection->product_description,
            $collection->product_serie,
            $collection->product_lots,
            $collection->product_exp_date,
            $collection->product_available,
            $collection->product_color,
            $collection->product_size,
            $collection->product_package_number,
            $collection->product_unitp_box,
            $collection->product_cmtr_pbox,
            $collection->product_cmtr_quantity
        ];
    }
}
