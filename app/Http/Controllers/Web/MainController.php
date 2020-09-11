<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\MainRequest;
use App\Models\Services\Web\MainService;
use Illuminate\Http\Request;
use Validator;

class MainController extends Controller
{
    private $mainService;

    public function __construct(MainService $mainServi)
    {
        $this->mainService = $mainServi;
    }

    public function index(MainRequest $request)
    {
        return $this->mainService->index($request);
    }

    public function simpleTransaction(Request $request)
    {
        return $this->mainService->simpleTransaction($request);
    }
}
