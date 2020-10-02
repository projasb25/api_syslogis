<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
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
}
