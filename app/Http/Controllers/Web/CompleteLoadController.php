<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\MainRequest;
use App\Models\Services\Web\CompleteLoadService;
use Illuminate\Http\Request;
use Validator;

class CompleteLoadController extends Controller
{
    private $mainService;

    public function __construct(CompleteLoadService $mainServi)
    {
        $this->mainService = $mainServi;
    }

    public function load(Request $request)
    {
        return $this->mainService->load($request);
    }

    public function process(Request $request)
    {
        return $this->mainService->process_load($request);
    }

    public function process_distribution(Request $request)
    {
        return $this->mainService->process_distribution();
    }
}
