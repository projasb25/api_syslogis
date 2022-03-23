<?php

namespace App\Models\Repositories\Integration;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MainRepository
{
    public function getGuideFromIntegration($guide_number, $user)
    {
        $query = DB::table('guide as gd')
            ->where('gd.guide_number', $guide_number)
            ->where('gd.id_organization', $user->id_organization)
            ->orderBy('gd.id_guide','desc')
            ->first();
        return $query;
    }

    public function getProductInfo($id_guide)
    {
        $query = DB::table('sku_product as sp')
            ->where('sp.id_guide',$id_guide)
            ->get();
        return $query;
    }

    public function getTrackingInfo($id_guide)
    {
        $query = DB::table('guide_tracking as gt')
            ->where('gt.id_guide', $id_guide)
            ->whereIn('gt.status',['PENDIENTE', 'ASIGNADO', 'CURSO', 'RECOLECCION COMPLETA', 'ENTREGADO', 'NO ENTREGADO', 'NO RECOLECTADO'])
            ->where('gt.attempt','>=',0)
            ->orderBy('gt.id_guide_tracking')
            ->get();
        return $query;
    }

    public function getLoadDataByGuide($guide_number, $user)
    {
        $query = DB::table('load_integration_detail as lid')
            ->join('load_integration as li','lid.id_load_integration', '=', 'li.id_load_integration')
            ->where('lid.guide_number', $guide_number)
            ->where('li.id_organization', $user->id_organization)
            ->get();
        return $query;
    }

    public function getIntegrationData()
    {
        $query = DB::table('integration_data as id')
            ->join('integration_data_detail as idd','idd.id_integration_data','=','id.id_integration_data')
            ->where(function($q) {
                $q->where('id.type','like','Envío a domicilio%')
                  ->orWhere('id.type', 'like', '%Retiro en tienda%');
            })
            // ->where('id.type','like','Envío a domicilio%')
            // ->orWhere('id.type', 'like', '%Retiro en tienda%')
            ->where('id.status', 'PENDIENTE')
            // ->whereIn('idd.delivery_department',['LIMA','CALLAO'])
            ->get();
        return $query;
    }

    public function InRetail_getCollectData($type)
    {
        $query = DB::table('integration_data as id')
            ->join('integration_data_detail as idd','idd.id_integration_data','=','id.id_integration_data')
            ->where('id.type','like',$type)
            ->where('id.status', 'PENDIENTE')
            ->get();
        return $query;
    }

    public function InRetail_getDistinctTypes()
    {
        $query = DB::table('integration_data as id')
            ->select('distinct(id.type)')
            ->where('id.status', 'PENDIENTE')
            ->get();
        return $query;
    }

    public function getLoadIntegration()
    {
        $query = DB::table('load_integration as li')
            ->join('load_integration_detail as lid','lid.id_load_integration','=','li.id_load_integration')
            ->where('li.status', 'PENDIENTE')
            ->where('id_organization', 68)
            // ->whereIn('idd.delivery_department',['LIMA','CALLAO'])
            ->get();
        return $query;
    }

    public function getIntegrationDataProvincia()
    {
        $query = DB::table('integration_data as id')
            ->join('integration_data_detail as idd','idd.id_integration_data','=','id.id_integration_data')
            ->where('id.type','Envío a domicilio')
            ->where('id.status', 'PENDIENTE')
            ->whereNotIn('idd.delivery_department',['LIMA','CALLAO'])
            ->get();
        return $query;
    }

    public function getIntegrationDataExpress()
    {
        $query = DB::table('integration_data as id')
            ->join('integration_data_detail as idd','idd.id_integration_data','=','id.id_integration_data')
            ->where('id.status', 'PENDIENTE')
            ->where('id.id_organization',58)
            ->get();
        return $query;
    }

    // public function getGuidesCollected()
    // {
    //     $query = DB::table('guide as gd')
    //         ->join('integration_data_detail as idd','idd.guide_number','=','gd.guide_number')
    //         ->where('gd.type','RECOLECCION')
    //         ->whereIn('gd.status', ['RECOLECCION COMPLETA', 'RECOLECCION PARCIAL'])
    //         ->where('gd.proc_integracion',1)
    //         ->whereIn('idd.delivery_department',['LIMA','CALLAO'])
    //         ->get();
    //     return $query;
    // }

    public function InRetail_getGuidesCollectedByType($type)
    {
        $query = DB::table('guide as gd')
            ->join('integration_data_detail as idd','idd.guide_number','=','gd.guide_number')
            ->where('gd.type','RECOLECCION')
            ->whereIn('gd.status', ['RECOLECCION COMPLETA', 'RECOLECCION PARCIAL'])
            ->where('gd.proc_integracion',1)
            ->whereIn('idd.delivery_department',['LIMA','CALLAO'])
            ->where('gd.delivery_type', $type)
            ->get();
        return $query;
    }

    public function InRetail_getCollectedGuidesTypes()
    {
        $query = DB::table('guide as gd')
            ->select('distinct(gd.delivery_type)')
            ->join('integration_data_detail as idd','idd.guide_number','=','gd.guide_number')
            ->where('gd.type','RECOLECCION')
            ->whereIn('gd.status', ['RECOLECCION COMPLETA', 'RECOLECCION PARCIAL'])
            ->where('gd.proc_integracion',1)
            ->whereIn('idd.delivery_department',['LIMA','CALLAO'])
            ->get();
        return $query;
    }

    public function getGuidesCollectedIntegration()
    {
        $query = DB::table('guide as gd')
            ->join('load_integration_detail as lid','lid.guide_number','=','gd.guide_number')
            ->where('gd.type','RECOLECCION')
            ->whereIn('gd.status', ['RECOLECCION COMPLETA', 'RECOLECCION PARCIAL'])
            ->where('gd.integracion',1)
            ->get();
        return $query;
    }

    public function getGuidesCollectedExpress()
    {
        $query = DB::table('guide as gd')
            ->join('integration_data_detail as idd','idd.guide_number','=','gd.guide_number')
            ->where('gd.type','RECOLECCION')
            ->whereIn('gd.status', ['RECOLECCION COMPLETA', 'RECOLECCION PARCIAL'])
            ->where('gd.proc_integracion',1)
            ->where('gd.id_organization',58)
            ->get();
        return $query;
    }

    public function getGuidesCollectedProvince()
    {
        $query = DB::table('guide as gd')
            ->join('integration_data_detail as idd','idd.guide_number','=','gd.guide_number')
            ->where('gd.type','RECOLECCION')
            ->whereIn('gd.status', ['RECOLECCION COMPLETA', 'RECOLECCION PARCIAL'])
            ->where('gd.proc_integracion',1)
            ->whereNotIn('idd.delivery_department',['LIMA','CALLAO'])
            ->get();
        return $query;
    }

    public function getDataToReport()
    {
        $query = DB::table('integration_data as id')
            ->select('id.id_integration_data','id.request_data','id.reportado','idd.guide_number','idd.alt_code1', 'idd.seg_code', 'idd.seller_name')
            ->join('integration_data_detail as idd','idd.id_integration_data','=','id.id_integration_data')
            ->where('id.reportado',0)
            ->groupBy('id.id_integration_data')
            ->groupBy('idd.alt_code1')
            ->groupBy('idd.guide_number')
            ->groupBy('idd.seg_code')
            ->groupBy('idd.seller_name')
            ->get();
        return $query;
    }

    public function updateReportado($id_carga, $report)
    {
        DB::table('integration_data')->where('id_integration_data', $id_carga)->update(['reportado' => $report]);
    }

    public function insertData($data, $user)
    {
        DB::beginTransaction();
        try {
            $id = DB::table('integration_data')->insertGetId(
                [
                    'id_integration_user' => $user->id_integration_user, 'id_corporation' => $user->id_corporation,
                    'id_organization' => $user->id_organization, 'request_data' => json_encode($data), 'status' => 'PENDIENTE',
                    'created_by' => $user->integration_user, 'number_records' => count($data['items']),
                    'type' => $data['selectedSla']
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
                        'delivery_address' => $data['clientAddressStreet'] . ' ' . $data['clientAddressNumber'] . ' ' . $data['clientAddressComplement'],
                        'delivery_client_dni' => $data['clientDocument'],
                        'delivery_client_name' => $data['clientFirstName'] . ' ' . $data['clientLastName'],
                        'delivery_client_phone1' => $data['clientPhone'],
                        'delivery_contact_name' => $data['clientFirstName'] . ' ' . $data['clientLastName'],
                        'delivery_contact_email' => $data['clientEmail'],
                        'seller_name' =>  $data['sellerCorporateName']
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

    public function insertMassiveLoad($data, $type)
    {
        DB::beginTransaction();
        try {
            $id = DB::table('massive_load')->insertGetId([
                'number_records' => count($data),
                'status' => 'PENDIENTE',
                'created_by' => 'InRetail '.$type,
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
                    'client_date' => date('Y-m-d H:i:s', time() + 86400),
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
                    'created_by' => 'InRetail '.$type,
                    // 'delivery_type' => $value['delivery_type'] ?? null,
                    // 'contact_name' => $value['contact_name'] ?? null,
                    // 'contact_phone' => $value['contact_phone'] ?? null,
                    // 'payment_method' => $value['payment_method'] ?? null,
                    // 'amount' => $value['amount'] ?? null,
                    // 'collect_time_range' => $value['collect_time_range'] ?? null,
                    'seller_name' => $value->seller_name,
                    'date_loaded' => date('Y-m-d H:i:s'),
                    'delivery_type' => $type
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

    public function insertMassiveLoadIntegration($data)
    {
        DB::beginTransaction();
        try {
            $id = DB::table('massive_load')->insertGetId([
                'number_records' => count($data),
                'status' => 'PENDIENTE',
                'created_by' => 'enviame',
                'id_corporation' => $data[0]->id_corporation,
                'id_organization' => $data[0]->id_organization,
                'type' => 'RECOLECCION',
                'integracion' => 1
            ]);

            foreach ($data as $key => &$value) {
                DB::table('massive_load_details')->insert([
                    'id_massive_load' => $id,
                    'seg_code' => $value->seg_code,
                    'guide_number' => $value->guide_number,
                    'alt_code1' => $value->alt_code1,
                    'alt_code2' => $value->alt_code2,
                    'client_date' => date('Y-m-d H:i:s', time() + 86400),
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
                    'seller_name' => $value->seller_name,
                    'date_loaded' => date('Y-m-d H:i:s')
                ]);

                DB::table('load_integration')->where('id_load_integration',$value->id_load_integration)->update(['status'=>'PROCESADO']);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        return $id;
    }

    public function insertMassiveLoadDist($data, $type)
    {
        DB::beginTransaction();
        try {
            $id = DB::table('massive_load')->insertGetId([
                'number_records' => count($data),
                'status' => 'PENDIENTE',
                'created_by' => 'InRetail '.$type,
                'id_corporation' => $data[0]->id_corporation,
                'id_organization' => $data[0]->id_organization,
                'type' => 'DISTRIBUCION',
                'proc_integracion' => 1
            ]);

            foreach ($data as $key => &$value) {
                DB::table('massive_load_details')->insert([
                    'id_massive_load' => $id,
                    'seg_code' => $value->seg_code,
                    'guide_number' => $value->guide_number,
                    'alt_code1' => $value->alt_code1,
                    'alt_code2' => $value->alt_code2,
                    'client_date' => date('Y-m-d H:i:s', time() + 86400),
                    // 'client_date2' => $value['client_date2'] ?? null,
                    'client_barcode' => $value->client_barcode,
                    'client_dni' => $value->delivery_client_dni,
                    'client_name' => $value->delivery_client_name,
                    'client_phone1' => $value->delivery_client_phone1,
                    'client_phone2' => $value->delivery_client_phone2,
                    'client_phone3' => $value->delivery_client_phone3,
                    'client_email' => $value->delivery_contact_email,
                    'client_address' => $value->delivery_address,
                    'client_address_reference' => $value->delivery_address_reference,
                    // 'coord_latitude' => $value['coord_latitude'] ?? null,
                    // 'coord_longitude' => $value['coord_longitude'] ?? null,
                    'ubigeo' => $value->delivery_ubigeo,
                    'department' => $value->delivery_department,
                    'district' => $value->delivery_district,
                    'province' => $value->delivery_province,
                    'sku_code' => $value->sku_code,
                    'sku_description' => $value->sku_description,
                    'sku_weight' =>  $value->sku_weight,
                    'sku_pieces' =>  $value->sku_pieces,
                    // 'sku_brand' => $value['sku_brand'] ?? null,
                    // 'sku_size' => $value['sku_size'] ?? null,
                    // 'box_code' => $value['box_code'] ?? null,
                    'status' => 'PENDIENTE',
                    'created_by' => 'InRetail '.$type,
                    // 'delivery_type' => $value['delivery_type'] ?? null,
                    'contact_name' => $value->delivery_contact_name,
                    // 'contact_phone' => $value['contact_phone'] ?? null,
                    // 'payment_method' => $value['payment_method'] ?? null,
                    // 'amount' => $value['amount'] ?? null,
                    // 'collect_time_range' => $value['collect_time_range'] ?? null,
                    'seller_name' => $value->seller_name,
                    'date_loaded' => date('Y-m-d H:i:s'),
                    'delivery_type' => $type
                ]);
                DB::table('guide')->where('id_guide',$value->id_guide)->update(['proc_integracion'=>2]);
            }

            // DB::table('integration_data')->where('id_integration_data',$value->id_integration_data)->update(['status'=>'PROCESADO']);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        return $id;
    }

    public function insertarCargaDistribucion($data)
    {
        DB::beginTransaction();
        try {
            $id = DB::table('massive_load')->insertGetId([
                'number_records' => count($data),
                'status' => 'PENDIENTE',
                'created_by' => 'enviame',
                'id_corporation' => $data[0]->id_corporation,
                'id_organization' => $data[0]->id_organization,
                'type' => 'DISTRIBUCION',
                'integracion' => 1
            ]);

            foreach ($data as $key => &$value) {
                DB::table('massive_load_details')->insert([
                    'id_massive_load' => $id,
                    'seg_code' => $value->seg_code,
                    'guide_number' => $value->guide_number,
                    'alt_code1' => $value->alt_code1,
                    'alt_code2' => $value->alt_code2,
                    'client_date' => date('Y-m-d H:i:s', time() + 86400),
                    // 'client_date2' => $value['client_date2'] ?? null,
                    'client_barcode' => $value->client_barcode,
                    'client_dni' => $value->delivery_client_dni,
                    'client_name' => $value->delivery_client_name,
                    'client_phone1' => $value->delivery_client_phone1,
                    'client_phone2' => $value->delivery_client_phone2,
                    'client_phone3' => $value->delivery_client_phone3,
                    'client_email' => $value->delivery_contact_email,
                    'client_address' => $value->delivery_address,
                    'client_address_reference' => $value->delivery_address_reference,
                    // 'coord_latitude' => $value['coord_latitude'] ?? null,
                    // 'coord_longitude' => $value['coord_longitude'] ?? null,
                    'ubigeo' => $value->delivery_ubigeo,
                    'department' => $value->delivery_department,
                    'district' => $value->delivery_district,
                    'province' => $value->delivery_province,
                    'sku_code' => $value->sku_code,
                    'sku_description' => $value->sku_description,
                    'sku_weight' =>  $value->sku_weight,
                    'sku_pieces' =>  $value->sku_pieces,
                    // 'sku_brand' => $value['sku_brand'] ?? null,
                    // 'sku_size' => $value['sku_size'] ?? null,
                    // 'box_code' => $value['box_code'] ?? null,
                    'status' => 'PENDIENTE',
                    'created_by' => 'enviame',
                    // 'delivery_type' => $value['delivery_type'] ?? null,
                    'contact_name' => $value->delivery_contact_name,
                    // 'contact_phone' => $value['contact_phone'] ?? null,
                    // 'payment_method' => $value['payment_method'] ?? null,
                    // 'amount' => $value['amount'] ?? null,
                    // 'collect_time_range' => $value['collect_time_range'] ?? null,
                    'seller_name' => $value->seller_name,
                    'date_loaded' => date('Y-m-d H:i:s')
                ]);
                DB::table('guide')->where('id_guide',$value->id_guide)->update(['integracion'=>2]);
            }

            // DB::table('integration_data')->where('id_integration_data',$value->id_integration_data)->update(['status'=>'PROCESADO']);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        return $id;
    }

    public function insertIntegrationData($data, $user)
    {
        DB::beginTransaction();
        try {
            $id = DB::table('load_integration')->insertGetId(
                [
                    'id_integration_user' => $user->id_integration_user, 'id_corporation' => $user->id_corporation,
                    'id_organization' => $user->id_organization, 'request_data' => json_encode($data), 'status' => 'PENDIENTE',
                    'created_by' => $user->integration_username, 'number_records' => count($data['items']),
                    'type' => $data['selectedSla'] ?? 'NINGUNO'
                ]
            );

            $idOriginal = 'RP' . Carbon::now()->format('Ymd') . str_pad($id, 6, "0", STR_PAD_LEFT);

            foreach ($data['items'] as $value) {
                $ubigeo_recoleccion = DB::table('ubigeo')->where('ubigeo', $data['sellerUbigeo'])->first();
                $ubigeo_dist = DB::table('ubigeo')->where('ubigeo', $data['clientUbigeo'])->first();

                if (!$ubigeo_recoleccion && !$ubigeo_dist) {
                    throw new Exception("Ubigeo inválido", 1);
                }

                DB::table('load_integration_detail')->insert(
                    [
                        'id_load_integration' => $id,
                        'status' => 'PENDIENTE',
                        'created_by' => $user->integration_username,
                        'seg_code' => $data['segCode'],
                        'guide_number' => $idOriginal,
                        'client_barcode' => $data['barcode'] ?? $idOriginal,
                        'alt_code1' => $data['altCode1'] ?? null,
                        'sku_code' => $value['id'],
                        'sku_description' => $value['description'],
                        'sku_weight' => $value['weight'] ?? null,
                        'sku_pieces' => $value['quantity'],
                        'collect_ubigeo' => $data['sellerUbigeo'],
                        'collect_department' => $ubigeo_recoleccion->department,
                        'collect_district' => $ubigeo_recoleccion->district,
                        'collect_province' => $ubigeo_recoleccion->province,
                        'collect_address_reference' => $data['sellerAddressReference'] ?? null,
                        'collect_address' => $data['sellerAddress'],
                        'collect_client_dni' => $data['sellerDocument'],
                        'collect_client_name' => $data['sellerCorporateName'],
                        'collect_client_phone1' => $data['sellerPhone1'] ?? null,
                        'collect_client_phone2' => $data['sellerPhone2'] ?? null,
                        'collect_contact_name' => $data['sellerContactName'] ?? null,
                        'collect_client_email' => $data['sellerContactEmail'] ?? null,
                        'delivery_ubigeo' => $data['clientUbigeo'],
                        'delivery_department' => $ubigeo_dist->department,
                        'delivery_district' => $ubigeo_dist->district,
                        'delivery_province' => $ubigeo_dist->province,
                        'delivery_address_reference' => $data['clientAddressReference'] ?? null,
                        'delivery_address' => $data['clientAddress'],
                        'delivery_client_dni' => $data['clientDocument'],
                        'delivery_client_name' => $data['clientName'],
                        'delivery_client_phone1' => $data['clientPhone1'] ?? null,
                        'delivery_client_phone2' => $data['clientPhone2'] ?? null,
                        'delivery_contact_email' => $data['clientEmail'] ?? null,
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::warning("insertar data integracion nueva " . $e->getMessage());
            DB::rollback();
            throw $e;
        }
        DB::commit();
        return $idOriginal;
    }

    public function getDatosRutaCargoIntegracion($guide_number)
    {
        $query = DB::select("select
                li.id_organization, date(lid.date_created) as date_loaded, lid.guide_number, lid.client_barcode,
                lid.delivery_client_name as client_name, lid.delivery_client_phone1 as client_phone1, lid.delivery_contact_email as client_email,
                lid.delivery_client_dni as client_dni, 'DISTRIBUCION' as type, lid.alt_code1, null as collect_time_range,
                lid.delivery_contact_name as contact_name, null as client_date, null as amount, null as payment_method,
                org.name, org.address as org_address, lid.delivery_district as district, lid.delivery_province as province,
                lid.delivery_address as address, lid.delivery_address_reference as address_refernce, lid.delivery_department as department,
                0 as total_pieces, 0 as total_weight, GROUP_CONCAT(lid.sku_code, '-',lid.sku_description) as contenido,
                date(li.date_updated) as date_created
            from load_integration_detail lid
            join load_integration li on li.id_load_integration = lid.id_load_integration
            join organization as org on org.id_organization = li.id_organization
            where
                lid.guide_number = ?
            group by
                lid.guide_number,
                li.id_organization,
                date(lid.date_created),
                lid.client_barcode,
                lid.delivery_client_name,
                lid.delivery_client_phone1,
                lid.delivery_contact_email,
                lid.delivery_client_dni,
                lid.alt_code1,
                lid.delivery_contact_name,
                lid.delivery_district,
                lid.delivery_province,
                lid.delivery_address,
                lid.delivery_address_reference,
                lid.delivery_department,
                date(li.date_updated)
            order by 1 desc;", [$guide_number]);
        return $query;
    }
}
