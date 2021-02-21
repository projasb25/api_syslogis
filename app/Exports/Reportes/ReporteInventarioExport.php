<?php

namespace App\Exports\Reportes;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReporteInventarioExport implements FromCollection, WithMapping, WithHeadings
{
    protected $where;
    protected $take;
    protected $skip;
    protected $user_data;

    public function __construct($where, $take, $skip,$user_data) {
        $this->where = $where;
        $this->take = $take;
        $this->skip = $skip;
        $this->user_data = $user_data;
    }

    public function headings(): array
    {
        return [
            'CLIENT_NAME','STORE_NAME','HALLWAY','COLUMN','LEVEL','QUANTITY','QUARANTINE','SHRINKAGE',
            'AVAILABLE', 'PRODUCT_CODE', 'PRODUCT_DESCRIPTION', 'PRODUCT_SERIE', 'PRODUCT_LOTS',
            'PRODUCT_EXP_DATE', 'PRODUCT_AVAILABLE', 'PRODUCT_COLOR', 'PRODUCT_SIZE',
            'PRODUCT_PACKAGE_NUMBER','PRODUCT_UNITP_BOX','PRODUCT_CMTR_PBOX',
            'PRODUCT_CMTR_QUANTITY'
        ];
    }

    public function collection()
    {
        return collect(DB::select("CALL SP_REPORTE_INVENTARIO(?,?,?,?)",
            [
                $this->where,
                $this->take,
                $this->skip,
                $this->user_data
            ]
        ));
    }

    public function map($collection): array
    {
        return [
            $collection->client_name,
            $collection->store_name,
            $collection->hallway,
            $collection->column,
            $collection->level,
            $collection->quantity,
            $collection->quarantine,
            $collection->shrinkage,
            $collection->available,
            $collection->product_code,
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
