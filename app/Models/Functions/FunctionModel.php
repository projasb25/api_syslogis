<?php

namespace App\Models\Functions;

use Illuminate\Http\Request;
use Log;

class FunctionModel
{
    private $functions = [
        "SP_AUTHENTICATE" => [
            "query" => 'CALL SP_AUTHENTICATE(:username)',
            "params" => ['username']
        ],
    ];

    public function getFunctions()
    {
        return $this->functions;
    }
}
