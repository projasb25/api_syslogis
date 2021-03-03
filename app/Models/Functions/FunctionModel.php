<?php

namespace App\Models\Functions;

use Illuminate\Http\Request;
use Log;

class FunctionModel
{
    private $functions = [
        "SP_SEL_ROLE" => [
            "query" => 'CALL SP_SEL_ROLES(:status)',
            "params" => ['status']
        ],
        "SP_INS_ROLE" => [
            "query" => 'CALL SP_INS_ROLE(:id_role,:id_corporation,:description,:status,:username)',
            "params" => ['id_role','id_corporation','description','status','username']
        ],
        "SP_SEL_DOMAIN" => [
            "query" => 'CALL SP_SEL_DOMAIN(:domain_name, :status)',
            "params" => ['domain_name', 'status']
        ],
        "SP_SEL_ROLEAPPLICATION" => [
            "query" => 'CALL SP_SEL_ROLEAPPLICATION(:id_corporation, :id_role)',
            "params" => ['id_corporation', 'id_role']
        ],
        "SP_SEL_USER" => [
            "query" => 'CALL SP_SEL_USER(:status)',
            "params" => ['status']
        ],
        "SP_SEL_CLIENT" => [
            "query" => 'CALL SP_SEL_CLIENT(:username, :id_corporation, :id_organization, :status)',
            "params" => ['username', 'id_corporation', 'id_organization', 'status']
        ],
        "SP_SEL_CLIENT_STORE" => [
            "query" => 'CALL SP_SEL_CLIENT_STORE(:id_client, :status)',
            "params" => ['id_client', 'status']
        ],
        "SP_INS_LOAD_TEMPLATE" => [
            "query" => 'CALL SP_INS_LOAD_TEMPLATE(:id_load_template,:id_corporation,:id_organization,:id_client,:name,:description,:json_detail,:status,:username, :type)',
            "params" => ['id_load_template','id_corporation','id_organization','id_client','name','description','json_detail','status','username','type']
        ],
        "SP_SEL_LOAD_TEMPLATE" => [
            "query" => 'CALL SP_SEL_LOAD_TEMPLATE(:username, :id_corporation, :id_organization, :status, :type)',
            "params" => ['username', 'id_corporation', 'id_organization', 'status', 'type']
        ],
        "SP_SEL_BILL_LOAD_DETAILS" => [
            "query" => 'SELECT * FROM bill_load_detail WHERE id_bill_load = :id_bill_load;',
            "params" => ['id_bill_load']
        ],
        "SP_INS_BUYER" => [
            "query" => 'CALL SP_INS_BUYER(:id_buyer,:id_corporation,:id_organization,:company_name,:doc_type,:doc_number,:buyer_email,:status,:username,:address)',
            "params" => ['id_buyer','id_corporation','id_organization', 'company_name','doc_type','doc_number','buyer_email','status','username','address']
        ],
        "SP_SEL_BUYER" => [
            "query" => 'CALL SP_SEL_BUYER(:id_corporation, :id_organization, :status, :doc_number)',
            "params" => ['id_corporation', 'id_organization', 'status', 'doc_number']
        ],
        "SP_SEL_PURCHASE_ORDER_DETAILS" => [
            "query" => 'SELECT * FROM purchase_order_detail WHERE id_purchase_order = :id_purchase_order;',
            "params" => ['id_purchase_order']
        ],
        "SP_SEL_KARDEX" => [
            "query" => 'CALL SP_SEL_KARDEX(:id_product, :id_inventory, :desde, :hasta)',
            "params" => ['id_product', 'id_inventory', 'desde', 'hasta']
        ],
        "SP_INS_PRODUCTO" => [
            "query" => 'CALL SP_INS_PRODUCTO(:id_product,:product_code,:product_alt_code1,:product_alt_code2,:product_description,:product_serie,:product_lots,:product_exp_date,:product_available,:product_color,:product_size,:product_package_number,:product_unitp_box,:product_cmtr_pbox,:product_cmtr_quantity,:username)',
            "params" => ['id_product','product_code','product_alt_code1','product_alt_code2','product_description','product_serie','product_lots','product_exp_date','product_available','product_color','product_size','product_package_number','product_unitp_box','product_cmtr_pbox','product_cmtr_quantity','username']
        ],
        "SP_SEL_ORGUSER" => [
            "query" => 'CALL SP_SEL_ORGUSER(:status, :id_user)',
            "params" => ['status', 'id_user']
        ],
        "SP_SEL_ORGANIZATIONS" => [
            "query" => 'CALL SP_SEL_ORGANIZTIONS(:status, :corpid, :username, :id_corporation)',
            "params" => ['status', 'corpid', 'username', 'id_corporation']
        ],
        "SP_INS_PROVIDER" => [
            "query" => 'CALL SP_INS_PROVIDER(:id_provider,:name,:ruc,:responsible_name,:responsible_phone,:responsible_email,:description,:address,:status,:username, :id_corporation, :id_organization)',
            "params" => ['id_provider','name','ruc','responsible_name','responsible_phone','responsible_email','description','address','status','username','id_corporation', 'id_organization']
        ],
        "SP_SEL_PROVIDERS" => [
            "query" => 'CALL SP_SEL_PROVIDERS(:username, :id_corporation, :id_organization, :status)',
            "params" => ['username', 'id_corporation', 'id_organization', 'status']
        ],
        "SP_INS_VEHICLE" => [
            "query" => 'CALL SP_INS_VEHICLE(:id_vehicle,:id_provider,:id_corporation,:id_organization,:brand,:model,:plate_number,:soat,:driver_license,:status,:username)',
            "params" => ['id_vehicle','id_provider','id_corporation','id_organization','brand','model','plate_number','soat','driver_license','status','username']
        ],
        "SP_SEL_VEHICLE" => [
            "query" => 'CALL SP_SEL_VEHICLE(:username, :id_corporation, :id_organization, :status)',
            "params" => ['username', 'id_corporation', 'id_organization', 'status']
        ],
        "SP_SEL_CORPORATIONS" => [
            "query" => 'CALL SP_SEL_CORPORATIONS(:status,:username)',
            "params" => ['status', 'username']
        ],
        "SP_ELIMINAR_CARGA" => [
            "query" => 'CALL SP_ELIMINAR_CARGA(:type,:id_document)',
            "params" => ['type', 'id_document']
        ],
        "SP_INS_PROPERTIES" => [
            "query" => 'CALL SP_INS_PROPERTIES(:id_properties, :name, :value, :status)',
            "params" => ['id_properties', 'name', 'value', 'status']
        ],





        
        /**
         * Funciones para Transaccions
         **/
        "SP_INS_ROLEAPPLICATION" => [
            'query' => 'CALL SP_INS_ROLEAPPLICATION(:header, :details, :username)',
            'headers_params' => [],
            'details_params' => ['id_roleapplication','id_role','id_application','view','modify','insert','delete']
        ],
        "SP_INS_DOMAIN" => [
            'query' => 'CALL SP_INS_DOMAIN(:header, :details, :username)',
            'headers_params' => [],
            'details_params' => []
        ],
        "SP_INS_CLIENTS" => [
            'query' => 'CALL SP_INS_CLIENTS(:header, :details, :username)',
            'headers_params' => ['id_client','doc_type','document','company_name','category','status'],
            'details_params' => ['id_client_store','store_name','description','address','status']
        ],
        "SP_INS_USER" => [
            'query' => 'CALL SP_INS_USER(:header, :details, :username)',
            'headers_params' => ["id_user","username","first_name","last_name","doc_type","doc_number","user_email","password","status", "type", "id_role"],
            'details_params' => []
            // "id_orguser", "id_corporation", "id_organization","id_role","bydefault","status"
        ],
        "SP_INS_CORPORATION" => [
            'query' => 'CALL SP_INS_CORPORATION(:header, :details, :username)',
            'headers_params' => ['id_corporation', 'name', 'description', 'status', 'doc_type', 'document'],
            'details_params' => ['id_organization', 'name', 'description', 'address', 'status', 'type']
        ],

        /**
         * Funciones para Paginacion
         **/
        "SP_SEL_BILL_LOADS" => [
            "query" => 'CALL SP_SEL_BILL_LOADS(?,?,?,?)',
            "params" => []
        ],
        "SP_SEL_BILL_LOADS_COUNT" => [
            "query" => 'CALL SP_SEL_BILL_LOADS_COUNT(?,?)',
            "params" => []
        ],
        "SP_SEL_PURCHASE_ORDER" => [
            "query" => 'CALL SP_SEL_PURCHASE_ORDER(?,?,?,?)',
            "params" => []
        ],
        "SP_SEL_PURCHASE_ORDER_COUNT" => [
            "query" => 'CALL SP_SEL_PURCHASE_ORDER_COUNT(?,?)',
            "params" => []
        ],
        "SP_REPORTE_INVENTARIO" => [
            "query" => 'CALL SP_REPORTE_INVENTARIO(?,?,?,?)',
            "params" => []
        ],
        "SP_REPORTE_INVENTARIO_COUNT" => [
            "query" => 'CALL SP_REPORTE_INVENTARIO_COUNT(?,?)',
            "params" => []
        ],
        "SP_REPORTE_INVENTARIO_PRODUCTO" => [
            "query" => 'CALL SP_REPORTE_INVENTARIO_PRODUCTO(?,?,?,?)',
            "params" => []
        ],
        "SP_REPORTE_INVENTARIO_PRODUCTO_COUNT" => [
            "query" => 'CALL SP_REPORTE_INVENTARIO_PRODUCTO_COUNT(?,?)',
            "params" => []
        ],











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
        "SP_SEL_GUIDES" => [
            "query" => 'CALL SP_SEL_GUIDES(:username)',
            "params" => ['username']
        ],
        "SP_SEL_DRIVERS" => [
            "query" => 'CALL SP_SEL_DRIVERS(:status, :current_corp, :current_org)',
            "params" => ['status', 'current_corp', 'current_org']
        ],
        "SP_SEL_VEHICLES" => [
            "query" => 'CALL SP_SEL_VEHICLES(:status, :id_provider)',
            "params" => ['status', 'id_provider']
        ],
        "SP_VEHICLE_DRIVER" => [
            "query" => 'CALL SP_VEHICLE_DRIVER()',
            "params" => []
        ],
        "SP_CREATE_SHIPPING_ORDER" => [
            "query" => 'CALL SP_CREATE_SHIPPING_ORDER(:id_vehicle, :id_driver, :quadrant_name, :guide_ids, :username)',
            "params" => ['id_vehicle', 'id_driver', 'quadrant_name','guide_ids', 'username']
        ],
        "SP_SEL_SHIPPING_ORDERS" => [
            "query" => 'CALL SP_SEL_SHIPPING_ORDERS(:status, :username)',
            "params" => ['status', 'username']
        ],        
        "SP_UPDATE_ADDRESS" => [
            "query" => 'CALL SP_UPDATE_ADDRESS(:id_guide, :latitude, :longitude)',
            "params" => ['id_guide', 'latitude', 'longitude']
        ],
        "SP_SEL_SHIPPING_DETAIL" => [
            "query" => 'CALL SP_SEL_SHIPPING_DETAIL(:id_shipping_order)',
            "params" => ['id_shipping_order']
        ],
        "SP_SEL_IMG_GUIDES" => [
            "query" => 'CALL SP_SEL_IMG_GUIDES(:id_guide)',
            "params" => ['id_guide']
        ],
        "SP_SEL_GUIDE_TRACKING" => [
            "query" => 'CALL SP_SEL_GUIDE_TRACKING(:id_guide)',
            "params" => ['id_guide']
        ],
        "SP_SEL_GUIDE_BY_BARCODE" => [
            "query" => 'CALL SP_SEL_GUIDE_BY_BARCODE(:client_barcode, :filterBy, :username)',
            "params" => ['client_barcode', 'filterBy', 'username']
            //"params" => ['client_barcode', 'seg_code', 'alt_code1', 'username']
        ],
        "SP_SEL_GUIDE_INFO" => [
            "query" => 'CALL SP_SEL_GUIDE_INFO(:id_guide)',
            "params" => ['id_guide']
        ],
        "SP_SEL_GUIDE_STATUS" => [
            "query" => 'CALL SP_SEL_GUIDE_STATUS(:id_guide)',
            "params" => ['id_guide']
        ],
        "SP_DASHBOARD_CLIENTE" => [
            "query" => 'CALL SP_DASHBOARD_CLIENTE(:desde, :hasta, :id_corporation, :id_organization)',
            "params" => ['desde', 'hasta', 'id_corporation', 'id_organization']
        ],
        "SP_DASHBOARD_PROVEEDOR" => [
            "query" => 'CALL SP_DASHBOARD_PROVEEDOR(:desde, :hasta, :id_provider)',
            "params" => ['desde', 'hasta', 'id_provider']
        ],
        "SP_DASHBOARD_EFECTIVIDAD_HORAS" => [
            "query" => 'CALL SP_DASHBOARD_EFECTIVIDAD_HORAS(:desde, :hasta, :id_corporation, :id_organization)',
            "params" => ['desde', 'hasta', 'id_corporation', 'id_organization']
        ],
        "SP_REPORTE_CONTROL" => [
            "query" => 'CALL SP_REPORTE_CONTROL(:desde, :hasta, :username)',
            "params" => ['desde', 'hasta', 'username']
        ],
        "SP_REPORTE_TORRE_CONTROL" => [
            "query" => 'CALL SP_REPORTE_TORRE_CONTROL(:desde, :hasta, :username)',
            "params" => ['desde', 'hasta', 'username']
        ],
        "SP_REPORTE_CONTROL_SKU" => [
            "query" => 'CALL SP_REPORTE_CONTROL_SKU(:desde, :hasta, :username)',
            "params" => ['desde', 'hasta', 'username']
        ],
        "SP_REPORTE_ASIGNACION_POR_GUIA" => [
            "query" => 'CALL SP_REPORTE_ASIGNACION_POR_GUIA(:desde, :hasta, :username)',
            "params" => ['desde', 'hasta', 'username']
        ],
        "SP_REASIGNAR_ENVIO" => [
            "query" => 'CALL SP_REASIGNAR_ENVIO(:id_shipping_order, :id_vehicle, :id_driver, :username)',
            "params" => ['id_shipping_order', 'id_vehicle', 'id_driver', 'username']
        ],
        "SP_ELIMINAR_ENVIO" => [
            "query" => 'CALL SP_ELIMINAR_ENVIO(:id_shipping_order, :username)',
            "params" => ['id_shipping_order', 'username']
        ],
        "SP_DEL_MASSIVE_LOAD" => [
            "query" => 'CALL SP_DEL_MASSIVE_LOAD(:id_massive_load, :username)',
            "params" => ['id_massive_load', 'username']
        ],
        "SP_ELIMINAR_GUIA" => [
            "query" => 'CALL SP_ELIMINAR_GUIA(:id_guide, :username)',
            "params" => ['id_guide', 'username']
        ],
        "SP_DESASIGNAR_GUIA" => [
            "query" => 'CALL SP_DESASIGNAR_GUIA(:id_guide, :username)',
            "params" => ['id_guide', 'username']
        ],
        "SP_SEL_PROPERTIES" => [
            "query" => 'CALL SP_SEL_PROPERTIES(:name, :status)',
            "params" => ['name', 'status']
        ],
        "SP_ELIMINAR_IMAGEN" => [
            "query" => 'CALL SP_ELIMINAR_IMAGEN(:url, :id_shipping_order)',
            "params" => ['url', 'id_shipping_order']
        ],
        "SP_SEL_MOTIVES" => [
            "query" => 'CALL SP_SEL_MOTIVES()',
            "params" => []
        ],
        "SP_CAMBIAR_ESTADO" => [
            "query" => 'CALL SP_CAMBIAR_ESTADO(:id_guide,:status,:motive,:username)',
            "params" => ['id_guide', 'status', 'motive', 'username']
        ],
        /**
         * Funciones para Paginacion
         **/

        "SP_SEL_GUIDES_P" => [
            "query" => 'CALL SP_SEL_GUIDES_P(?,?,?)',
            "params" => ['id_massive_load', 'username']
        ],
        "SP_SEL_GUIDES_PCOUNT" => [
            "query" => 'CALL SP_SEL_GUIDES_PCOUNT(?,?,?)',
            "params" => ['id_massive_load', 'username']
        ],
        "SP_SEL_IMG_MONITOR" => [
            "query" => 'CALL SP_SEL_IMG_MONITOR(?,?,?)',
            "params" => ['id_massive_load', 'username']
        ],
        "SP_SEL_IMG_MONITOR_COUNT" => [
            "query" => 'CALL SP_SEL_IMG_MONITOR_COUNT(?,?,?)',
            "params" => ['id_massive_load', 'username']
        ],

        /**
         * Funciones para Transaccions
         **/        
        "SP_INS_VEHICLE_DRIVER" => [
            'query' => 'CALL SP_INS_VEHICLE_DRIVER(:header, :details, :username)',
            'headers_params' => ["id_driver","first_name","last_name","doc_number", "doc_type","email","phone","status","password"],
            'details_params' => ["id_vehicle", "id_provider", "vehicle_type","brand","model","plate_number","soat","status"]
        ],
        "SP_UPDATE_SHIPPING_ORDER" => [
            'query' => 'CALL SP_UPDATE_SHIPPING_ORDER(:header, :details, :username)',
            'headers_params' => [],
            'details_params' => ["id_shipping_order", "id_shipping_order_detail", "id_guide", "operation"]
        ]
    ];

    public function getFunctions()
    {
        return $this->functions;
    }
}
