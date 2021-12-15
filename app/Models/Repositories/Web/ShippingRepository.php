<?php

namespace App\Models\Repositories\Web;

use App\Exceptions\CustomException;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShippingRepository
{
    public function getShippingOrder($id)
    {
        return DB::table('shipping_order')->where('shippingorderid',$id)->first();
    }

    public function get_hoja_ruta($id)
    {
        return DB::table('shipping_order')->where('id_shipping_order', $id)->first();
    }

    public function aceptarEnvio($id)
    {
        DB::table('shipping_order')->where('shippingorderid',$id)->update(['status' => 'ACEPTADO']);
    }

    public function listarRutas($id)
    {
        return DB::select("CALL SP_SEL_DETALLE_ENVIO(?)",[$id]);
    }

    public function iniciarRuta($id)
    {
        return DB::select("CALL SP_INICIAR_RUTA(?)",[$id]);
    }

    public function getMotivos($tipo)
    {
        if ($tipo === 'recoleccion') {
            $query = DB::table('collect_motive')->where('status', 'ACTIVO')->where('estado', 'No Recolectado')->get();
        } else {
            $query = DB::table('motive')->where('status', 'ACTIVO')->where('estado', 'No entregado')->get();
        }
        return $query;
    }

    public function getShippingOrderDetail($id)
    {
        return DB::table('shipping_order_detail')->where('id_shipping_order_detail', $id)->first();
    }

    public function getShippingDetailByGuideNumber($guide_number, $id_shipping_order)
    {
        return DB::table('shipping_order_detail')->where('guide_number', $guide_number)->where('id_shipping_order', $id_shipping_order)->get();
    }

    public function insertarImagen($id, $id_shipping, $url, $desc, $type)
    {
        DB::table('guide_images')->insert(['guideid' => $id, 'shippingorderid' => $id_shipping ,'url' => $url, 'description' => $desc, 'type' => $type]);
    }

    public function actualizarPedido($data)
    {
        return DB::select("CALL SP_ACTUALIZAR_PEDIDO(?)",[json_encode($data)]);
    }

    public function finalizarRuta($id)
    {
        return DB::select("CALL SP_FINALIZAR_RUTA(?)",[$id]);
    }

    public function obtenerImagenes($id_guide, $id_shipping_order)
    {
        return DB::table('guide_images')->where('id_guide', $id_guide)->where('id_shipping_order', $id_shipping_order)->get();
    }

    public function rechazarEnvio($id)
    {
        DB::beginTransaction();
        try {
            DB::table('shipping_order')->where('shippingorderid',$id)->update(['status' => 'RECHAZADO']);
            DB::table('shipping_order_detail')->where('shippingorderid',$id)->update(['status' => 'RECHAZADO']);

            $guias = DB::table('shipping_order_detail')->select('guideid')->where('shippingorderid',$id)->get();
            foreach ($guias as $key => $guia) {
                $guide = DB::table('guide')->where('guideid', $guia->guideid)->first();

                $last_tracking = DB::table('guide_tracking')->where('guideid', $guia->guideid)->orderBy('guidetrackingid', 'desc')->first();
                DB::table('guide_tracking')->where('guidetrackingid', $last_tracking->guidetrackingid)->update(['attempt' => -1]);
                DB::table('guide_tracking')->insert(['guideid' => $guia->guideid, 'status' => 'ANULADO', 'motive' => 'Guia rechazada por conductor', 'attempt' => -1]);

                $last_status = DB::table('guide_tracking')->where('guideid', $guia->guideid)->where('attempt', '>=', 0)->orderBy('guidetrackingid', 'desc')->first();
                DB::table('guide')->where('guideid', $guia->guideid)->update(['status' => $last_status->status, 'attempt' => ($guide->attempt - 1)]);

                // DB::table('guide')->where('id_guide', $guia->id_guide)->update(['status' => 'PENDIENTE']);
                // DB::table('guide_tracking')->insert(['id_guide' => $guia->id_guide, 'status' => 'PENDIENTE', 'motive' => 'Registro Automático.']);
            }
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();
    }
    
    public function get_imprimir_hoja_ruta($shipping_order)
    {
        $query = DB::select('select
            adr.address, adr.district, vh.plate_number, gd.guide_number, gd.client_barcode, dv.first_name, dv.last_name, pv.name as provider_name, so.*,
            (select count(guide_barcode) from shipping_order_detail as sod2 where sod2.guide_barcode = gd.client_barcode and id_shipping_order = so.id_shipping_order) as nro_guias
        from
            shipping_order so
        join vehicle vh on vh.id_vehicle = so.id_vehicle 
        join driver dv on dv.id_driver = vh.id_driver
        join provider pv on pv.id_provider = vh.id_provider
        join shipping_order_detail sod on sod.id_shipping_order = so.id_shipping_order
        join guide as gd on gd.id_guide = sod.id_guide
        join address adr on adr.id_address = gd.id_address
        where
            so.id_shipping_order = ?
        group by 
            gd.client_barcode,
            gd.guide_number,
            adr.address,
            adr.district
        order by 
            adr.district,
            gd.guide_number;', [$shipping_order]);
        return $query;
    }

    public function actualizar_hoja_ruta($filename, $id)
    {
        DB::table('shipping_order')->where('id_shipping_order', $id)->update([
            'hoja_ruta_doc' => $filename
        ]);
    }
}
