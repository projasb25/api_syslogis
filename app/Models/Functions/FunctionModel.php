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
        "SP_SEL_TEMPLATE" => [
            "query" => 'SELECT * FROM load_template WHERE status = "ACTIVO";',
            "params" => []
        ],
        "SP_INS_TEMPLATE" => [
            "query" => "CALL SP_INS_TEMPLATE(:id_load_template, :name,:description,:organization,:json_detail,:username, :status)",
            "params" => ['id_load_template', 'name', 'description', 'organization', 'json_detail','status', 'username']
        ],
        "SP_SEL_MASSIVE_LOADS" => [
            "query" => 'SELECT * FROM massive_load order by date_created desc;',
            "params" => []
        ],
        "SP_SEL_LOADS_DETAILS" => [
            "query" => 'SELECT * FROM massive_load_details WHERE id_massive_load = :id_massive_load;',
            "params" => ['id_massive_load']
        ],

        /**
         * Funciones para Transaccions
         **/
        "SP_INS_CORPORATION" => [
            'query' => 'CALL SP_INS_CORPORATION(:header, :details, :username)',
            'headers_params' => ['id_corporation', 'name', 'description', 'status'],
            'details_params' => ['id_organization', 'name', 'description', 'ruc', 'address', 'status', 'typeservices']
        ]
    ];

    public function getFunctions()
    {
        return $this->functions;
    }
}
