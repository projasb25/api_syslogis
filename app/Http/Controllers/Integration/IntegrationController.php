<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\Services\Integration\MainService;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    private $mainService;

    public function __construct(MainService $mainServi)
    {
        $this->mainService = $mainServi;
    }

    public function index(Request $request)
    {
        return $this->mainService->index($request);
    }
}
