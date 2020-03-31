<?php

namespace App\Http\Controllers;

use App\Models\Services\OfertasService;
use Illuminate\Http\Request;

class OfertasController extends Controller
{
    private $ofertasServi;

    public function __construct(OfertasService $ofertasService)
    {
        $this->ofertasServi = $ofertasService;    
    }

    public function listar(Request $request)
    {
        return $this->ofertasServi->listar($request);
        return response()->json('lista');
    }
}
