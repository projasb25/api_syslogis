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

    public function LogInsert($cud, $estado, $subEstado, $idpedido_detalle, $result, $request, $response)
    {
        DB::table('log_integration_ripley')->insert(
            [
                'cud' => $cud,
                'estado' => $estado,
                'subEstado' => $subEstado,
                'idpedido_detalle' => $idpedido_detalle,
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
            pdep.observaciones as subestado, vh.numero_placa, 'Qayarix' as courier,env.fecha, pd.contacto_nombre_descarga, pd.contacto_dni_descarga, p.nro_guia_sistema ,CONCAT('https://www.qayarix.com:4721/ripley/status?c=',pd.idpedido_detalle) AS url ,
            pd.idpedido_detalle
        from pedido p
        join pedido_detalle pd on pd.idpedido = p.idpedido and pd.idpedido_detalle = (select max(idpedido_detalle) from pedido_detalle pd2 where pd2.idpedido = p.idpedido)
        join pedido_detalle_estado_pedido_detalle as pdep on pdep.idpedido_detalle = pd.idpedido_detalle and pdep.idpedido_detalle_estado_pedido_detalle = (select max(idpedido_detalle_estado_pedido_detalle) from pedido_detalle_estado_pedido_detalle where idpedido_detalle = pd.idpedido_detalle)
        join envio as env on env.idenvio = pd.idenvio
        join vehiculo as vh on vh.idvehiculo = env.idvehiculo
        where p.token in (
            '0600050000855037220006',
            '0600050000857775160002',
            '0600050000855787900105',
            '0600050000855504530002',
            '0600050000855369990002',
            '0600050000854966460002',
            '0600050000854913690008',
            '0600050000856576910001',
            '0600050000856295930003',
            '0600050000858459690001',
            '0600050000857685030003',
            '0600050000857508800001',
            '0600050000857416300001',
            '0600050000857398620003',
            '0600050000857085670002',
            '0600050000857027490006',
            '0600050000856655390001',
            '0600050000855520350001',
            '0600050000853087630001',
            '0600050000856776770001',
            '0600050000853913200001',
            '0600050000858288600002',
            '0600050000858139650001',
            '0600050000857985450001',
            '0600050000857401150001',
            '0600050000856856420102',
            '0600050000856322360101',
            '0600050000854896870001',
            '0600050000856550230001',
            '0600050000856306930004',
            '0600050000856301810002',
            '0600050000856156510001',
            '0600050000856584610005',
            '0600050000856664930001',
            '0600050000857729080101',
            '0600050000855702740002',
            '0600050000855539540003',
            '0600050000855508700003',
            '0600050000855428210001',
            '0600050000855118340002',
            '0600050000850489440001',
            '0600050000856838660002',
            '0600050000856838610001',
            '0600050000858195740001',
            '0600050000858145120101',
            '0600050000858143140002',
            '0600050000858013560002',
            '0600050000857978830005',
            '0600050000857960430004',
            '0600050000857950640001',
            '0600050000857807420003',
            '0600050000857721610001',
            '0600050000857720960008',
            '0600050000857516670003',
            '0600050000857215400001',
            '0600050000857170020004',
            '0600050000857146520003',
            '0600050000857023460005',
            '0600050000857002960003',
            '0600050000856968710002',
            '0600050000856965490001',
            '0600050000856812880001',
            '0600050000856808660001',
            '0600050000856770050003',
            '0600050000855485730003',
            '0600050000856776970002',
            '0600050000856197570001',
            '0600050000854413670001',
            '0600050000857527240001',
            '0600050000858274740002',
            '0600050000858143850003',
            '0600050000856944680005',
            '0600050000856912210002',
            '0600050000856908100001',
            '0600050000856856410101',
            '0600050000855590770004',
            '0600050000855641970001',
            '0600050000856550230001',
            '0600050000854800970502',
            '0600050000856596640002',
            '0600050000853395510003'
            )
            group by p.token");
    }
    // where date(p.fecha) >= '2020-10-23' and p.idcliente in (108,5,109,112,113,115)

    public function checkReported($cud, $estado, $subestado, $idpedido_detalle)
    {
        return DB::table('log_integration_ripley')
            ->where('cud', $cud)
            ->where('estado', $estado)
            ->where('subEstado', $subestado)
            ->where('idpedido_detalle', $idpedido_detalle)
            ->where('result', 'SUCCESS')
            ->first();
    }
}