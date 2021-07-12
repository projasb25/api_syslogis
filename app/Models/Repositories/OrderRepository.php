<?php

namespace App\Models\Repositories;

use App\Exceptions\CustomException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderRepository
{
    public function getMassiveLoad($id)
    {
        return DB::table('massive_load')->where('id_massive_load', $id)->first();
    }

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
                $check_ubigeo1 = DB::table('ubigeo')
                    ->whereRaw('LOWER(TRIM(department)) = ? ', [trim(strtolower($value['pickup_department']))])
                    ->whereRaw('LOWER(TRIM(province)) = ? ', [trim(strtolower($value['pickup_province']))])
                    ->whereRaw('LOWER(TRIM(district)) = ? ', [trim(strtolower($value['pickup_district']))])
                    ->first();
                $check_ubigeo2 = DB::table('ubigeo')
                    ->whereRaw('LOWER(TRIM(department)) = ? ', [trim(strtolower($value['delivery_department']))])
                    ->whereRaw('LOWER(TRIM(province)) = ? ', [trim(strtolower($value['delivery_province']))])
                    ->whereRaw('LOWER(TRIM(district)) = ? ', [trim(strtolower($value['delivery_district']))])
                    ->first();
                if (!$check_ubigeo1 || !$check_ubigeo2) {
                    throw new CustomException(['Error en el departamento, provincia y distrito. (Linea: '.($key+2).' )', 2121], 400);
                }
                
                $value['id_massive_load'] = $id;
                $value['status'] = 'PENDIENTE';
                $value['created_by'] = $data['username'];

                DB::table('massive_load_details')->insert([
                    'id_massive_load' => $value['id_massive_load'] ?? null,
                    'guide_number' => $value['guide_number'] ?? null,
                    'seg_code' => $value['seg_code'] ?? null,
                    'pickup_contact_name' => $value['pickup_contact_name'] ?? null,
                    'pickup_document_type' => $value['pickup_document_type'] ?? null,
                    'pickup_document' => $value['pickup_document'] ?? null,
                    'pickup_phone' => $value['pickup_phone'] ?? null,
                    'pickup_address' => $value['pickup_address'] ?? null,
                    'pickup_reference' => $value['pickup_reference'] ?? null,
                    'pickup_contact_name_2' => $value['pickup_contact_name_2'] ?? null,
                    'pickup_document_type_2' => $value['pickup_document_type_2'] ?? null,
                    'pickup_document_2' => $value['pickup_document_2'] ?? null,
                    'pickup_phone_2' => $value['pickup_phone_2'] ?? null,
                    'pickup_time' => $value['pickup_time'] ?? null,
                    'pickup_district' => $value['pickup_district'] ?? null,
                    'pickup_department' => $value['pickup_department'] ?? null,
                    'pickup_province' => $value['pickup_province'] ?? null,
                    'delivery_contact_name' => $value['delivery_contact_name'] ?? null,
                    'delivery_document_type' => $value['delivery_document_type'] ?? null,
                    'delivery_document' => $value['delivery_document'] ?? null,
                    'delivery_phone' => $value['delivery_phone'] ?? null,
                    'delivery_address' => $value['delivery_address'] ?? null,
                    'delivery_reference' => $value['delivery_reference'] ?? null,
                    'delivery_contact_name_2' => $value['delivery_contact_name_2'] ?? null,
                    'delivery_document_type_2' => $value['delivery_document_type_2'] ?? null,
                    'delivery_document_2' => $value['delivery_document_2'] ?? null,
                    'delivery_phone_2' => $value['delivery_phone_2'] ?? null,
                    'delivery_time' => $value['delivery_time'] ?? null,
                    'delivery_district' => $value['delivery_district'] ?? null,
                    'delivery_department' => $value['delivery_department'] ?? null,
                    'delivery_province' => $value['delivery_province'] ?? null,
                    'product_name' => $value['product_name'] ?? null,
                    'product_description' => $value['product_description'] ?? null,
                    'product_weight' => $value['product_weight'] ?? null,
                    'product_size' => $value['product_size'] ?? null,
                    'product_quantity' => $value['product_quantity'] ?? null,
                    'status' => $value['status'],
                    'created_by' => $value['created_by'],
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        return $id;
    }

    public function process($id_massive_load, $username, $id_user)
    {
        $prev_val = '';
        DB::beginTransaction();
        try {
            /* ACTUALIZAR MASIVE_LOAD */
            DB::table('massive_load')
                ->where('id_massive_load', $id_massive_load)
                ->update([
                    'status' => 'PROCESADO',
                    'modified_by' => $username
                ]);
            
            /* ACTUALIZAR MASIVE_LOAD_DETAILS */
            DB::table('massive_load_details')
                ->where('id_massive_load', $id_massive_load)
                ->update([
                    'status' => 'PROCESADO',
                    'modified_by' => $username
                ]);

            $detalles = DB::table('massive_load_details')->select('*')
                        ->where('id_massive_load', $id_massive_load)
                        ->orderBy('guide_number')
                        ->orderBy('seg_code')
                        ->orderBy('pickup_contact_name')
                        ->orderBy('pickup_document')
                        ->get();
            
            foreach ($detalles as $value) {
                $current_val = join(',',[$value->guide_number, $value->seg_code, $value->pickup_contact_name, $value->pickup_document]);
                if ($current_val !== $prev_val) {
                    /* Validar si existe la dirección registrada, si es asi, utlizar el mismo id */
                    $pickup_add = $this->check_address(
                        $value->pickup_address,
                        $value->pickup_reference,
                        $value->pickup_district,
                        $value->pickup_province,
                        $value->pickup_department,
                        $username
                    );

                    $delivery_add = $this->check_address(
                        $value->delivery_address,
                        $value->delivery_reference,
                        $value->delivery_district,
                        $value->delivery_province,
                        $value->delivery_department,
                        $username
                    );

                    /* Insertamos en la tabla guia solo si las columnas claves son unicas */
                    $id_order = DB::table('order')->insertGetId([
                        'id_user' => $id_user,
                        'id_massive_load' => $id_massive_load,
                        'guide_number' => $value->guide_number,
                        'seg_code' => $value->seg_code,
                        'pickup_contact_name' => $value->pickup_contact_name,
                        'pickup_document_type' => $value->pickup_document_type,
                        'pickup_document' => $value->pickup_document,
                        'pickup_phone' => $value->pickup_phone,
                        'pickup_address_id' => $pickup_add,
                        'pickup_address' => $value->pickup_address,
                        'pickup_reference' => $value->pickup_reference,
                        'pickup_contact_name_2' => $value->pickup_contact_name_2,
                        'pickup_document_type_2' => $value->pickup_document_type_2,
                        'pickup_document_2' => $value->pickup_document_2,
                        'pickup_phone_2' => $value->pickup_phone_2,
                        'pickup_time' => $value->pickup_time,
                        'delivery_contact_name' => $value->delivery_contact_name,
                        'delivery_document_type' => $value->delivery_document_type,
                        'delivery_document' => $value->delivery_document,
                        'delivery_phone' => $value->delivery_phone,
                        'delivery_address_id' => $delivery_add,
                        'delivery_address' => $value->delivery_address,
                        'delivery_reference' => $value->delivery_reference,
                        'delivery_contact_name_2' => $value->delivery_contact_name_2,
                        'delivery_document_type_2' => $value->delivery_document_type_2,
                        'delivery_document_2' => $value->delivery_document_2,
                        'delivery_phone_2' => $value->delivery_phone_2,
                        'delivery_time' => $value->delivery_time,
                        'type' => $value->type,
                        'status' => 'PENDIENTE',
                        'created_by' => $username,
                    ]);

                    DB::table('order_tracking')->insert([
                        ['id_order' => $id_order, 'status' => 'PENDIENTE', 'motive' => 'A la espera de un motorizado.'],
                    ]);
                }

                /* Insertar en sku_producto */
                $id_sku = DB::table('product')->insertGetId([
                    'id_order' => $id_order,
                    'name' => $value->product_name,
                    'description' => $value->product_description,
                    'weight' => $value->product_weight,
                    'size' => $value->product_size,
                    'quantity' => $value->product_quantity,
                    'status' => 'PENDIENTE',
                    'created_by' => $username
                    ]);
                    
                $prev_val = $current_val;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        return $id_massive_load;
    }

    public function check_address($address, $address_reference, $district, $province, $department, $username)
    {
        $check_add = DB::table('address')
            ->whereRaw('LOWER(`address`) = ? ',[trim(strtolower($address))])
            ->where('address', $address)
            ->where('department', $department)
            ->where('district', $district)
            ->where('province', $province)
            ->first();

        $ubgieo = DB::table('ubigeo')
                ->whereRaw('LOWER(TRIM(department)) = ? ', [trim(strtolower($department))])
                ->whereRaw('LOWER(TRIM(province)) = ? ', [trim(strtolower($province))])
                ->whereRaw('LOWER(TRIM(district)) = ? ', [trim(strtolower($district))])
                ->first();

        if (!$check_add) {
            $address_id = DB::table('address')->insertGetId([
                'ubigeo' => trim($ubgieo->ubigeo),
                'address' => $address,
                'address_refernce' => $address_reference,
                'department' => $department,
                'district' => $district,
                'province' => $province,
                'status' => 'ACTIVO',
                'created_by' => $username
            ]);
        } else { 
            $address_id = $check_add->id_address;
        }
        return $address_id;
    }

    // public function get($id)
    // {
    //     return Conductor::find($id);
    // }

    // public function all()
    // {
    //     return Conductor::all();
    // }

    // public function delete($id)
    // {
    //     Conductor::destroy($id);
    // }

    // public function update($id, array $data)
    // {
    //     Conductor::find($id)->update($data);
    // }

    // public function register($input)
    // {
    //     return Conductor::create($input);
    // }
}
