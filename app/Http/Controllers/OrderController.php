<?php

namespace App\Http\Controllers;

use App\Models\Services\OrderService;
use Illuminate\Http\Request;
use Validator;

class OrderController extends Controller
{
    protected $orderServi;

    public function __construct(OrderService $orderService)
    {
        $this->orderServi = $orderService;
    }

    public function massive_load(Request $request)
    {
        return $this->orderServi->massive_load($request);
    }

    public function insert(Request $request)
    {
        return response()->json([
            'succes' => true,
            'data' => 'Exito'
        ]);

        return $this->orderServi->insert($request->all());
    }
}
