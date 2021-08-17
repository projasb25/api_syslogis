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
        "SP_ASSIGN_ORDER_WEB" => [
            "query" => 'CALL SP_ASSIGN_ORDER_WEB(:id_order, :id_driver, :username)',
            "params" => ['id_order', 'id_driver', 'username']
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
            "query" => 'CALL SP_INS_USER(:id, :user, :first_name, :last_name, :doc_type, :doc_number, :user_email, :phone, :password, :status, :type, :username, :id_role)',
            "params" => ['id', 'user', 'first_name', 'last_name', 'doc_type', 'doc_number', 'user_email', 'phone', 'password', 'status', 'type', 'username', 'id_role']
        ],
        "SP_SEL_DRIVER" => [
            "query" => 'CALL SP_SEL_DRIVER(:type, :status)',
            "params" => ['type', 'status']
        ],
        "SP_INS_DRIVER" => [
            "query" => 'CALL SP_INS_DRIVER(:id_new_user, :new_username, :password, :first_name, :last_name, :doc_type, :doc_number, :user_email, :phone, :type, :status, :license, :license_expire_date, :plate_number, :soat, :id_user, :username)',
            "params" => ['id_new_user', 'new_username', 'password', 'first_name', 'last_name', 'doc_type', 'doc_number', 'user_email', 'phone', 'type', 'status', 'license', 'license_expire_date', 'plate_number', 'soat', 'id_user', 'username']
        ],
        "SP_MASSIVE_LOAD_DETAILS" => [
            "query" => 'SELECT * FROM massive_load_details WHERE id_massive_load = :id_massive_load;',
            "params" => ['id_massive_load']
        ],
        "SP_SEL_ORDER_IMAGE" => [
            "query" => 'CALL SP_SEL_ORDER_IMAGE(:id_order)',
            "params" => ['id_order']
        ],
        "SP_SEL_MOTIVES" => [
            "query" => 'CALL SP_SEL_MOTIVES(:type)',
            "params" => ['type']
        ],
        "SP_REASIGN_ORDER" => [
            "query" => 'CALL SP_REASIGN_ORDER(:id_order,:id_driver,:username)',
            "params" => ['id_order','id_driver','username']
        ],
        "SP_SEL_CLIENT" => [
            "query" => 'CALL SP_SEL_CLIENT(:status)',
            "params" => ['status']
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
            'headers_params' => ['order_pickup_address', 'order_pickup_reference', 'order_pickup_ubigeo', 'order_pickup_contact_name', 'order_pickup_contact_phone', 'order_delivery_address', 'order_delivery_reference', 'order_delivery_ubigeo', 'order_delivery_contact_name', 'order_delivery_contact_phone','order_pickup_lat','order_pickup_lng','order_delivery_lat','order_delivery_lng'],
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
        ],
        "SP_SEL_ORDER_USER" => [
            "query" => 'CALL SP_SEL_ORDER_USER(?,?,?,?)',
            "params" => []
        ],
        "SP_SEL_ORDER_USER_COUNT" => [
            "query" => 'CALL SP_SEL_ORDER_USER_COUNT(?,?)',
            "params" => []
        ],
        "SP_SEL_MASSIVE_LOAD" => [
            "query" => 'CALL SP_SEL_MASSIVE_LOAD(?,?,?,?)',
            "params" => []
        ],
        "SP_SEL_MASSIVE_LOAD_COUNT" => [
            "query" => 'CALL SP_SEL_MASSIVE_LOAD_COUNT(?,?)',
            "params" => []
        ]
        
    ];

    public function getFunctions()
    {
        return $this->functions;
    }
}
