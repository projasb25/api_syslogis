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
                    'created_by' => $user->integration_user, 'number_records' => count($data['DetalleProductos'])
                ]
            );

            $idOriginal = 'RP' . Carbon::now()->format('Ymd') . str_pad($id, 6, "0", STR_PAD_LEFT);

            foreach ($data['DetalleProductos'] as $value) {
                $ubigeo_recoleccion = DB::table('ubigeo')->where('ubigeo', $data['UbigeoOrigen'])->first();
                $ubigeo_dist = DB::table('ubigeo')->where('ubigeo', $data['UbigeoDestino'])->first();
                
                DB::table('integration_data_detail')->insert(
                    [
                        'id_integration_data' => $id,
                        'status' => 'PENDIENTE',
                        'created_by' => $user->integration_user,
                        'seg_code' => $data['CodCliente'],
                        'guide_number' => $idOriginal,
                        'client_barcode' => $idOriginal,
                        'sku_code' => $value['codigoProducto'],
                        'sku_description' => $value['descripcionProducto'],
                        'sku_weight' => $value['pesoProducto'],
                        'sku_pieces' => $value['CantidadProducto'],
                        'collect_department' => $ubigeo_recoleccion->department,
                        'collect_district' => $ubigeo_recoleccion->district,
                        'collect_province' => $ubigeo_recoleccion->province,
                        'collect_address_reference' => $data['DireccionReferencialRemitente'],
                        'collect_address' => $data['DireccionRemitente'],
                        'collect_client_dni' => $data['NroDocumentoRemitente'],
                        'collect_client_name' => $data['NombreRemitente'],
                        'collect_client_phone1' => $data['TelefonoRemitente'],
                        'collect_contact_name' => $data['ContactoRemitente'],
                        'collect_client_email' => $data['CorreoRemitente'],
                        'delivery_department' => $ubigeo_dist->department,
                        'delivery_district' => $ubigeo_dist->district,
                        'delivery_province' => $ubigeo_dist->province,
                        'delivery_address_reference' => $data['DireccionReferencialDestinatario'],
                        'delivery_address' => $data['DireccionDestinatario'],
                        'delivery_client_dni' => $data['NroDocumentoDestinatario'],
                        'delivery_client_name' => $data['NombreDestinatario'],
                        'delivery_client_phone1' => $data['TelefonoDestinatario'],
                        'delivery_contact_name' => $data['ContactoDestinatario'],
                        'delivery_contact_email' => $data['CorreoDestinatario']
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
