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
}
