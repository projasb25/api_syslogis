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
            "query" => 'CALL SP_SEL_CLIENT_ORDER(:status,:id_user)',
            "params" => ['status','id_user']
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
            "query" => 'CALL SP_ASIG_ORDER(:id_order, :id_driver)',
            "params" => ['id_order', 'id_driver']
        ],
        "SP_CHANGE_ORDER_STATUS" => [
            "query" => 'CALL SP_CHANGE_ORDER_STATUS(:id_order, :status)',
            "params" => ['id_order', 'status']
        ],
        "SP_LIS_DRIVER_ORDER" => [
            "query" => 'CALL SP_LIS_DRIVER_ORDER(:id_driver)',
            "params" => ['id_driver']
        ],
        /**
         * Funciones para Transaccions
         **/
        "SP_INS_ORDER" => [
            'query' => 'CALL SP_INS_ORDER(:header, :details, :username)',
            'headers_params' => ['order_pickup_address', 'order_pickup_reference', 'order_pickup_ubigeo', 'order_pickup_contact_name', 'order_pickup_contact_phone', 'order_delivery_address', 'order_delivery_reference', 'order_delivery_ubigeo', 'order_delivery_contact_name', 'order_delivery_contact_phone'],
            'details_params' => ['product_name', 'product_quantity', 'product_images']
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
