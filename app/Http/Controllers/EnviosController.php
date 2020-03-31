<?php

namespace App\Http\Controllers;

use App\Models\Services\EnviosService;
use Illuminate\Http\Request;

class EnviosController extends Controller
{
    protected $enviosServi;

    public function __construct(EnviosService $enviosService)
    {
        $this->enviosServi = $enviosService;
    }

    public function aceptar(Request $request)
    {
        return $this->enviosServi->aceptar($request);
    }

    public function rechazar(Request $request)
    {
        return $this->enviosServi->rechazar($request);
    }

    public function listarRutas(Request $request)
    {
        return $this->enviosServi->listarRutas($request->idofertaenvio);
    }

    public function iniciar(Request $request, $idenvio)
    {
        return $this->enviosServi->iniciar($request, $idenvio);
    }

    public function finalizar(Request $request, $idenvio)
    {
        return $this->enviosServi->finalizar($idenvio);
    }
}
