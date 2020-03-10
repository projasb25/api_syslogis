<?php

namespace App\Http\Controllers;

use App\Models\Services\OfertasService;
use Illuminate\Http\Request;

class ConductorController extends Controller
{
    private $ofertasServi;

    public function __construct(OfertasService $ofertasService)
    {
        $this->ofertasServi = $ofertasService;    
    }

    public function listarOfertas(Request $request)
    {
        $res = $this->ofertasServi->listar($request);
        return response()->json($res);
    }
}
