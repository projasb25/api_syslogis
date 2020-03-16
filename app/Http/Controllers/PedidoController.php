<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pedido\grabarImagen;
use App\Models\Services\PedidoService;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    protected $pedidoServi;

    public function __construct(PedidoService $pedidoService)
    {
        $this->pedidoServi = $pedidoService;
    }

    public function grabarImagen(grabarImagen $request)
    {
        return $this->pedidoServi->grabarImagen($request);
        return response()->json(['url' => $photourl]);
    }
}
