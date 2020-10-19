<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Services\Web\PublicoService;
use Illuminate\Http\Request;

class PublicoController extends Controller
{
    protected $service;
    public function __construct(PublicoService $publicoService) {
        $this->service = $publicoService;
    }
    public function guide_status(Request $request)
    {
        return $this->service->guide_status($request);
    }
}
