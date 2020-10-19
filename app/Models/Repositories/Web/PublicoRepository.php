<?php

namespace App\Models\Repositories\Web;

use Illuminate\Support\Facades\DB;

class PublicoRepository
{
    public function guide_status($id_guide)
    {
        return DB::select("CALL SP_SEL_GUIDE_STATUS(?)",[$id_guide]);
    }
}
