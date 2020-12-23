<?php

namespace App\Models\Repositories\Web;

use App\Exceptions\CustomException;
use App\Models\Entities\Guide;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MassiveLoadRepository
{
    public function get($id)
    {
        return DB::table('massive_load')->where('id_massive_load', $id)->first();
    }

    public function getPropiedad($name)
    {
        return DB::table('properties')->where('name', $name)->first();
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

                $check_ubigeo = DB::table('ubigeo')
                    ->whereRaw('LOWER(department) = ? ', [trim(strtolower($value['department']))])
                    ->whereRaw('LOWER(province) = ? ', [trim(strtolower($value['province']))])
                    ->whereRaw('LOWER(district) = ? ', [trim(strtolower($value['district']))])
                    ->first();
                if (!$check_ubigeo) {
                    Log::error('Ubigeo no encontrado', ['distrito' => $value['district'], 'provincia' => $value['province'], 'departamento' => $value['department'] ]);
                    throw new CustomException(['Error en el departamento, provincia y distrito. (Linea: '.($key+2).' )', 2121], 400);
                }
                
                // if (!array_key_exists('client_barcode', $value) || !isset($value['client_barcode'])) {
                //     $value['client_barcode'] = Str::random(40);
                // }
                // if (!array_key_exists('sku_code', $value) || !isset($value['sku_code'])) {
                //     $value['sku_code'] = Str::random(10);
                // }    (value - (25567 + 1)) * 86400 * 1000

                if (isset($value['client_date']) && !is_string($value['client_date'])) {
                    $value['client_date'] = date('Y-m-d H:i:s', (($value['client_date'] - (25567 + 1)) * 86400));
                }
                if (isset($value['client_date2']) && !is_string($value['client_date2'])) {
                    $value['client_date2'] = date('Y-m-d H:i:s', (($value['client_date2'] - (25567 + 1)) * 86400));
                }

                $value['id_massive_load'] = $id;
                $value['status'] = 'PENDIENTE';
                $value['created_by'] = $data['username'];

                DB::table('massive_load_details')->insert([
                    'id_massive_load' => $value['id_massive_load'] ?? null,
                    'seg_code' => $value['seg_code'] ?? null,
                    'guide_number' => $value['guide_number'] ?? null,
                    'alt_code1' => $value['alt_code1'] ?? null,
                    'alt_code2' => $value['alt_code2'] ?? null,
                    'client_date' => $value['client_date'] ?? null,
                    'client_date2' => $value['client_date2'] ?? null,
                    'client_barcode' => $value['client_barcode'] ?? null,
                    'client_dni' => $value['client_dni'] ?? null,
                    'client_name' => $value['client_name'] ?? null,
                    'client_phone1' => $value['client_phone1'] ?? null,
                    'client_phone2' => $value['client_phone2'] ?? null,
                    'client_phone3' => $value['client_phone3'] ?? null,
                    'client_email' => $value['client_email'] ?? null,
                    'client_address' => $value['client_address'] ?? null,
                    'client_address_reference' => $value['client_address_reference'] ?? null,
                    'coord_latitude' => $value['coord_latitude'] ?? null,
                    'coord_longitude' => $value['coord_longitude'] ?? null,
                    'ubigeo' => $check_ubigeo->ubigeo,
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
                    'created_by' => $value['created_by'] ?? null,
                    'delivery_type' => $value['delivery_type'] ?? null,
                    'contact_name' => $value['contact_name'] ?? null,
                    'contact_phone' => $value['contact_phone'] ?? null
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
        $prev_nguia = '';
        $prev_barcode = '';
        $total_weight = 0;
        $total_pieces = 0;
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
                        ->orderBy('guide_number')
                        ->orderBy('seg_code')
                        // ->orderBy('alt_code1')
                        // ->orderBy('alt_code2')
                        ->orderBy('client_barcode')
                        ->get();
            
            foreach ($detalles as $value) {
                $current_val = join(',',[$value->seg_code, $value->alt_code1, $value->alt_code2, $value->client_barcode]);
                if ($current_val !== $prev_val) {
                    if (isset($id_guide)) {
                        DB::table('guide')->where('id_guide', $id_guide)->update(['total_weight' => $total_weight, 'total_pieces' => $total_pieces]);
                        $total_weight = 0;
                        $total_pieces = 0;
                    }
                    /* Validar si existe la dirección registrada, si es asi, utlizar el mismo id */
                    $check_add = DB::table('address')->whereRaw('LOWER(`address`) = ? ',[trim(strtolower($value->client_address))])->first();
                    if (!$check_add) {
                        $address_id = DB::table('address')->insertGetId([
                            'ubigeo' => $value->ubigeo,
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
                        'guide_number' => $value->guide_number,
                        'id_address' => $address_id,
                        'seg_code' => $value->seg_code,
                        'alt_code1' => $value->alt_code1,
                        'alt_code2' => $value->alt_code2,
                        'client_date' => $value->client_date,
                        'client_date2' => $value->client_date2,
                        'client_barcode' => $value->client_barcode,
                        'client_dni' => $value->client_dni,
                        'client_name' => $value->client_name,
                        'client_phone1' => $value->client_phone1,
                        'client_phone2' => $value->client_phone2,
                        'client_phone3' => $value->client_phone3,
                        'client_email' => $value->client_email,
                        'status' => ($value->status === 'PROCESADO') ? 'PENDIENTE' : 'SIN FISICO',
                        'created_by' => $data['username'],
                        'delivery_type' => $data['delivery_type'],
                        'contact_name' => $data['contact_name'],
                        'contact_phone' => $data['contact_phone']
                    ]);

                    if ($value->status === 'PROCESADO') {
                        DB::table('guide_tracking')->insert([
                            ['id_guide' => $id_guide, 'status' => 'PROCESADO', 'motive' => 'Registro Automático.'],
                            ['id_guide' => $id_guide, 'status' => 'DESPACHADO', 'motive' => 'Registro Automático.'],
                            ['id_guide' => $id_guide, 'status' => 'DESPACHO ACEPTADO', 'motive' => 'Registro Automático.'],
                            ['id_guide' => $id_guide, 'status' => 'PENDIENTE', 'motive' => 'Registro Automático.'],
                        ]);
                    } else {
                        DB::table('guide_tracking')->insert([
                            ['id_guide' => $id_guide, 'status' => 'SIN FISICO', 'motive' => 'Registro Automático.'],
                        ]);
                    }
                }

                if (is_null($value->client_barcode)) {
                    if ($prev_nguia === $value->guide_number) {
                        DB::table('guide')->where('id_guide', $id_guide)->update(['client_barcode' => $prev_barcode]);
                    } else {
                        $client_barcode = Carbon::now()->format('Ymd') . str_pad($id_guide, 7, "0", STR_PAD_LEFT);
                        DB::table('guide')->where('id_guide', $id_guide)->update(['client_barcode' => $client_barcode]);
                        $prev_barcode = $client_barcode;
                        $prev_nguia = $value->guide_number;
                    }
                }

                /* Insertar en sku_producto */
                $id_sku = DB::table('sku_product')->insertGetId([
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
                    
                if (is_null($value->sku_code)) {
                    $v_sku_code =  'SKU' . str_pad($id_sku, 7, "0", STR_PAD_LEFT);
                    DB::table('sku_product')->where('id_sku_product', $id_sku)->update(['sku_code' => $v_sku_code]);
                }

                $total_weight =+ $value->sku_weight;
                $total_pieces =+ $value->sku_pieces;
                $prev_val = $current_val;
            }

            DB::table('guide')->where('id_guide', $id_guide)->update(['total_weight' => $total_weight, 'total_pieces' => $total_pieces]);

            $address = DB::table('guide AS gd')
                    ->select('gd.id_address','add.address', 'add.latitude', 'add.longitude', 'ubi.district')
                    ->distinct()
                    ->join('address AS add','add.id_address','=','gd.id_address')
                    ->join('ubigeo as ubi', 'ubi.ubigeo', '=', 'add.ubigeo')
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

    public function get_datos_ruta_cargo_ripley($id)
    {
        $query = DB::select("select
            gd.guide_number, gd.client_barcode, gd.client_name, gd.client_phone1, gd.client_email, gd.client_dni,
            org.name, org.address as org_address, adr.district, adr.province, adr.address,
            GROUP_CONCAT(gd.seg_code, '-',sku.sku_description) as contenido, ml.date_updated as date_created
        from
            guide gd
        join massive_load ml on ml.id_massive_load = gd.id_massive_load
        join organization as org on org.id_organization = gd.id_organization
        join address as adr on adr.id_address = gd.id_address
        join sku_product as sku on sku.id_guide = gd.id_guide
        where
            gd.id_massive_load = ?
        group by
            gd.client_barcode,
            gd.guide_number,
            gd.client_name,
            gd.client_phone1,
            gd.client_email,
            org.name,
            org.address,
            adr.district,
            adr.province,
            adr.address
        order by adr.district;", [$id]);
        return $query;
    }

    public function get_datos_ruta_cargo_oechsle($id)
    {
        $query = DB::select("select
            gd.guide_number, gd.client_name, gd.client_phone1, gd.client_email, gd.client_dni,
            org.name, org.address as org_address,
            adr.district, adr.province, adr.address,
            GROUP_CONCAT(gd.client_barcode, '-',sku.sku_description) as contenido,
            GROUP_CONCAT(if(gd.delivery_type is null, '',gd.delivery_type), '||',if(gd.contact_name is null, '',gd.contact_name), '||',if(gd.contact_phone is null, '',gd.contact_phone) SEPARATOR ';') as observaciones,
            ml.date_created
        from guide gd
        join massive_load as ml on ml.id_massive_load = gd.id_massive_load
        join sku_product as sku on sku.id_guide = gd.id_guide
        join organization as org on org.id_organization = gd.id_organization
        join address as adr on adr.id_address = gd.id_address
        where
            gd.id_massive_load = ?
        group by
            gd.guide_number,
            gd.client_name,
            gd.client_phone1,
            gd.client_email,
            gd.client_dni,
            org.name,
            org.address,
            adr.district,
            adr.province,
            adr.address
        order by adr.district;", [$id]);
        return $query;
    }

    public function get_motivos()
    {
        return DB::table('motive')->where('estado','No Entregado')->where('starred', 1)->get();
    }


    public function get_doc_ruta_cargo($id)
    {
        return DB::table('massive_load')->where('id_massive_load', $id)->get();
    }

    public function actualizar_doc_ruta($id, $filename)
    {
        DB::table('massive_load')->where('id_massive_load', $id)->update([
            'ruta_doc_cargo' => $filename
        ]);
    }

    public function actualizar_doc_marathon($id, $filename)
    {
        DB::table('massive_load')->where('id_massive_load', $id)->update([
            'ruta_marathon' => $filename
        ]);
    }
}
