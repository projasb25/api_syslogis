<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\MassiveLoadInsertRequest;
use App\Http\Requests\Web\MassiveLoadProcessRequest;
use App\Http\Requests\Web\Publica\MassiveLoadInsertRequest as PublicaMassiveLoadInsertRequest;
use App\Models\Services\Web\MassiveLoadService;
use Illuminate\Http\Request;

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

    public function print_cargo(Request $request)
    {
        return $this->mainService->print_cargo($request);
    }

    public function print_marathon(Request $request)
    {
        return $this->mainService->print_marathon($request);
    }

    public function public_massive_load(PublicaMassiveLoadInsertRequest $request)
    {
        return $this->mainService->publicoInsertarCarga($request);
    }
}
