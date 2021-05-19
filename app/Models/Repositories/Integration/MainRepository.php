<?php

namespace App\Models\Repositories\Integration;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MainRepository
{
    public function getIntegrationData()
    {
        $query = DB::table('integration_data as id')
            ->join('integration_data_detail as idd','idd.id_integration_data','=','id.id_integration_data')
            ->where('id.status', 'PENDIENTE')
            ->get();
        return $query;
    }

    public function getGuidesCollected()
    {
        $query = DB::table('guide as gd')
            ->join('integration_data_detail as idd','idd.guide_number','=','gd.guide_number')
            ->where('gd.type','RECOLECCION')
            ->whereIn('gd.status', ['RECOLECCION COMPLETA'])
            ->where('gd.proc_integracion',1)
            ->get();
        return $query;
    }

    public function insertData($data, $user)
    {
        DB::beginTransaction();
        try {
            $id = DB::table('integration_data')->insertGetId(
                [
                    'id_integration_user' => $user->id_integration_user, 'id_corporation' => $user->id_corporation,
                    'id_organization' => $user->id_organization, 'request_data' => json_encode($data), 'status' => 'PENDIENTE',
                    'created_by' => $user->integration_user, 'number_records' => count($data['items'])
                ]
            );

            $idOriginal = 'RP' . Carbon::now()->format('Ymd') . str_pad($id, 6, "0", STR_PAD_LEFT);

            foreach ($data['items'] as $value) {
                $ubigeo_recoleccion = DB::table('ubigeo')->where('ubigeo', $data['sellerUbigeo'])->first();
                $ubigeo_dist = DB::table('ubigeo')->where('ubigeo', $data['clientUbigeo'])->first();

                DB::table('integration_data_detail')->insert(
                    [
                        'id_integration_data' => $id,
                        'status' => 'PENDIENTE',
                        'created_by' => $user->integration_user,
                        'seg_code' => $data['orderNumber'],
                        'guide_number' => $idOriginal,
                        'client_barcode' => $idOriginal,
                        'alt_code1' => $data['marketplaceId'],
                        'sku_code' => $value['id'],
                        'sku_description' => $value['description'],
                        'sku_weight' => $value['weight'],
                        'sku_pieces' => $value['quantity'],
                        'collect_ubigeo' => $data['sellerUbigeo'],
                        'collect_department' => $ubigeo_recoleccion->department,
                        'collect_district' => $ubigeo_recoleccion->district,
                        'collect_province' => $ubigeo_recoleccion->province,
                        'collect_address_reference' => null,
                        'collect_address' => $data['sellerAddress'],
                        'collect_client_dni' => $data['sellerCorporateDocument'],
                        'collect_client_name' => $data['sellerCorporateName'],
                        'collect_client_phone1' => $data['sellerPhone'],
                        'collect_contact_name' => null,
                        'collect_client_email' => $data['sellerEmail'],
                        'delivery_ubigeo' => $data['clientUbigeo'],
                        'delivery_department' => $ubigeo_dist->department,
                        'delivery_district' => $ubigeo_dist->district,
                        'delivery_province' => $ubigeo_dist->province,
                        'delivery_address_reference' => $data['clientAddressReference'],
                        'delivery_address' => $data['clientAddressStreet'],
                        'delivery_client_dni' => $data['clientDocument'],
                        'delivery_client_name' => $data['clientFirstName'] . ' ' . $data['clientLastName'],
                        'delivery_client_phone1' => $data['clientPhone'],
                        'delivery_contact_name' => $data['clientFirstName'] . ' ' . $data['clientLastName'],
                        'delivery_contact_email' => $data['clientEmail']
                    ]
                );
            }
        } catch (Exception $e) {
            Log::warning("Actualizar estado conductor " . $e->getMessage());
            DB::rollback();
        }
        DB::commit();
        return $idOriginal;
    }

    public function insertMassiveLoad($data)
    {
        DB::beginTransaction();
        try {
            $id = DB::table('massive_load')->insertGetId([
                'number_records' => count($data),
                'status' => 'PENDIENTE',
                'created_by' => 'integracion',
                'id_corporation' => $data[0]->id_corporation,
                'id_organization' => $data[0]->id_organization,
                'type' => 'RECOLECCION',
                'proc_integracion' => 1
            ]);

            foreach ($data as $key => &$value) {
                DB::table('massive_load_details')->insert([
                    'id_massive_load' => $id,
                    'seg_code' => $value->seg_code,
                    'guide_number' => $value->guide_number,
                    'alt_code1' => $value->alt_code1,
                    'alt_code2' => $value->alt_code2,
                    // 'client_date' => $value['client_date'] ?? null,
                    // 'client_date2' => $value['client_date2'] ?? null,
                    'client_barcode' => $value->client_barcode,
                    'client_dni' => $value->collect_client_dni,
                    'client_name' => $value->collect_client_name,
                    'client_phone1' => $value->collect_client_phone1,
                    'client_phone2' => $value->collect_client_phone2,
                    'client_phone3' => $value->collect_client_phone3,
                    'client_email' => $value->collect_client_email,
                    'client_address' => $value->collect_address,
                    'client_address_reference' => $value->collect_address_reference,
                    // 'coord_latitude' => $value['coord_latitude'] ?? null,
                    // 'coord_longitude' => $value['coord_longitude'] ?? null,
                    'ubigeo' => $value->collect_ubigeo,
                    'department' => $value->collect_department,
                    'district' => $value->collect_district,
                    'province' => $value->collect_province,
                    'sku_code' => $value->sku_code,
                    'sku_description' => $value->sku_description,
                    'sku_weight' =>  $value->sku_weight,
                    'sku_pieces' =>  $value->sku_pieces,
                    // 'sku_brand' => $value['sku_brand'] ?? null,
                    // 'sku_size' => $value['sku_size'] ?? null,
                    // 'box_code' => $value['box_code'] ?? null,
                    'status' => 'PENDIENTE',
                    'created_by' => 'integracion',
                    // 'delivery_type' => $value['delivery_type'] ?? null,
                    // 'contact_name' => $value['contact_name'] ?? null,
                    // 'contact_phone' => $value['contact_phone'] ?? null,
                    // 'payment_method' => $value['payment_method'] ?? null,
                    // 'amount' => $value['amount'] ?? null,
                    // 'collect_time_range' => $value['collect_time_range'] ?? null,
                ]);

                DB::table('integration_data')->where('id_integration_data',$value->id_integration_data)->update(['status'=>'PROCESADO']);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        return $id;
    }
}
