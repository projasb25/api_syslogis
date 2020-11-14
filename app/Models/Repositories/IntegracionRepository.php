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
    // gt.date_created as Fecha, gd.date_updated, gd.client_name as NombreReceptor,
    
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

    public function updateReportadoOeschle($guides)
    {
        DB::beginTransaction();
        try {
            foreach ($guides as $key => $guide) {
                DB::table('guide')->where('id_guide', $guide->id_guide)->update(['reportado_integracion' => 1]);
            }
        } catch (Exception $e) {
            Log::warning("updateReportadoOeschle" . $e->getMessage());
            DB::rollback();
        }
        DB::commit();
    }

    public function LogInsertOechsle($result, $request, $response, $guias)
    {
        $guias_l = explode(',', $guias);
        foreach ($guias_l as $id_guia) {
            DB::table('log_integracion_oechsle')->insert(
                [
                    'result' => $result,
                    'id_guide' => $id_guia,
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
}