<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Services\Web\BillLoadService;
use Illuminate\Http\Request;

class BillLoadController extends Controller
{
    private $mainService;

    public function __construct(BillLoadService $service)
    {
        $this->mainService = $service;
    }

    public function index(Request $request)
    {
        # code...
    }
}
