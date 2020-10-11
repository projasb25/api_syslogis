<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pedido\actualizarPedido;
use App\Http\Requests\Pedido\grabarImagen;
use App\Models\Services\ShippingService;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    protected $service;
    public function __construct(ShippingService $shippingService) {
        $this->service = $shippingService;
    }

    public function aceptar(Request $request)
    {
        return $this->service->aceptarOferta($request);
    }

    public function rechazar(Request $request)
    {
        return $this->service->rechazarOferta($request);
    }

    public function listarRutas(Request $request)
    {
        return $this->service->listarRutas($request);
    }

    public function iniciar(Request $request)
    {
        return $this->service->iniciarRuta($request);
    }

    public function getMotivos(Request $request)
    {
        return $this->service->motivos();
    }

    public function grabarImagen(grabarImagen $request)
    {
        return $this->service->grabarImagen($request);
    }

    public function getImagen(Request $request)
    {
        return $this->service->getImagen($request);
    }

    public function actualizar(actualizarPedido $request)
    {
        return $this->service->actualizarPedido($request);
    }

    public function finalizar(Request $request)
    {
        return $this->service->finalizarRuta($request);
    }
}
