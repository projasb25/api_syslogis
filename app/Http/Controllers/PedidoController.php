<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pedido\grabarImagen;
use App\Http\Requests\Pedido\obtenerImagen;
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
    }

    public function getImagen(Request $request, $idpedido_detalle)
    {
        return $this->pedidoServi->getImagen($idpedido_detalle);
    }
}
