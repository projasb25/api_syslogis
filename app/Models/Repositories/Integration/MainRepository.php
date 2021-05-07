<?php

namespace App\Models\Repositories\Integration;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MainRepository
{
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
                        'sku_code' => $value['id'],
                        'sku_description' => $value['description'],
                        'sku_weight' => $value['weight'],
                        'sku_pieces' => $value['quantity'],
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
                        'delivery_department' => $ubigeo_dist->department,
                        'delivery_district' => $ubigeo_dist->district,
                        'delivery_province' => $ubigeo_dist->province,
                        'delivery_address_reference' => $data['clientAddressReference'],
                        'delivery_address' => $data['clientAddressStreet'],
                        'delivery_client_dni' => $data['clientDocument'],
                        'delivery_client_name' => $data['clientFirstName']. ' '. $data['clientLastName'],
                        'delivery_client_phone1' => $data['clientPhone'],
                        'delivery_contact_name' => $data['clientFirstName']. ' '. $data['clientLastName'],
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
}
