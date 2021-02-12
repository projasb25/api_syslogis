<?php

namespace App\Models\Repositories\Web;

use App\Exceptions\CustomException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillLoadRepository
{
    public function insertBillLoad($data)
    {
        DB::beginTransaction();
        try {
            $id = DB::table('bill_load')->insertGetId([
                'id_corporation' => $data['id_corporation'],
                'id_organization' => $data['id_organization'],
                'id_client' => $data['id_client'],
                'id_client_store' => $data['id_client_store'],
                'number_records' => $data['count'],
                'status' => 'PENDIENTE',
                'created_by' => $data['username']
            ]);

            foreach ($data['data'] as $key => &$value) {

                if (isset($value['product_exp_date']) && !is_string($value['product_exp_date'])) {
                    $value['product_exp_date'] = date('Y-m-d H:i:s', (($value['product_exp_date'] - (25567 + 1)) * 86400));
                }

                $value['id_bill_load'] = $id;
                $value['status'] = 'PENDIENTE';
                $value['created_by'] = $data['username'];

                DB::table('bill_load_detail')->insert([
                    'id_bill_load' => $value['id_bill_load'] ?? null,
                    'id_corporation' => $value['id_corporation'] ?? null,
                    'id_organization' => $value['id_organization'] ?? null,
                    'product_code' => $value['product_code'] ?? null,
                    'product_alt_code1' => $value['product_alt_code1'] ?? null,
                    'product_alt_code2' => $value['product_alt_code2'] ?? null,
                    'product_description' => $value['product_description'] ?? null,
                    'product_serie' => $value['product_serie'] ?? null,
                    'product_lots' => $value['product_lots'] ?? null,
                    'product_exp_date' => $value['product_exp_date'] ?? null,
                    'product_available' => $value['product_available'] ?? null,
                    'product_quantity' => $value['product_quantity'] ?? null,
                    'product_color' => $value['product_color'] ?? null,
                    'product_size' => $value['product_size'] ?? null,
                    'product_package_number' => $value['product_package_number'] ?? null,
                    'product_unitp_box' => $value['product_unitp_box'] ?? null,
                    'product_cmtr_pbox' => $value['product_cmtr_pbox'] ?? null,
                    'product_cmtr_quantity' => $value['product_cmtr_quantity'] ?? null,
                    'status' => $value['status'] ?? null,
                    'created_by' => $value['created_by'] ?? null
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        return $id;
    }
}
