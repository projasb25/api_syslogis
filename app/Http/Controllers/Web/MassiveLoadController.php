<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\MassiveLoadInsertRequest;
use App\Http\Requests\Web\MassiveLoadProcessRequest;
use App\Models\Services\Web\MassiveLoadService;

class MassiveLoadController extends Controller
{
    private $mainService;

    public function __construct(MassiveLoadService $service)
    {
        $this->mainService = $service;
    }

    public function index(MassiveLoadInsertRequest $request)
    {
        return $this->mainService->index($request);
    }

    public function process(MassiveLoadProcessRequest $request)
    {
        return $this->mainService->process($request);
    }
}
