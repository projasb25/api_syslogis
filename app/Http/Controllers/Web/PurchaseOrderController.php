<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\PurchaseOrderLoadRequest;
use App\Models\Services\Web\PurchaseOrderService;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    protected $mainService;
    public function __construct(PurchaseOrderService $service) {
        $this->mainService = $service;
    }

    public function index(PurchaseOrderLoadRequest $request)
    {
        return $this->mainService->index($request);
    }
}
