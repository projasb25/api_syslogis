<?php

namespace App\Models\Repositories\Web;

use App\Exceptions\CustomException;
use App\Models\Entities\Guide;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Helpers\ArrayHelper;

class MassiveLoadRepository
{
    public function get($id)
    {
        return DB::table('massive_load')->where('id_massive_load', $id)->first();
    }

    public function getGuide($id)
    {
        return DB::table('guide')->where('id_guide', $id)->first();
    }

    public function getPropiedad($name)
    {
        return DB::table('properties')->where('name', $name)->first();
    }

    public function execute_store($sp_name, $data_bidnings)
    {
        return DB::select($sp_name,$data_bidnings);
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
                'id_organization' => $data['id_organization'],
                'id_load_template' => $data['id_load_template'],
                'id_subsidiary' => $data['id_subsidiary'],
                'type' => 'DISTRIBUCION'
            ]);

            foreach ($data['data'] as $key => &$value) {
                switch ($data['id_load_template']) {
                    case 68:
                    case 72:
                        $value['department'] = 'LIMA';
                        $value['province'] = '';
                        $check_ubigeo = DB::table('ubigeo')
                            ->where(function ($query) {
                                $query->whereRaw('LOWER(TRIM(department)) = ? ', [trim(strtolower('LIMA'))])
                                    ->orWhereRaw('LOWER(TRIM(department)) = ? ', [trim(strtolower('CALLAO'))]);
                            })
                            ->whereRaw('LOWER(TRIM(district)) = ? ', [trim(strtolower($value['district']))])
                            ->first();

                        $client_name = DB::table('tambo_address')
                            ->whereRaw('LOWER(TRIM(address)) = ? ', [trim(strtolower($value['client_address']))])
                            ->first();
                        $value['client_name'] = $client_name->tambo_name;
                        break;
                    case 71:
                        $value['department'] = '';
                        $value['province'] = '';
                        $value['district'] = $value['client_address'];
                        $check_ubigeo = DB::table('tambo_address')
                            ->whereRaw('LOWER(TRIM(tambo_name)) = ? ', [trim(strtolower($value['client_address']))])
                            ->first();
                        break;
                    case 82:
                        Log::info('aca');
                        $value['department'] = '';
                        $value['province'] = '';
                        $value['district'] = '';
                        $check_ubigeo = DB::table('master_ripley')
                        ->whereRaw('LOWER(TRIM(ripley_name)) = ? ', [trim(strtolower($value['client_name']))])
                        ->first();
                        Log::info('data => ', ['check_ubigeo' => (array) $check_ubigeo]);
                        break;
                    default:
                        $check_ubigeo = DB::table('ubigeo')
                            ->whereRaw('LOWER(TRIM(department)) = ? ', [trim(strtolower($value['department']))])
                            ->whereRaw('LOWER(TRIM(province)) = ? ', [trim(strtolower($value['province']))])
                            ->whereRaw('LOWER(TRIM(district)) = ? ', [trim(strtolower($value['district']))])
                            ->first();
                        break;
                }

                if (!$check_ubigeo) {
                    Log::error('Ubigeo no encontrado', ['distrito' => $value['district'], 'provincia' => $value['province'], 'departamento' => $value['department'] ]);
                    throw new CustomException(['Error en el departamento, provincia y distrito. (Linea: '.($key+2).' )', 2121], 400);
                }

                if ($data['id_load_template'] == 71) {
                    $value['client_address'] = $check_ubigeo->address;
                }

                if ($data['id_load_template'] == 82) {
                    $value['client_address'] = $check_ubigeo->ripley_address;
                    $value['client_dni'] = $check_ubigeo->document;
                    $value['client_email'] = $check_ubigeo->email;
                    $value['client_phone1'] = $check_ubigeo->phone;
                    $value['contact_name'] = $check_ubigeo->contact_name;
                    $value['collect_time_range'] = $check_ubigeo->collect_date;
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
                if (!isset($value['client_date'])) {
                    $value['client_date'] = date('Y-m-d H:i:s', time() + 86400);
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
                    'department' => $check_ubigeo->department,
                    'district' => $check_ubigeo->district,
                    'province' => $check_ubigeo->province,
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
                    'contact_phone' => $value['contact_phone'] ?? null,
                    'payment_method' => $value['payment_method'] ?? null,
                    'amount' => $value['amount'] ?? null,
                    'collect_time_range' => $value['collect_time_range'] ?? null,
                    'date_loaded' => $data['date_loaded'],
                    'id_subsidiary' => $data['id_subsidiary'],
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
                    $check_add = DB::table('address')
                        ->whereRaw('LOWER(`address`) = ? ',[trim(strtolower($value->client_address))])
                        ->whereRaw('LOWER(`district`) = ?', [trim(strtolower($value->district))])
                        ->whereRaw('LOWER(`department`) = ?', [trim(strtolower($value->department))])
                        ->whereRaw('LOWER(`province`) = ?', [trim(strtolower($value->province))])
                        ->first();

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
                        'delivery_type' => $value->delivery_type,
                        'contact_name' => $value->contact_name,
                        'contact_phone' => $value->contact_phone,
                        'collect_time_range' => $value->collect_time_range,
                        'collect_contact_name' => $value->collect_contact_name,
                        'type' => 'DISTRIBUCION',
                        'payment_method' => $value->payment_method,
                        'amount' => $value->amount,
                        'seller_name' => $value->seller_name,
                        'date_loaded' => $value->date_loaded,
                        'client_info' => $value->client_info,
                        'id_subsidiary' => $value->id_subsidiary,
                        'client_address_reference' => $value->client_address_reference
                    ]);

                    DB::table('massive_load_details')->where('id_load_detail', $value->id_load_detail)->update(['id_guide' => $id_guide]);

                    if ($value->status === 'PROCESADO') {
                        DB::table('guide_tracking')->insert([
                            ['id_guide' => $id_guide, 'status' => 'PROCESADO', 'motive' => 'Registro Automático.', 'type' => 'DISTRIBUCION', 'date_created' => $value->date_loaded],
                            ['id_guide' => $id_guide, 'status' => 'DESPACHADO', 'motive' => 'Registro Automático.', 'type' => 'DISTRIBUCION', 'date_created' => $value->date_loaded],
                            ['id_guide' => $id_guide, 'status' => 'DESPACHO ACEPTADO', 'motive' => 'Registro Automático.', 'type' => 'DISTRIBUCION', 'date_created' => $value->date_loaded],
                            ['id_guide' => $id_guide, 'status' => 'PENDIENTE', 'motive' => 'Registro Automático.', 'type' => 'DISTRIBUCION', 'date_created' => $value->date_loaded],
                        ]);
                    } else {
                        DB::table('guide_tracking')->insert([
                            ['id_guide' => $id_guide, 'status' => 'SIN FISICO', 'motive' => 'Registro Automático.', 'type' => 'DISTRIBUCION'],
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
                    'created_by' => $data['username'],
                    'sku_vol_weight' => $value->sku_vol_weight
                    ]);

                if (is_null($value->sku_code)) {
                    $v_sku_code =  'SKU' . str_pad($id_sku, 7, "0", STR_PAD_LEFT);
                    DB::table('sku_product')->where('id_sku_product', $id_sku)->update(['sku_code' => $v_sku_code]);
                }

                // Actualizar el id de la guia en la carga masiva
                DB::table('massive_load_details')->where('id_load_detail', $value->id_load_detail)->update(['guideid' => $id_guide]);

                $total_weight += $value->sku_weight;
                $total_pieces += $value->sku_pieces;
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
            gd.id_organization, gd.date_loaded, gd.delivery_type,
            gd.guide_number, gd.client_barcode, gd.client_name, gd.client_phone1, gd.client_email, gd.client_dni, gd.type, gd.alt_code1,
            gd.alt_code2, gd.collect_time_range, gd.collect_date_range, gd.contact_name, gd.client_date, gd.amount, gd.payment_method,
            org.name, org.address as org_address, adr.district, adr.province, adr.address, adr.address_refernce, adr.department,
            GROUP_CONCAT(gd.seg_code, '-',sku.sku_description) as contenido, ml.date_updated as date_created,
            GROUP_CONCAT(sku.sku_code,'-', sku.sku_description) as contenido2,
            gd.total_pieces, gd.total_weight, gd.client_address_reference
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
            gd.alt_code1,
            gd.alt_code2,
            gd.total_pieces,
            gd.total_weight,
            org.name,
            org.address,
            adr.district,
            adr.province,
            adr.address
        order by adr.district;", [$id]);
        return $query;
    }

    public function get_datos_ruta_cargo_ripley_guide($id_massive_load, $id_guide)
    {
        $query = DB::select("select
            gd.id_organization, gd.date_loaded, gd.delivery_type,
            gd.guide_number, gd.client_barcode, gd.client_name, gd.client_phone1, gd.client_email, gd.client_dni, gd.type, gd.alt_code1,
            gd.collect_time_range, gd.contact_name, gd.client_date, gd.amount, gd.payment_method,
            org.name, org.address as org_address, adr.district, adr.province, adr.address, adr.address_refernce, adr.department,
            GROUP_CONCAT(gd.seg_code, '-',sku.sku_description) as contenido, ml.date_updated as date_created,
            gd.total_pieces, gd.total_weight, gd.client_address_reference
        from
            guide gd
        join massive_load ml on ml.id_massive_load = gd.id_massive_load
        join organization as org on org.id_organization = gd.id_organization
        join address as adr on adr.id_address = gd.id_address
        join sku_product as sku on sku.id_guide = gd.id_guide
        where
            gd.id_massive_load = ? and
            gd.id_guide = ?
        group by
            gd.client_barcode,
            gd.guide_number,
            gd.client_name,
            gd.client_phone1,
            gd.client_email,
            gd.alt_code1,
            gd.total_pieces,
            gd.total_weight,
            org.name,
            org.address,
            adr.district,
            adr.province,
            adr.address
        order by adr.district;", [$id_massive_load, $id_guide]);
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

    public function get_datos_ruta_cargo_oechsle_guide($id_massive_load, $id_guide)
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
            gd.id_massive_load = ? and
            gd.id_guide = ?
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
        order by adr.district;", [$id_massive_load, $id_guide]);
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

    public function publicoInsertarCarga($data)
    {
        DB::beginTransaction();
        try {
            $id = DB::table('massive_load')->insertGetId([
                'number_records' => count($data),
                'status' => 'PENDIENTE',
                'created_by' => 'Integracion',
                'id_corporation' => 5,
                'id_organization' => 15
            ]);

            foreach ($data as $key => &$value) {

                // Check informacion de ubigeo
                $check_ubigeo = DB::table('ubigeo')
                    ->whereRaw('LOWER(TRIM(department)) = ? ', [trim(strtolower($value['department']))])
                    ->whereRaw('LOWER(TRIM(province)) = ? ', [trim(strtolower($value['province']))])
                    ->whereRaw('LOWER(TRIM(district)) = ? ', [trim(strtolower($value['district']))])
                    ->first();
                if (!$check_ubigeo) {
                    Log::error('Ubigeo no encontrado', ['distrito' => $value['district'], 'provincia' => $value['province'], 'departamento' => $value['department'] ]);
                    throw new CustomException(['Error en el departamento, provincia y distrito. (Linea: '.($key+2).' )', 2121], 400);
                }

                $value['id_massive_load'] = $id;
                $value['status'] = 'PENDIENTE';
                $value['created_by'] = 'Integracion';

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
                    // 'delivery_type' => $value['delivery_type'] ?? null,
                    // 'contact_name' => $value['contact_name'] ?? null,
                    // 'contact_phone' => $value['contact_phone'] ?? null
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        return $id;
    }

    public function get_datos_ripley_reversa($id_massive_load)
    {
        $query = DB::select("select
            gd.seller_name,
            adr.address,
            adr.address_refernce,
            adr.district,
            adr.department,
            adr.province,
            gd.client_phone1,
            gd.client_name,
            gd.client_barcode,
            gd.seg_code,
            gd.guide_number,
            GROUP_CONCAT(sp.sku_description) as sku_description,
            GROUP_CONCAT(sp.sku_pieces) as sku_pieces,
            gd.client_info
        from guide gd
        join address adr on adr.id_address = gd.id_address
        join sku_product sp on sp.id_guide = gd.id_guide
        where
            gd.id_massive_load = ?
        order by adr.district", [$id_massive_load]);
        return $query;
    }

    public function get_datos_ripley_reversa_guide($id_massive_load, $id_guide)
    {
        $query = DB::select("select
            gd.seller_name,
            adr.address,
            adr.address_refernce,
            adr.district,
            adr.department,
            adr.province,
            gd.client_phone1,
            gd.client_name,
            gd.client_barcode,
            gd.seg_code,
            gd.guide_number,
            GROUP_CONCAT(sp.sku_description) as sku_description,
            GROUP_CONCAT(sp.sku_pieces) as sku_pieces,
            gd.client_info
        from guide gd
        join address adr on adr.id_address = gd.id_address
        join sku_product sp on sp.id_guide = gd.id_guide
        where
            gd.id_massive_load = ? and
            gd.id_guide = ?
        order by adr.district", [$id_massive_load, $id_guide]);
        return $query;
    }

    public function get_datos_ripley_seller($ripley_name)
    {
        return DB::table('master_ripley')->where('ripley_name', $ripley_name)->first();
    }

    public function get_datos_ruta_cargo_ripley_seller($id)
    {
        $query = DB::select("select
            gd.id_organization, gd.date_loaded,
            gd.guide_number, gd.client_barcode, gd.client_name, gd.client_phone1, gd.client_email, gd.client_dni, gd.type, gd.alt_code1,
            gd.collect_time_range, gd.contact_name, gd.client_date, gd.amount, gd.payment_method,
            org.name, org.address as org_address, adr.district, adr.province, adr.address, adr.address_refernce, adr.department,
            GROUP_CONCAT(gd.seg_code, '-',sku.sku_description) as contenido, ml.date_updated as date_created,
            gd.total_pieces, gd.total_weight, mr.ripley_name, mr.address as raddress, mr.district as rdistrict
        from
            guide gd
        join massive_load ml on ml.id_massive_load = gd.id_massive_load
        join organization as org on org.id_organization = gd.id_organization
        join address as adr on adr.id_address = gd.id_address
        join sku_product as sku on sku.id_guide = gd.id_guide
        join master_ripley mr on mr.ripley_name = gd.client_name
        where
            gd.id_massive_load = ?
        group by
            gd.client_barcode,
            gd.guide_number,
            gd.client_name,
            gd.client_phone1,
            gd.client_email,
            gd.alt_code1,
            gd.total_pieces,
            gd.total_weight,
            org.name,
            org.address,
            adr.district,
            adr.province,
            adr.address
        order by adr.district;", [$id]);
        return $query;
    }
}
