<?php

namespace App\Models\Repositories\Web;

use Illuminate\Support\Facades\DB;

class PurchaseOrderRepository
{
    public function insertPurchaseOrder($data)
    {
        DB::beginTransaction();
        try {
            $id = DB::table('purchase_order')->insertGetId([
                'id_corporation' => $data['id_corporation'],
                'id_organization' => $data['id_organization'],
                'id_client' => $data['id_client'],
                'id_client_store' => $data['id_client_store'],
                'id_load_template' => $data['id_load_template'],
                'id_buyer' => $data['id_buyer'],
                'number_records' => $data['count'],
                'status' => 'PENDIENTE',
                'created_by' => $data['username'],
                'purchase_order_number' => $data['purchase_order_number']
            ]);

            foreach ($data['data'] as $key => $value) {
                DB::table('purchase_order_detail')->insert([
                    'id_purchase_order' =>  $id,
                    'product_code' => $value['product_code'] ?? null,
                    'product_description' => $value['product_description'] ?? null,
                    'product_quantity' => $value['product_quantity'] ?? null,
                    'status' => 'PENDIENTE',
                    'created_by' => $data['username']
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
