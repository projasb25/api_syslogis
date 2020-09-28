<?php

namespace App\Models\Repositories\Web;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MainRepository
{
    public function execute_store($sp_name, $data_bidnings)
    {
        return DB::select($sp_name,$data_bidnings);
    }
}
