<?php

namespace App\Http\Controllers;

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
}
