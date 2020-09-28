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
            "query" => 'CALL SP_SEL_TEMPLATE(:id_corporation, :id_organization)',
            "params" => ['id_corporation', 'id_organization']
        ],
        "SP_INS_TEMPLATE" => [
            "query" => "CALL SP_INS_TEMPLATE(:id_load_template, :name,:description,:json_detail,:username, :status, :id_corporation, :id_organization)",
            "params" => ['id_load_template', 'name', 'description', 'json_detail','status', 'username', 'id_corporation', 'id_organization']
        ],
        "SP_SEL_MASSIVE_LOADS" => [
            "query" => 'SELECT * FROM massive_load order by date_created desc;',
            "params" => []
        ],
        "SP_SEL_LOADS_DETAILS" => [
            "query" => 'SELECT * FROM massive_load_details WHERE id_massive_load = :id_massive_load;',
            "params" => ['id_massive_load']
        ],
        "SP_SEL_CORPORATIONS" => [
            "query" => 'SELECT * FROM corporation c WHERE status = "ACTIVO";',
            "params" => []
        ],
        "SP_SEL_ORGANIZATIONS" => [
            "query" => 'SELECT * FROM organization where status = "ACTIVO" and id_corporation = :id_corporation;',
            "params" => ['id_corporation']
        ],
        "SP_SEL_GUIDES" => [
            "query" => 'CALL SP_SEL_GUIDES(:username)',
            "params" => ['username']
        ],
        "SP_SEL_DRIVERS" => [
            "query" => 'CALL SP_SEL_DRIVERS(:status)',
            "params" => ['status']
        ],
        "SP_SEL_VEHICLES" => [
            "query" => 'CALL SP_SEL_VEHICLES(:status)',
            "params" => ['status']
        ],
        "SP_SEL_DOMAIN" => [
            "query" => 'CALL SP_SEL_DOMAIN(:domain_name)',
            "params" => ['domain_name']
        ],
        "SP_VEHICLE_DRIVER" => [
            "query" => 'CALL SP_VEHICLE_DRIVER()',
            "params" => []
        ],
        /**
         * Funciones para Transaccions
         **/
        "SP_INS_CORPORATION" => [
            'query' => 'CALL SP_INS_CORPORATION(:header, :details, :username)',
            'headers_params' => ['id_corporation', 'name', 'description', 'status'],
            'details_params' => ['id_organization', 'name', 'description', 'ruc', 'address', 'status', 'typeservices']
        ],
        "SP_INS_VEHICLE_DRIVER" => [
            'query' => 'CALL SP_INS_VEHICLE_DRIVER(:header, :details, :username)',
            'headers_params' => ["id_driver","first_name","last_name","doc_number","email","phone","status"],
            'details_params' => ["id_vehicle","vehicle_type","brand","model","plate_number","soat","status"]
        ],
        "SP_INS_DOMAIN" => [
            'query' => 'CALL SP_INS_DOMAIN(:header, :details, :username)',
            'headers_params' => [],
            'details_params' => ["id_domain","domain_name","domain_value","domain_description","status"]
        ]
    ];

    public function getFunctions()
    {
        return $this->functions;
    }
}
