<?php

namespace App\Models\Repositories\Integration;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MainRepository
{
    public function insertData($data, $user)
    {
        DB::beginTransaction();
        try {
            $id = DB::table('integration_data')->insertGetId(
                [
                    'id_integration_user' => $user->id_integration_user, 'id_corporation' => $user->id_corporation,
                    'id_organization' => $user->id_organization, 'request_data' => $data, 'status' => 'PROCESADO',
                    'created_by' => $user->integration_user
                ]
            );

            $idOriginal = 'RP' . Carbon::now()->format('Ymd') . str_pad($id, 6, "0", STR_PAD_LEFT);
            DB::table('integration_data')->where('id_integration_data', $id)->update(['unique_id' => $idOriginal]);
        } catch (Exception $e) {
            Log::warning("Actualizar estado conductor " . $e->getMessage());
            DB::rollback();
        }
        DB::commit();
        return $idOriginal;
    }
}
