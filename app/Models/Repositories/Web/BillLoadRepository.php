<?php

namespace App\Models\Repositories\Web;

use App\Exceptions\CustomException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillLoadRepository
{
    public function get($id)
    {
        return DB::table('bill_load')->where('id_bill_load', $id)->first();
    }

    public function getDetail($id)
    {
        return DB::table('bill_load_detail')->where('id_bill_load',$id)->get();
    }

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
                'created_by' => $data['username'],
                'id_load_template' => $data['id_load_template'],
                'entrance_guide' => $data['entrance_guide'],
                'entry_purchase_order' => $data['entry_purchase_order']
            ]);

            foreach ($data['data'] as $key => &$value) {

                if (isset($value['product_exp_date']) && !is_string($value['product_exp_date'])) {
                    $value['product_exp_date'] = date('Y-m-d H:i:s', (($value['product_exp_date'] - (25567 + 1)) * 86400));
                }

                if (isset($value['entry_date']) && !is_string($value['entry_date'])) {
                    $value['entry_date'] = date('Y-m-d H:i:s', (($value['entry_date'] - (25567 + 1)) * 86400));
                }

                $value['id_bill_load'] = $id;
                $value['status'] = 'PENDIENTE';
                $value['created_by'] = $data['username'];

                DB::table('bill_load_detail')->insert([
                    'id_bill_load' => $value['id_bill_load'] ?? null,
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
                    'product_line' => $value['product_line'] ?? null,
                    'status' => $value['status'] ?? null,
                    'created_by' => $value['created_by'] ?? null,
                    'hallway' => $value['hallway'] ?? null,
                    'level' => $value['level'] ?? null,
                    'column' => $value['column'] ?? null,
                    'shrinkage' => $value['shrinkage'] ?? 0,
                    'quarantine' => $value['quarantine'] ?? 0,
                    'scrap' => $value['scrap'] ?? 0,
                    'demo' => $value['demo'] ?? 0,
                    'batch' => $value['batch'] ?? 0,
                    'document_type' => $value['document_type'] ?? 0,
                    'document_number' => $value['document_number'] ?? 0,
                    'entry_date' => $value['entry_date'] ?? 0,
                    'observation' => $value['observation'] ?? '',
                    'owner' => $value['owner'] ?? 0,
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        return $id;
    }

    public function process($data)
    {
        DB::beginTransaction();
        try {
            DB::table('bill_load')
                ->where('id_bill_load', $data['id_bill_load'])
                ->update([
                    'status' => 'PROCESADO', 'modified_by' => $data['username']
                ]);
            

            foreach ($data['detalle'] as $value) {
                if (($value->shrinkage + $value->quarantine) > $value->product_quantity) {
                    throw new CustomException(['Cantidad invalida.', 2020], 400);
                }
                // TABLA PRODUCTO
                $check_product = DB::table('product')->where([
                    ['product_code', $value->product_code], 
                    ['id_client', $data['id_client']],
                    ['id_client_store', $data['id_client_store']]
                ])->first();
                if (!$check_product) {
                    $product_id = DB::table('product')->insertGetId([
                        'id_corporation' => $data['id_corporation'],
                        'id_organization' => $data['id_organization'],
                        'id_client' => $data['id_client'],
                        'id_client_store' => $data['id_client_store'],
                        'product_code' => $value->product_code,
                        'product_alt_code1' => $value->product_alt_code1,
                        'product_alt_code2' => $value->product_alt_code2,
                        'product_description' => $value->product_description,
                        'product_serie' => $value->product_serie,
                        'product_lots' => $value->product_lots,
                        'product_available' => $value->product_available,
                        'product_color' => $value->product_color,
                        'product_size' => $value->product_size,
                        'product_package_number' => $value->product_package_number,
                        'product_unitp_box' => $value->product_unitp_box,
                        'product_cmtr_pbox' => $value->product_cmtr_pbox,
                        'product_cmtr_quantity' => $value->product_cmtr_quantity,
                        'product_quantity' => $value->product_quantity,
                        'created_by' => $data['username'],
                        'product_shrinkage_total' =>  $value->shrinkage,
                        'product_quarantine_total' => $value->quarantine,
                        'product_scrap_total' => $value->scrap,
                        'product_demo_total' => $value->demo,
                        'product_available_total' => $value->product_quantity - ($value->shrinkage + $value->quarantine + $value->demo + $value->scrap),
                    ]);
                } else {
                    DB::table('product')->where('id_product', $check_product->id_product)
                    ->update([
                        'product_quantity' => $check_product->product_quantity + $value->product_quantity,
                        'product_shrinkage_total' => $check_product->product_shrinkage_total + $value->shrinkage,
                        'product_quarantine_total' => $check_product->product_quarantine_total + $value->quarantine,
                        'product_scrap_total' => $check_product->product_scrap_total + $value->scrap,
                        'product_demo_total' => $check_product->product_demo_total + $value->demo,
                        'product_available_total' => $check_product->product_available_total + ($value->product_quantity - ($value->shrinkage + $value->quarantine + $value->demo + $value->scrap)),
                        'modified_by' => $data['username']
                    ]);
                    $product_id = $check_product->id_product;
                }

                $check_inventory = DB::table('inventory')->where([
                    ['id_product', $product_id],
                    ['hallway', $value->hallway],
                    ['level', $value->level],
                    ['column', $value->column],
                    ['batch', $value->batch]
                ])->first();

                if (!$check_inventory) {
                    $shrinkage = $value->shrinkage;
                    $quarantine = $value->quarantine;
                    $balance = $value->product_quantity;
                    $scrap = $value->scrap;
                    $demo = $value->demo;
                    $balance_available = $value->product_quantity - ($value->shrinkage + $value->quarantine + $value->demo + $value->scrap);

                    $inventory_id = DB::table('inventory')->insertGetId([
                        'id_corporation' => $data['id_corporation'],
                        'id_organization' => $data['id_organization'],
                        'id_client' => $data['id_client'],
                        'id_client_store' => $data['id_client_store'],
                        'id_product' => $product_id,
                        'hallway' => $value->hallway,
                        'level' => $value->level,
                        'column' => $value->column,
                        'quantity' => $value->product_quantity,
                        'shrinkage' => $shrinkage,
                        'quarantine' => $quarantine,
                        'scrap' => $scrap,
                        'demo' => $demo,
                        'available' =>  $balance_available,
                        'created_by' => $data['username'],
                        'batch' => $value->batch,
                        'product_exp_date' => $value->product_exp_date,
                    ]);
                } else {
                    $inventory_id = $check_inventory->id_inventory;
                    $shrinkage = $check_inventory->shrinkage + $value->shrinkage;
                    $quarantine = $check_inventory->quarantine + $value->quarantine;
                    $scrap = $check_inventory->scrap +  $value->scrap;
                    $demo = $check_inventory->demo + $value->demo;
                    $balance = $check_inventory->quantity + $value->product_quantity;
                    $balance_available = $check_inventory->available + ($value->product_quantity - ($value->shrinkage + $value->quarantine + $value->demo + $value->scrap));

                    DB::table('inventory')->where('id_inventory', $check_inventory->id_inventory)
                    ->update([
                        'quantity' => $check_inventory->quantity + $value->product_quantity,
                        'shrinkage' => $shrinkage,
                        'quarantine' => $quarantine,
                        'scrap' => $scrap,
                        'demo' => $demo,
                        'available' => $balance_available,
                        'modified_by' => $data['username']
                    ]);
                }

                DB::table('kardex')->insert([
                    'id_corporation' => $data['id_corporation'],
                    'id_organization' => $data['id_organization'],
                    'id_product' => $product_id,
                    'id_inventory' => $inventory_id,
                    'description' => 'ENTRADA',
                    'quantity' => $value->product_quantity,
                    'shrinkage' => $value->shrinkage,
                    'quarantine' => $value->quarantine,
                    'scrap' => $value->scrap,
                    'demo' => $value->demo,
                    'balance' => $balance,
                    'balance_available' => $balance_available,
                    'type' => null,
                    'doc_type' => 'NOTA DE INGRESO',
                    'id_document' => $data['id_bill_load'],
                    'created_by' => $data['username'],
                    'batch' => $value->batch
                ]);

                DB::table('bill_load_detail')
                ->where('id_bill_load_detail', $value->id_bill_load_detail)
                ->update([
                    'status' => 'PROCESADO', 'modified_by' => $data['username'],
                    'hallway' => $value->hallway, 'level' => $value->level,
                    'column' => $value->column, 'shrinkage' => $value->shrinkage,
                    'scrap' => $value->scrap, 'demo' => $value->demo,
                    'quarantine' => $value->quarantine
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        return $inventory_id;
    }
}
