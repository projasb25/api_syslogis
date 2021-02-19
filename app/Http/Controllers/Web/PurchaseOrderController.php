<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Web\PurchaseOrderService;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    protected $mainService;
    public function __construct(PurchaseOrderService $service) {
        $this->mainService = $service;
    }

    public function index(Request $request)
    {
        return $this->mainService->index($request);
    }
}
