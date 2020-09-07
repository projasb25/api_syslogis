<?php

namespace App\Models\Repositories\Web;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MassiveLoadRepository
{
    public function insertMassiveLoad($data)
    {
        DB::beginTransaction();
        try {
            $id = DB::table('massive_load')->insertGetId([
                'number_records' => $data['count'],
                'status' => 'PENDIENTE',
                'created_by' => $data['username']
                ]);
                
            foreach ($data['data'] as $key => &$value) {
                if (!array_key_exists('client_barcode', $value) || !isset($value['client_barcode'])) {
                    $value['client_barcode'] = Str::random(40);
                }
                if (!array_key_exists('sku_description', $value) || !isset($value['sku_description'])) {
                    $value['sku_description'] = Str::random(40);
                }
                $value['id_massive_load'] = $id;
                $value['status'] = 'PENDIENTE';
                $value['created_by'] = $data['username'];
                
                DB::table('massive_load_details')->insert([
                    'id_massive_load' => $value['id_massive_load'] ?? null,
                    'seg_code' => $value['seg_code'] ?? null,
                    'alt_code1' => $value['alt_code1'] ?? null,
                    'alt_code2' => $value['alt_code2'] ?? null,
                    'client_date' => $value['client_date'] ?? null,
                    'client_barcode' => $value['client_barcode'] ?? null,
                    'client_dni' => $value['client_dni'] ?? null,
                    'client_name' => $value['client_name'] ?? null,
                    'client_phone1' => $value['client_phone1'] ?? null,
                    'client_phone2' => $value['client_phone2'] ?? null,
                    'client_phone3' => $value['client_phone3'] ?? null,
                    'client_email' => $value['client_email'] ?? null,
                    'client_address' => $value['client_address'] ?? null,
                    'client_address_reference' => $value['client_address_ref,erence'] ?? null,
                    'coord_latitude' => $value['coord_latitude'] ?? null,
                    'coord_longitude' => $value['coord_longitude'] ?? null,
                    'department' => $value['department'] ?? null,
                    'district' => $value['district'] ?? null,
                    'province' => $value['province'] ?? null,
                    'sku_code' => $value['sku_code'] ?? null,
                    'sku_description' => $value['sku_description'] ?? null,
                    'sku_weight' => $value['sku_weight'] ?? null,
                    'sku_pieces' => $value['sku_pieces'] ?? null,
                    'sku_brand' => $value['sku_brand'] ?? null,
                    'sku_size' => $value['sku_size'] ?? null,
                    'box_code' => $value['box_code'] ?? null,
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
