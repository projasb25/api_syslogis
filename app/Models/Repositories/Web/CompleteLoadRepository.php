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

class CompleteLoadRepository
{
    public function selCargasRecoleccion()
    {
        return DB::table('complete_load as cl')
            ->select('cl.id_organization')
            ->distinct()
            ->where('collect_process', 0)
            ->get();
    }

    public function selDataCargaRecoleccion($orgid)
    {
        return DB::table('complete_load as cl')
            ->select('cl.*', 'cld.*', 'org.description as org_name')
            ->join('organization as org', 'org.id_organization', 'cl.id_organization')
            ->join('complete_load_detail as cld', 'cld.id_complete_load', 'cl.id_complete_load')
            ->where('cl.collect_process', 0)
            ->where('cl.id_organization', $orgid)
            ->get();
    }

    public function insertCompleteLoad($data)
    {
        DB::beginTransaction();
        try {
            $id = DB::table('complete_load')->insertGetId([
                'number_records' => $data['count'],
                'status' => 'PENDIENTE',
                'created_by' => $data['username'],
                'id_corporation' => $data['id_corporation'],
                'id_organization' => $data['id_organization'],
                'id_load_template' => $data['id_load_template'],
                'type' => 'COMPLETA'
            ]);

            foreach ($data['data'] as $key => &$value) {
                $check_collect_ubigeo = DB::table('ubigeo')
                    ->whereRaw('LOWER(TRIM(department)) = ? ', [trim(strtolower($value['collect_department']))])
                    ->whereRaw('LOWER(TRIM(district)) = ? ', [trim(strtolower($value['collect_district']))])
                    ->whereRaw('LOWER(TRIM(province)) = ? ', [trim(strtolower($value['collect_province']))])
                    ->first();

                $check_delivery_ubigeo = DB::table('ubigeo')
                    ->whereRaw('LOWER(TRIM(department)) = ? ', [trim(strtolower($value['delivery_department']))])
                    ->whereRaw('LOWER(TRIM(district)) = ? ', [trim(strtolower($value['delivery_district']))])
                    ->whereRaw('LOWER(TRIM(province)) = ? ', [trim(strtolower($value['delivery_province']))])
                    ->first();

                if (!$check_collect_ubigeo || !$check_delivery_ubigeo) {
                    Log::error('Ubigeo no encontrado', ['value' => $value]);
                    throw new CustomException(['Error en el departamento, provincia y distrito. (Linea: ' . ($key + 2) . ' )', 2121], 400);
                }

                if (isset($value['client_date']) && !is_string($value['client_date'])) {
                    $value['client_date'] = date('Y-m-d H:i:s', (($value['client_date'] - (25567 + 1)) * 86400));
                }
                if (isset($value['client_date2']) && !is_string($value['client_date2'])) {
                    $value['client_date2'] = date('Y-m-d H:i:s', (($value['client_date2'] - (25567 + 1)) * 86400));
                }
                if (!isset($value['client_date'])) {
                    $value['client_date'] = date('Y-m-d H:i:s', time() + 86400);
                }

                $value['id_complete_load'] = $id;
                $value['status'] = 'PENDIENTE';
                $value['created_by'] = $data['username'];
                $value['type'] = 'COMPLETA';

                DB::table('complete_load_detail')->insert([
                    'id_complete_load' => $value['id_complete_load'] ?? null,
                    'seg_code' => $value['seg_code'] ?? null,
                    'guide_number' => $value['guide_number'] ?? null,
                    'client_barcode' => $value['client_barcode'] ?? null,
                    'alt_code1' => $value['alt_code1'] ?? null,
                    'alt_code2' => $value['alt_code2'] ?? null,
                    'sku_code' => $value['sku_code'] ?? null,
                    'sku_description' => $value['sku_description'] ?? null,
                    'sku_weight' => $value['sku_weight'] ?? null,
                    'sku_pieces' => $value['sku_pieces'] ?? null,
                    'seller_name' => $value['seller_name'] ?? null,
                    'status' => $value['status'] ?? null,
                    'type' => $value['type'] ?? null,
                    'created_by' => $value['created_by'] ?? null,
                    'modified_by' => $value['created_by'] ?? null,
                    'sku_vol_weight' => $value['sku_vol_weight'] ?? null,
                    'collect_ubigeo' => $check_collect_ubigeo->ubigeo,
                    'collect_department' => $check_collect_ubigeo->department,
                    'collect_district' => $check_collect_ubigeo->district,
                    'collect_province' => $check_collect_ubigeo->province,
                    'collect_address_reference' => $value['collect_address_reference'] ?? null,
                    'collect_address' => $value['collect_address'] ?? null,
                    'collect_client_dni' => $value['collect_client_dni'] ?? null,
                    'collect_client_name' => $value['collect_client_name'] ?? null,
                    'collect_client_phone1' => $value['collect_client_phone1'] ?? null,
                    'collect_client_phone2' => $value['collect_client_phone2'] ?? null,
                    'collect_client_phone3' => $value['collect_client_phone3'] ?? null,
                    'collect_contact_name' => $value['collect_contact_name'] ?? null,
                    'collect_client_email' => $value['collect_client_email'] ?? null,
                    'delivery_ubigeo' => $check_delivery_ubigeo->ubigeo,
                    'delivery_department' => $check_delivery_ubigeo->department,
                    'delivery_district' => $check_delivery_ubigeo->district,
                    'delivery_province' => $check_delivery_ubigeo->province,
                    'delivery_address_reference' => $value['delivery_address_reference'] ?? null,
                    'delivery_address' => $value['delivery_address'] ?? null,
                    'delivery_client_dni' => $value['delivery_client_dni'] ?? null,
                    'delivery_client_name' => $value['delivery_client_name'] ?? null,
                    'delivery_client_phone1' => $value['delivery_client_phone1'] ?? null,
                    'delivery_client_phone2' => $value['delivery_client_phone2'] ?? null,
                    'delivery_client_phone3' => $value['delivery_client_phone3'] ?? null,
                    'delivery_contact_name' => $value['delivery_contact_name'] ?? null,
                    'delivery_client_email' => $value['delivery_client_email'] ?? null,
                    'client_date' => $value['client_date'] ?? null,
                    'client_date2' => $value['client_date2'] ?? null,
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        return $id;
    }

    public function insertCompleteCollectLoad($data)
    {
        DB::beginTransaction();
        try {
            $id = DB::table('massive_load')->insertGetId([
                'number_records' => count($data),
                'status' => 'PENDIENTE',
                'created_by' => $data[0]->org_name,
                'id_corporation' => $data[0]->id_corporation,
                'id_organization' => $data[0]->id_organization,
                'type' => 'RECOLECCION',
                'complete_load' => 1
            ]);

            foreach ($data as $key => &$value) {
                DB::table('massive_load_details')->insert([
                    'id_massive_load' => $id,
                    'seg_code' => $value->seg_code,
                    'guide_number' => $value->guide_number,
                    'client_barcode' => $value->client_barcode,
                    'alt_code1' => $value->alt_code1,
                    'alt_code2' => $value->alt_code2,
                    'client_date' => $value->client_date,
                    'client_date2' => $value->client_date2,
                    'client_dni' => $value->collect_client_dni,
                    'client_name' => $value->collect_client_name,
                    'client_phone1' => $value->collect_client_phone1,
                    'client_phone2' => $value->collect_client_phone2,
                    'client_phone3' => $value->collect_client_phone3,
                    'client_email' => $value->collect_client_email,
                    'client_address' => $value->collect_address,
                    'client_address_reference' => $value->collect_address_reference,
                    'ubigeo' => $value->collect_ubigeo,
                    'department' => $value->collect_department,
                    'district' => $value->collect_district,
                    'province' => $value->collect_province,
                    'sku_code' => $value->sku_code,
                    'sku_description' => $value->sku_description,
                    'sku_weight' =>  $value->sku_weight,
                    'sku_pieces' =>  $value->sku_pieces,
                    'sku_vol_weight' => $value->sku_vol_weight,
                    'status' => 'PENDIENTE',
                    'created_by' => 'command',
                    'seller_name' => $value->seller_name,
                    'date_loaded' => date('Y-m-d H:i:s'),
                    'id_complete_load_detail' => $value->id_complete_load_detail
                ]);

                DB::table('complete_load')->where('id_complete_load',$value->id_complete_load)->update(['collect_process'=> 1, 'status' => 'PROCESADO']);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        return $id;
    }
}
