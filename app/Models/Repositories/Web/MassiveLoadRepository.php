<?php

namespace App\Models\Repositories\Web;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MassiveLoadRepository
{
    public function get($id)
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
                'created_by' => $data['username'],
                'id_corporation' => $data['id_corporation'],
                'id_organization' => $data['id_organization']
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

    public function process($data)
    {
        $prev_val = '';
        DB::beginTransaction();
        try {
            /* ACTUALIZAR MASIVE_LOAD */
            DB::table('massive_load')
                ->where('id_massive_load', $data['id_massive_load'])
                ->update([
                    'status' => 'PROCESADO',
                    'modified_by' => $data['username']
                ]);
            
            /* ACTUALIZAR MASIVE_LOAD_DETAILS */
            DB::table('massive_load_details')
                ->where('id_massive_load', $data['id_massive_load'])
                ->update([
                    'status' => 'PROCESADO',
                    'modified_by' => $data['username']
                ]);

            DB::table('massive_load_details')
            ->whereIn('id_load_detail', $data['data'])
            ->update([
                'status' => 'SIN FISICO',
                'modified_by' => $data['username']
            ]);

            $detalles = DB::table('massive_load_details')->select('*')
                        ->where('id_massive_load', $data['id_massive_load'])
                        ->orderBy('seg_code')
                        ->orderBy('alt_code1')
                        ->orderBy('alt_code2')
                        ->orderBy('client_barcode')
                        ->get();
            
            foreach ($detalles as $value) {
                $current_val = join(',',[$value->seg_code, $value->alt_code1, $value->alt_code2, $value->client_barcode]);
                if ($current_val !== $prev_val) {
                    /* Validar si existe la dirección registrada, si es asi, utlizar el mismo id */
                    $check_add = DB::table('address')->whereRaw('LOWER(`address`) = ? ',[trim(strtolower($value->client_address))])->first();
                    if (!$check_add) {
                        $address_id = DB::table('address')->insertGetId([
                            'id_ubigeo' => 1,
                            'address' => $value->client_address,
                            'address_refernce' => $value->client_address_reference,
                            'latitude' => $value->coord_latitude,
                            'longitude' => $value->coord_longitude,
                            'latitude_delivery' => null,
                            'longitude_delivery' => null,
                            'department' => $value->department,
                            'district' => $value->district,
                            'province' => $value->province,
                            'status' => 'ACTIVO',
                            'created_by' => $data['username']
                        ]);
                    } else { 
                        $address_id = $check_add->id_address;
                    }

                    /* Insertamos en la tabla guia solo si las columnas claves son unicas */
                    $id_guide = DB::table('guide')->insertGetId([
                        'id_corporation' => $data['id_corporation'],
                        'id_organization' => $data['id_organization'],
                        'id_massive_load' => $data['id_massive_load'],
                        'id_address' => $address_id,
                        'seg_code' => $value->seg_code,
                        'alt_code1' => $value->alt_code1,
                        'alt_code2' => $value->alt_code2,
                        'client_date' => $value->client_date,
                        'client_barcode' => $value->client_barcode,
                        'client_dni' => $value->client_dni,
                        'client_name' => $value->client_name,
                        'client_phone1' => $value->client_phone1,
                        'client_phone2' => $value->client_phone2,
                        'client_phone3' => $value->client_phone3,
                        'client_email' => $value->client_email,
                        'status' => ($value->status === 'PROCESADO') ? 'DESPACHO ACEPTADO' : 'SIN FISICO',
                        'created_by' => $data['username']
                    ]);

                    if ($value->status === 'PROCESADO') {
                        DB::table('guide_tracking')->insert([
                            ['id_guide' => $id_guide, 'status' => 'PROCESADO', 'motive' => 'Registro Automático.'],
                            ['id_guide' => $id_guide, 'status' => 'DESPACHADO', 'motive' => 'Registro Automático.'],
                            ['id_guide' => $id_guide, 'status' => 'DESPACHO ACEPTADO', 'motive' => 'Registro Automático.'],
                        ]);
                    } else {
                        DB::table('guide_tracking')->insert([
                            ['id_guide' => $id_guide, 'status' => 'SIN FISICO', 'motive' => 'Registro Automático.'],
                        ]);
                    }
                }

                /* Insertar en sku_producto */
                DB::table('sku_product')->insert([
                    'id_guide' => $id_guide,
                    'sku_code' => $value->sku_code,
                    'sku_description' => $value->sku_description,
                    'sku_weight' => $value->sku_weight,
                    'sku_pieces' => $value->sku_pieces,
                    'sku_brand' => $value->sku_brand,
                    'sku_size' => $value->sku_size,
                    'box_code' => $value->box_code,
                    'status' => $value->status,
                    'created_by' => $data['username']
                ]);
                $prev_val = $current_val;
            }

            $address = DB::table('guide AS gd')
                    ->select('gd.id_address','add.address', 'add.latitude', 'add.longitude')
                    ->distinct()
                    ->join('address AS add','add.id_address','=','gd.id_address')
                    ->where('gd.id_massive_load', $data['id_massive_load'])
                    ->get();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        return $address;
    }

    public function actualizarCoordenadas($data)
    {
        DB::beginTransaction();
        try {
            foreach ($data as $key => $value) {
                DB::table('address')->where('id_address', $value['id_address'])
                    ->update([
                        'latitude' => $value['latitude'],
                        'longitude' => $value['longitude']
                    ]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();
    }
}
