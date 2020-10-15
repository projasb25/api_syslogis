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
            and gd.status IN ('CURSO', 'ENTREGADO', 'NO ENTREGADO') and gd.id_guide in (86, 84)"
        );
    }

    public function LogInsert($cud, $id_guide, $result, $request, $response)
    {
        DB::table('log_integration_ripley')->insert(
            [
                'id_guide' => $id_guide,
                'cud' => $cud,
                'result' => $result,
                'response' => json_encode($response),
                'request' => json_encode($request)
            ]
            );
    }
}