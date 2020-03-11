<?php

namespace App\Http\Controllers;

use App\Models\Repositories\PedidoDetalleRepository;
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
        $res = $this->enviosServi->aceptar($request);
        return response()->json($res);
    }
}
