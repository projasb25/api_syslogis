<?php

namespace App\Models\Repositories;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DriverRepository
{
    public function getShippingOrders($id_driver)
    {
        return collect(DB::select("CALL SP_SEL_DRIVERS_ORDERS(?)",[$id_driver]));
    }

    public function actualizarEstado($estado, $id)
    {
        DB::beginTransaction();
        try {
            DB::table('driver')->where('driverid', $id)->update(['status' => $estado]);
            DB::table('vehicle')->where('driverid', $id)->update(['status' => $estado]);
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
        DB::commit();
    }
}
