<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pedido\grabarImagen;
use App\Models\Services\Web\ShippingService;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    protected $shippingServi;

    public function __construct(ShippingService $shippingService) {
        $this->shippingServi = $shippingService;
    }


    public function index(Request $request)
    {
        return $this->shippingServi->index($request);
    }

    public function print_hoja_ruta(Request $request)
    {
        return $this->shippingServi->print_hoja_ruta_reworked($request);
    }
    
    public function grabarImagen(grabarImagen $request)
    {
        return $this->shippingServi->grabarImagen($request);
    }
}
