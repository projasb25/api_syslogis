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
        return DB::table('shipping_order')->where('id_shipping_order',$id)->first();
    }

    public function get_hoja_ruta($id)
    {
        return DB::table('shipping_order')->where('id_shipping_order', $id)->first();
    }

    public function aceptarEnvio($id)
    {
        DB::table('shipping_order')->where('id_shipping_order',$id)->update(['status' => 'ACEPTADO']);
    }

    public function rechazarEnvio($id)
    {
        DB::beginTransaction();
        try {
            DB::table('shipping_order')->where('id_shipping_order',$id)->update(['status' => 'RECHAZADO']);
            $guias = DB::table('shipping_order_detail')->select('id_guide')->where('id_shipping_order',$id)->get();
            foreach ($guias as $key => $guia) {
                DB::table('guide')->where('id_guide', $guia->id_guide)->update(['status' => 'PENDIENTE']);
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
        adr.address, adr.district, vh.plate_number, gd.client_barcode,  dv.first_name, dv.last_name, so.*
    from
        shipping_order so
    join vehicle vh on vh.id_vehicle = so.id_vehicle 
    join driver dv on dv.id_driver = vh.id_driver
    join shipping_order_detail sod on sod.id_shipping_order = so.id_shipping_order
    join guide gd on gd.id_guide = sod.id_guide
    join address adr on adr.id_address = gd.id_address
    where
        so.id_shipping_order = ?;', [$shipping_order]);
        return $query;
    }

    public function actualizar_hoja_ruta($filename, $id)
    {
        DB::table('shipping_order')->where('id_shipping_order', $id)->update([
            'hoja_ruta_doc' => $filename
        ]);
    }
}
