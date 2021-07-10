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
        ],
        "SP_SEL_CLIENT_ORDER" => [
            "query" => 'CALL SP_SEL_CLIENT_ORDER(:status,:id_user, :type)',
            "params" => ['status','id_user', 'type']
        ],
        "SP_SEL_ORDER_DETAIL" => [
            "query" => 'CALL SP_SEL_ORDER_DETAIL(:id_order,:id_user)',
            "params" => ['id_order','id_user']
        ],
        "SP_SEL_ORDER_TRACKING" => [
            "query" => 'CALL SP_SEL_ORDER_TRACKING(:id_order,:id_user)',
            "params" => ['id_order','id_user']
        ],
        "SP_SEL_ORDER_STATUS" => [
            "query" => 'CALL SP_SEL_ORDER_STATUS()',
            "params" => []
        ],
        "SP_ASIG_ORDER" => [
            "query" => 'CALL SP_ASIG_ORDER(:id_order, :id_user)',
            "params" => ['id_order', 'id_user']
        ],
        "SP_CHANGE_ORDER_STATUS" => [
            "query" => 'CALL SP_CHANGE_ORDER_STATUS(:id_order, :status)',
            "params" => ['id_order', 'status']
        ],
        "SP_LIS_DRIVER_ORDER" => [
            "query" => 'CALL SP_LIS_DRIVER_ORDER(:id_user)',
            "params" => ['id_user']
        ],
        "SP_SEL_ROLES" => [
            "query" => 'CALL SP_SEL_ROLES(:status)',
            "params" => ['status']
        ],
        "SP_SEL_ROLEAPPLICATION" => [
            "query" => 'CALL SP_SEL_ROLEAPPLICATION(:id_role)',
            "params" => ['id_role']
        ],
        "SP_INS_ROLE" => [
            "query" => 'CALL SP_INS_ROLE(:id_role, :description, :status, :username)',
            "params" => ['id_role', 'description', 'status', 'username']
        ],
        "SP_SEL_DOMAIN" => [
            "query" => 'CALL SP_SEL_DOMAIN(:domain_name, :status)',
            "params" => ['domain_name', 'status']
        ],
        "SP_INS_PROPERTIES" => [
            "query" => 'CALL SP_INS_PROPERTIES(:id_properties, :name, :value, :status)',
            "params" => ['id_properties', 'name', 'value', 'status']
        ],
        "SP_SEL_USER" => [
            "query" => 'CALL SP_SEL_USER(:type, :status)',
            "params" => ['type', 'status']
        ],
        "SP_INS_CLIENT" => [
            "query" => 'CALL SP_INS_CLIENT(:id_user, :username, :first_name, :last_name, :doc_type, :doc_number, :user_email, :phone, :password, :status, :type, :createdby)',
            "params" => ['id_user', 'username', 'first_name', 'last_name', 'doc_type', 'doc_number', 'user_email', 'phone', 'password', 'status', 'type', 'createdby']
        ],
        "SP_INS_USER" => [
            "query" => 'CALL SP_INS_USER(:id, :id_role, :user, :first_name, :last_name, :doc_type, :doc_number, :user_email, :phone, :password, :status, :type)',
            "params" => ['id', 'id_role', 'user', 'first_name', 'last_name', 'doc_type', 'doc_number', 'user_email', 'phone', 'password', 'status', 'type']
        ],
        "SP_SEL_DRIVER" => [
            "query" => 'CALL SP_SEL_DRIVER(:type, :status)',
            "params" => ['type', 'status']
        ],
        "SP_INS_DRIVER" => [
            "query" => 'CALL SP_INS_DRIVER(:id_driver, :id_user, :license, :license_expire_date, :plate_number, :soat, :matpel_number, :matpel_exp_date, :type, :status)',
            "params" => ['id_driver', 'id_user', 'license', 'license_expire_date', 'plate_number', 'soat', 'matpel_number', 'matpel_exp_date', 'type', 'status']
        ],
        /**
         * Funciones para Transaccions
         **/
        "SP_INS_ROLEAPPLICATION" => [
            'query' => 'CALL SP_INS_ROLEAPPLICATION(:header, :details, :username)',
            'headers_params' => [],
            'details_params' => ['id_roleapplication','id_role','id_application','view','modify','insert','delete']
        ],
        "SP_INS_ORDER" => [
            'query' => 'CALL SP_INS_ORDER(:header, :details, :username)',
            'headers_params' => ['order_pickup_address', 'order_pickup_reference', 'order_pickup_ubigeo', 'order_pickup_contact_name', 'order_pickup_contact_phone', 'order_delivery_address', 'order_delivery_reference', 'order_delivery_ubigeo', 'order_delivery_contact_name', 'order_delivery_contact_phone'],
            'details_params' => ['product_name', 'product_quantity', 'product_images']
        ],
        "SP_INS_DOMAIN" => [
            'query' => 'CALL SP_INS_DOMAIN(:header, :details, :username)',
            'headers_params' => [],
            'details_params' => []
        ],

        /**
         * Funciones para Paginacion
         **/
        "SP_SEL_CLIENT_ORDER_P" => [
            "query" => 'CALL SP_SEL_CLIENT_ORDER_P(?,?,?,?)',
            "params" => []
        ],
        "SP_SEL_CLIENT_ORDER_COUNT" => [
            "query" => 'CALL SP_SEL_CLIENT_ORDER_COUNT(?,?)',
            "params" => []
        ]
        
    ];

    public function getFunctions()
    {
        return $this->functions;
    }
}
