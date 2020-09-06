<?php

namespace App\Models\Repositories\Web;

use Illuminate\Support\Facades\DB;

class MainRepository
{
    public function execute_store($sp_name, $data_bidnings)
    {
        return DB::select($sp_name,$data_bidnings);
    }
}
