<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Services\Web\CollectService;
use Illuminate\Http\Request;

class CollectController extends Controller
{
    private $service;

    public function __construct(CollectService $collectService)
    {
        $this->service = $collectService;
    }

    public function load(Request $request)
    {
        return $this->service->load($request);
    }

    public function process(Request $request)
    {
        return $this->service->process($request);
    }
}
