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
            "query" => 'CALL SP_SEL_TEMPLATE(:username, :status)',
            "params" => ['username', 'status']
        ],
        "SP_INS_TEMPLATE" => [
            "query" => "CALL SP_INS_TEMPLATE(:id_load_template, :name,:description,:json_detail,:username, :status, :id_corporation, :id_organization)",
            "params" => ['id_load_template', 'name', 'description', 'json_detail','status', 'username', 'id_corporation', 'id_organization']
        ],
        "SP_SEL_MASSIVE_LOADS" => [
            "query" => 'CALL SP_SEL_MASSIVE_LOADS(:username)',
            "params" => ['username']
        ],
        "SP_SEL_LOADS_DETAILS" => [
            "query" => 'SELECT * FROM massive_load_details WHERE id_massive_load = :id_massive_load;',
            "params" => ['id_massive_load']
        ],
        "SP_SEL_ORGANIZATIONS" => [
            "query" => 'CALL SP_SEL_ORGANIZATIONS(:status, :id_corporation, :type)',
            "params" => ['status', 'id_corporation', 'type']
        ],
        "SP_SEL_GUIDES" => [
            "query" => 'CALL SP_SEL_GUIDES(:username)',
            "params" => ['username']
        ],
        "SP_SEL_DRIVERS" => [
            "query" => 'CALL SP_SEL_DRIVERS(:status, :current_corp, :current_org)',
            "params" => ['status', 'current_corp', 'current_org']
        ],
        "SP_SEL_VEHICLES" => [
            "query" => 'CALL SP_SEL_VEHICLES(:status)',
            "params" => ['status']
        ],
        "SP_SEL_DOMAIN" => [
            "query" => 'CALL SP_SEL_DOMAIN(:domain_name, :status)',
            "params" => ['domain_name', 'status']
        ],
        "SP_VEHICLE_DRIVER" => [
            "query" => 'CALL SP_VEHICLE_DRIVER()',
            "params" => []
        ],
        "SP_CREATE_SHIPPING_ORDER" => [
            "query" => 'CALL SP_CREATE_SHIPPING_ORDER(:id_vehicle, :guide_ids, :username)',
            "params" => ['id_vehicle', 'guide_ids', 'username']
        ],
        "SP_SEL_SHIPPING_ORDERS" => [
            "query" => 'CALL SP_SEL_SHIPPING_ORDERS(:status, :current_corp, :current_org)',
            "params" => ['status', 'current_corp', 'current_org']
        ],
        "SP_SEL_USER" => [
            "query" => 'CALL SP_SEL_USER(:status)',
            "params" => ['status']
        ],
        "SP_SEL_CORPORATIONS" => [
            "query" => 'CALL SP_SEL_CORPORATIONS(:status)',
            "params" => ['status']
        ],
        "SP_SEL_ORGUSER" => [
            "query" => 'CALL SP_SEL_ORGUSER(:status, :id_user)',
            "params" => ['status', 'id_user']
        ],
        "SP_SEL_ROLE" => [
            "query" => 'CALL SP_SEL_ROLE(:status)',
            "params" => ['status']
        ],
        /**
         * Funciones para Transaccions
         **/
        "SP_INS_CORPORATION" => [
            'query' => 'CALL SP_INS_CORPORATION(:header, :details, :username)',
            'headers_params' => ['id_corporation', 'name', 'description', 'status'],
            'details_params' => ['id_organization', 'name', 'description', 'ruc', 'address', 'status', 'type']
        ],
        "SP_INS_VEHICLE_DRIVER" => [
            'query' => 'CALL SP_INS_VEHICLE_DRIVER(:header, :details, :username)',
            'headers_params' => ["id_driver","first_name","last_name","doc_number","email","phone","status"],
            'details_params' => ["id_vehicle","vehicle_type","brand","model","plate_number","soat","status"]
        ],
        "SP_INS_DOMAIN" => [
            'query' => 'CALL SP_INS_DOMAIN(:header, :details, :username)',
            'headers_params' => [],
            'details_params' => []
        ],
        "SP_INS_USER" => [
            'query' => 'CALL SP_INS_USER(:header, :details, :username)',
            'headers_params' => ["id_user","username","first_name","last_name","doc_type","doc_number","user_email","password","status", "type"],
            'details_params' => []
            // "id_orguser", "id_corporation", "id_organization","id_role","bydefault","status"
        ]
    ];

    public function getFunctions()
    {
        return $this->functions;
    }
}
