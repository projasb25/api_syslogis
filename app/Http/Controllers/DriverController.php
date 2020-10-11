<?php

namespace App\Http\Controllers;

use App\Models\Services\DriverService;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    protected $service;

    public function __construct(DriverService $driverService) {
        $this->service = $driverService;
    }

    public function listarOfertas(Request $request)
    {
        return $this->service->listarOfertas($request);
    }
}
