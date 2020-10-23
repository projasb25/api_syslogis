<?php

namespace App\Models\Repositories;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IntegracionRepository
{
    public function getGuides()
    {
        return DB::select("select 
                gd.id_guide, gd.seg_code as CUD, gd.status, 
                gt.status as Estado, gt.motive as SubEstado, 
                vh.plate_number as Placa, 'Qayarix' as Courier,
                gt.date_created as Fecha, gd.date_updated, gd.client_name as NombreReceptor,
                gd.client_dni as IDReceptor, gd.client_barcode as TrackNumber
            from guide as gd
            join guide_tracking as gt on gt.id_guide = gd.id_guide and gt.id_guide_tracking = (select max(id_guide_tracking) from guide_tracking where id_guide = gt.id_guide)
            left join shipping_order as so on so.id_shipping_order = gt.id_shipping_order
            left join vehicle as vh on vh.id_vehicle = so.id_vehicle
            WHERE gd.id_organization IN (1,2,3) and gd.reportado_integracion = 0
            and gd.status IN ('CURSO', 'ENTREGADO', 'NO ENTREGADO')"
        );
        // and gd.id_guide in (104,121,138,93,94,97,99,110,111,114,116,127,128,131,133,144,145,148,150,92)
    }

    public function LogInsert($cud, $estado, $subEstado, $result, $request, $response)
    {
        DB::table('log_integration_ripley')->insert(
            [
                'cud' => $cud,
                'estado' => $estado,
                'subEstado' => $subEstado,
                'result' => $result,
                'request' => json_encode($request),
                'response' => json_encode($response)
            ]
            );
    }
    
    public function updateReportado($id_guide)
    {
        DB::table('guide')->where('id_guide', $id_guide)->update(['reportado_integracion', 1]);
    }

    // public function getGuideOeschle()
    // {
    //     $data = Guide::where('id_corporation', 4)
    //         ->where('status','ENTREGADO')
    //         ->where('reportado_integracion', 0)
    //         ->get();
    //     return $data;
    // }

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

    public function LogInsertOechsle($result, $request, $response)
    {
        DB::table('log_integracion_oechsle')->insert(
            [
                'result' => $result,
                'response' => json_encode($response),
                'request' => json_encode($request)
            ]
            );
    }

    public function getTestRipley()
    {
        return DB::table('test_ripley')->get();
    }

    public function getIntegracionRipley()
    {
        return DB::select("select
            p.token as cud,
            CASE 
                WHEN pdep.idestado_pedido_detalle = 17 THEN 'NO ENTREGADO'
                WHEN pdep.idestado_pedido_detalle = 16 THEN 'ENTREGADO'
                WHEN pdep.idestado_pedido_detalle = 12 THEN 'CURSO'
            END AS estado,
            pdep.observaciones as subestado, vh.numero_placa, 'Qayarix' as courier,env.fecha, pd.contacto_nombre_descarga, pd.contacto_dni_descarga, p.nro_guia_sistema ,CONCAT('https://www.qayarix.com:4721/ripley/status?c=',pd.idpedido_detalle) AS url  
        from pedido p
        join pedido_detalle pd on pd.idpedido = p.idpedido and pd.idpedido_detalle = (select max(idpedido_detalle) from pedido_detalle pd2 where pd2.idpedido = p.idpedido)
        join pedido_detalle_estado_pedido_detalle as pdep on pdep.idpedido_detalle = pd.idpedido_detalle and pdep.idpedido_detalle_estado_pedido_detalle = (select max(idpedido_detalle_estado_pedido_detalle) from pedido_detalle_estado_pedido_detalle where idpedido_detalle = pd.idpedido_detalle)
        join envio as env on env.idenvio = pd.idenvio
        join vehiculo as vh on vh.idvehiculo = env.idvehiculo
        where date(p.fecha) >= '2020-10-23' and p.idcliente in (108,5,109,112,113,115)");
    }

    public function checkReported($cud, $estado, $subestado)
    {
        return DB::table('log_integration_ripley')->where('cud', $cud)->where('estado', $estado)->where('subEstado', $subestado)->first();
    }
}