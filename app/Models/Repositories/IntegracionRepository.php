<?php

namespace App\Models\Repositories;

use App\Models\Entities\Guide;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IntegracionRepository
{
    public function getGuides($corpId)
    {
        $query = DB::select("CALL SP_SEL_INTEGRATION_GUIDES(?)", [$corpId]);
        return $query;
        // return DB::select("select 
        //         gd.id_guide, gd.seg_code as CUD, gd.status, 
        //         gt.status as Estado, gt.motive as SubEstado, 
        //         vh.plate_number as Placa, 'Qayarix' as Courier,
        //         date_sub(gt.date_created, INTERVAL 5 hour) as Fecha, gd.date_updated, gd.client_name as NombreReceptor,
        //         gd.client_dni as IDReceptor, gd.client_barcode as TrackNumber
        //     from guide as gd
        //     join guide_tracking as gt on gt.id_guide = gd.id_guide and gt.id_guide_tracking = (select max(id_guide_tracking) from guide_tracking where id_guide = gt.id_guide)
        //     left join shipping_order as so on so.id_shipping_order = gt.id_shipping_order
        //     left join vehicle as vh on vh.id_vehicle = so.id_vehicle
        //     WHERE gd.id_corporation IN (1) and gd.reportado_integracion = 0
        //     and gd.status IN ('CURSO', 'ENTREGADO', 'NO ENTREGADO')"
        // );
    }

    public function getGuidesAllStatus($corpId)
    {
        $query = DB::select("CALL SP_SEL_INTEGRATION_GUIDES_ALLSTATUS(?)", [$corpId]);
        return $query;
    }

    public function getGuidesTukuy()
    {
        $query = DB::select("CALL SP_SEL_GUIDES_TUKUY_INTEGRATION()");
        return $query;
    }

    public function insertarImagen($id, $id_shipping, $url, $desc, $type)
    {
        DB::table('guide_images')->insert(['id_guide' => $id, 'id_shipping_order' => $id_shipping ,'url' => $url, 'description' => $desc, 'type' => $type]);
    }

    public function updateGuidesTukuy($id_guide, $id_shipping_order, $id_shipping_order_detail, $status, $motive)
    {
        $query = DB::select("CALL SP_UPDATE_GUIDES_TUKUY_INTEGRATION(?,?,?,?,?)",[$id_guide, $id_shipping_order, $id_shipping_order_detail, $status, $motive]);
        return $query;
    }

    public function reportarErrorIntegracionTukuy($id_guide)
    {
        DB::table('guide')->where('id_guide', $id_guide)->update(['processed_distribution' => 9]);
    }
    
    public function LogInsert($cud, $id_guide, $estado, $subestado, $result, $request, $response)
    {
        DB::table('log_integration_ripley')->insert(
            [
                'id_guide' => $id_guide,
                'cud' => $cud,
                'estado' => $estado,
                'subestado' => $subestado,
                'result' => $result,
                'response' => json_encode($response),
                'request' => json_encode($request)
            ]
            );
    }

    public function logInsertInRetail($seg_code, $guide_number, $id_guide, $estado, $subestado, $result, $request, $response)
    {
        DB::table('log_integration_inretail')->insert(
            [
                'id_guide' => $id_guide,
                'seg_code' => $seg_code,
                'guide_number' => $guide_number,
                'estado' => $estado,
                'subestado' => $subestado,
                'result' => $result,
                'response' => json_encode($response),
                'request' => json_encode($request)
            ]
            );
    }

    public function logInsertCoolbox($seg_code, $guide_number, $id_guide, $estado, $subestado, $result, $request, $response)
    {
        DB::table('log_integration_coolbox')->insert(
            [
                'id_guide' => $id_guide,
                'seg_code' => $seg_code,
                'guide_number' => $guide_number,
                'estado' => $estado,
                'subestado' => $subestado,
                'result' => $result,
                'response' => json_encode($response),
                'request' => json_encode($request)
            ]
            );
    }

    public function insertLogIntegration($seg_code, $id_corporation, $id_organization, $guide_number, $id_guide, $estado, $subestado, $result, $request, $response)
    {
        DB::table('log_integration')->insert(
            [
                'id_guide' => $id_guide,
                'id_corporation' => $id_corporation,
                'id_organization' => $id_organization,
                'seg_code' => $seg_code,
                'guide_number' => $guide_number,
                'status' => $estado,
                'substatus' => $subestado,
                'result' => $result,
                'response' => json_encode($response, JSON_UNESCAPED_UNICODE),
                'request' => json_encode($request, JSON_UNESCAPED_SLASHES)
            ]
            );
    }
    
    public function updateReportado($id_guide, $report)
    {
        DB::table('guide')->where('id_guide', $id_guide)->update(['reportado_integracion' => $report]);
    }

    public function getGuideOeschle()
    {
        $query = DB::select("CALL SP_SEL_INTEGRATION_GUIDES_OECHSLE()");
        // $data = Guide::where('id_corporation', 4)
        //     ->where('status','ENTREGADO')
        //     ->where('reportado_integracion', 0)
        //     ->get();
        return $query;
    }

    public function updateReportadoOeschle($guias, $report)
    {
        DB::beginTransaction();
        try {
            $guias_l = explode(',', $guias);
            foreach ($guias_l as $id_guia) {
                DB::table('guide')->where('id_guide', $id_guia)->update(['reportado_integracion' => $report]);
            }
            // foreach ($guides as $key => $guide) {
            //     DB::table('guide')->where('id_guide', $guide->id_guide)->update(['reportado_integracion' => 1]);
            // }
        } catch (Exception $e) {
            Log::warning("updateReportadoOeschle" . $e->getMessage());
            DB::rollback();
        }
        DB::commit();
    }

    public function LogInsertOechsle($result, $request, $response, $guias, $altcode)
    {
        $guias_l = explode(',', $guias);
        foreach ($guias_l as $id_guia) {
            DB::table('log_integracion_oechsle')->insert(
                [
                    'result' => $result,
                    'id_guide' => $id_guia,
                    'nro_despacho' => $altcode,
                    'response' => json_encode($response),
                    'request' => json_encode($request)
                ]
            );
        }
    }

    public function getTestRipley()
    {
        return DB::table('test_ripley')->get();
    }

    public function getGuidesInRetail()
    {
        $query = DB::select("CALL SP_SEL_INTEGRATION_GUIDES_INRETAIL()");
        return $query;
    }

    public function getGuidesCoolbox()
    {
        $query = DB::select("CALL SP_SEL_INTEGRATION_GUIDES_COOLBOX()");
        return $query;
    }

    public function getGuideOeschleInter()
    {
        $query = DB::select("CALL SP_SEL_INTEGRATION_GUIDES_OECHSLE_INTER()");
        return $query;
    }

    public function LogInsertOechsle_inter($result, $request, $response, $guias, $altcode, $status, $type)
    {
        $guias_l = explode(',', $guias);
        foreach ($guias_l as $id_guia) {
            DB::table('log_integracion_oechsle_inter')->insert(
                [
                    'result' => $result,
                    'id_guide' => $id_guia,
                    'nro_despacho' => $altcode,
                    'response' => $response,
                    'request' => json_encode($request),
                    'status' => $status,
                    'type' => $type
                ]
            );
        }
    }
}