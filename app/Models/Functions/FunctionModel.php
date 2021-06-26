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
        "SP_UBIGEO" => [
            "query" => 'CALL SP_UBIGEO(:search,:filter)',
            "params" => ['search','filter']
        ]
    ];

    public function getFunctions()
    {
        return $this->functions;
    }
}
