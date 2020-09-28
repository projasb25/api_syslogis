<?php

namespace App\Models\Repositories\Web;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class MainRepository
{
    public function execute_store($sp_name, $data_bidnings)
    {
        try {
            return DB::select($sp_name,$data_bidnings);
        } catch (QueryException $th) {
            throw $th;
        }
    }
}
