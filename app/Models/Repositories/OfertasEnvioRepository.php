<?php

namespace App\Models\Repositories;

use App\Models\Entities\OfertaEnvio;
use Illuminate\Support\Facades\DB;

class OfertasEnvioRepository
{
    public function get($id)
    {
        return OfertaEnvio::find($id);
    }

    public function all()
    {
        return OfertaEnvio::all();
    }

    public function delete($id)
    {
        OfertaEnvio::destroy($id);
    }

    public function update($id, array $data)
    {
        OfertaEnvio::find($id)->update($data);
    }

    public function register($input)
    {
        return OfertaEnvio::create($input);
    }
}
