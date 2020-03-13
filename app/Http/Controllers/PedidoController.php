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
        $filename = "test.jpg";
        $path = $request->file('imagen')->move(public_path("/"), $filename);
        $photourl = url('/' . $filename);
        return response()->json(['url' => $photourl]);
    }
}
