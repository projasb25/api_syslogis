<?php

namespace App\Models\Repositories;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DriverRepository
{
    public function getShippingOrders($id_driver)
    {
        return DB::table('shipping_order as so')
            ->select("shippingorderid as id_shipping_order","id_driver","number_guides as paradas","descrption","status","type","date_created")
            ->where('id_driver', $id_driver)
            ->whereNotIn('status', ['FINALIZADO', 'RECHAZADO', 'ELIMINADO'])
            ->get();
    }

    public function actualizarEstado($estado, $id)
    {
        DB::beginTransaction();
        try {
            DB::table('driver')->where('id_driver', $id)->update(['status' => $estado]);
            DB::table('vehicle')->where('id_driver', $id)->update(['status' => $estado]);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();
    }
}
