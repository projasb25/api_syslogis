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
            "query" => 'CALL SP_SEL_TEMPLATE(:username, :status, :type)',
            "params" => ['username', 'status', 'type']
        ],
        "SP_INS_TEMPLATE" => [
            "query" => "CALL SP_INS_TEMPLATE(:id_load_template, :name,:description,:json_detail,:username, :status, :id_corporation, :id_organization, :type)",
            "params" => ['id_load_template', 'name', 'description', 'json_detail','status', 'username', 'id_corporation', 'id_organization', 'type']
        ],
        "SP_SEL_MASSIVE_LOADS" => [
            "query" => 'CALL SP_SEL_MASSIVE_LOADS(:username, :type)',
            "params" => ['username', 'type']
        ],
        "SP_SEL_LOADS_DETAILS" => [
            "query" => 'SELECT * FROM massive_load_details WHERE id_massive_load = :id_massive_load;',
            "params" => ['id_massive_load']
        ],
        "SP_SEL_ORGANIZATIONS" => [
            "query" => 'CALL SP_SEL_ORGANIZATIONS(:status, :id_corporation, :type, :username)',
            "params" => ['status', 'id_corporation', 'type', 'username']
        ],
        "SP_SEL_GUIDES" => [
            "query" => 'CALL SP_SEL_GUIDES(:username, :type)',
            "params" => ['username', 'type']
        ],
        "SP_SEL_DRIVERS" => [
            "query" => 'CALL SP_SEL_DRIVERS(:status, :current_corp, :current_org)',
            "params" => ['status', 'current_corp', 'current_org']
        ],
        "SP_SEL_VEHICLES" => [
            "query" => 'CALL SP_SEL_VEHICLES(:status, :id_provider)',
            "params" => ['status', 'id_provider']
        ],
        "SP_SEL_DOMAIN" => [
            "query" => 'CALL SP_SEL_DOMAIN(:domain_name, :status)',
            "params" => ['domain_name', 'status']
        ],
        "SP_VEHICLE_DRIVER" => [
            "query" => 'CALL SP_VEHICLE_DRIVER(:username)',
            "params" => ['username']
        ],
        "SP_CREATE_SHIPPING_ORDER" => [
            "query" => 'CALL SP_CREATE_SHIPPING_ORDER(:id_vehicle, :id_driver, :quadrant_name, :guide_ids, :username, :type)',
            "params" => ['id_vehicle', 'id_driver', 'quadrant_name','guide_ids', 'username', 'type']
        ],
        "SP_SEL_SHIPPING_ORDERS" => [
            "query" => 'CALL SP_SEL_SHIPPING_ORDERS(:status, :username, :type)',
            "params" => ['status', 'username', 'type']
        ],
        "SP_SEL_USER" => [
            "query" => 'CALL SP_SEL_USER(:status)',
            "params" => ['status']
        ],
        "SP_SEL_CORPORATIONS" => [
            "query" => 'CALL SP_SEL_CORPORATIONS(:status, :username)',
            "params" => ['status', 'username']
        ],
        "SP_SEL_ORGUSER" => [
            "query" => 'CALL SP_SEL_ORGUSER(:status, :id_user)',
            "params" => ['status', 'id_user']
        ],
        "SP_SEL_ROLE" => [
            "query" => 'CALL SP_SEL_ROLE(:status)',
            "params" => ['status']
        ],
        "SP_UPDATE_ADDRESS" => [
            "query" => 'CALL SP_UPDATE_ADDRESS(:id_guide, :latitude, :longitude)',
            "params" => ['id_guide', 'latitude', 'longitude']
        ],
        "SP_SEL_PROVIDERS" => [
            "query" => 'CALL SP_SEL_PROVIDERS(:status, :username)',
            "params" => ['status', 'username']
        ],
        "SP_INS_PROVIDER" => [
            "query" => 'CALL SP_INS_PROVIDER(:id_provider,:name,:ruc,:responsible_name,:responsible_phone,:responsible_email,:description,:address,:status,:username)',
            "params" => ['id_provider','name','ruc','responsible_name','responsible_phone','responsible_email','description','address','status','username']
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
            "query" => 'CALL SP_DASHBOARD_CLIENTE(:desde, :hasta, :id_corporation, :id_organization, :type)',
            "params" => ['desde', 'hasta', 'id_corporation', 'id_organization', 'type']
        ],
        "SP_DASHBOARD_PROVEEDOR" => [
            "query" => 'CALL SP_DASHBOARD_PROVEEDOR(:desde, :hasta, :id_provider, :type)',
            "params" => ['desde', 'hasta', 'id_provider', 'type']
        ],
        "SP_DASHBOARD_EFECTIVIDAD_HORAS" => [
            "query" => 'CALL SP_DASHBOARD_EFECTIVIDAD_HORAS(:desde, :hasta, :id_corporation, :id_organization)',
            "params" => ['desde', 'hasta', 'id_corporation', 'id_organization']
        ],
        "SP_REPORTE_CONTROL" => [
            "query" => 'CALL SP_REPORTE_CONTROL(:desde, :hasta, :username, :type)',
            "params" => ['desde', 'hasta', 'username', 'type']
        ],
        "SP_REPORTE_TORRE_CONTROL" => [
            "query" => 'CALL SP_REPORTE_TORRE_CONTROL(:desde, :hasta, :username, :type)',
            "params" => ['desde', 'hasta', 'username', 'type']
        ],
        "SP_REPORTE_CONTROL_SKU" => [
            "query" => 'CALL SP_REPORTE_CONTROL_SKU(:desde, :hasta, :username, :type)',
            "params" => ['desde', 'hasta', 'username', 'type']
        ],
        "SP_REPORTE_ASIGNACION_POR_GUIA" => [
            "query" => 'CALL SP_REPORTE_ASIGNACION_POR_GUIA(:desde, :hasta, :username, :type)',
            "params" => ['desde', 'hasta', 'username', 'type']
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
        "SP_INS_PROPERTIES" => [
            "query" => 'CALL SP_INS_PROPERTIES(:id_properties, :name, :value, :status)',
            "params" => ['id_properties', 'name', 'value', 'status']
        ],
        "SP_ELIMINAR_IMAGEN" => [
            "query" => 'CALL SP_ELIMINAR_IMAGEN(:url, :id_shipping_order)',
            "params" => ['url', 'id_shipping_order']
        ],
        "SP_SEL_MOTIVES" => [
            "query" => 'CALL SP_SEL_MOTIVES(:type)',
            "params" => ['type']
        ],
        "SP_CAMBIAR_ESTADO" => [
            "query" => 'CALL SP_CAMBIAR_ESTADO(:id_guide,:status,:motive,:username)',
            "params" => ['id_guide', 'status', 'motive', 'username']
        ],
        "SP_SEL_COLLECT_LOAD" => [
            "query" => 'CALL SP_SEL_COLLECT_LOAD(:username)',
            "params" => ['username']
        ],
        "SP_SEL_COLLECT_LOAD_DETAIL" => [
            "query" => 'SELECT * FROM collect_load_detail WHERE id_collect_load = :id_collect_load;',
            "params" => ['id_collect_load']
        ],
        "SP_REPORTE_RECOLECCION" => [
            "query" => 'CALL SP_REPORTE_RECOLECCION(:desde, :hasta, :username)',
            "params" => ['desde', 'hasta', 'username']
        ],
        "SP_REPORTE_DATA_CARGA" => [
            "query" => 'CALL SP_REPORTE_DATA_CARGA(:desde, :hasta, :id_corporation, :id_organization)',
            "params" => ['desde', 'hasta', 'id_corporation', 'id_organization']
        ],
        "SP_SEL_GUIDE_FULL" => [
            "query" => 'CALL SP_SEL_GUIDE_FULL(:search)',
            "params" => ['search']
        ],
        "SP_SEL_RIPLEY_SELLER" => [
            "query" => 'CALL SP_SEL_RIPLEY_SELLER(:name, :status)',
            "params" => ['name','status']
        ],
        "SP_INS_SELLER" => [
            "query" => 'CALL SP_INS_SELLER(:id_ripley_seller, :seller_name, :client_dni, :client_name, :client_phone1, :client_email, :client_address, :client_address_reference, :ubigeo, :department, :district, :province, :contact_name, :contact_phone, :status, :username)',
            "params" => ['id_ripley_seller', 'seller_name', 'client_dni', 'client_name', 'client_phone1', 'client_email', 'client_address', 'client_address_reference', 'ubigeo', 'department', 'district', 'province', 'contact_name', 'contact_phone', 'status', 'username']
        ],
        "SP_ACTUALIZAR_NOVEDAD_GUIA" => [
            "query" => 'CALL SP_ACTUALIZAR_NOVEDAD_GUIA(:p_guideId, :p_novedad)',
            "params" => ['p_guideId', 'p_novedad']
        ],
        "SP_INSERTAR_NOVEDAD_GUIA" => [
            "query" => 'CALL SP_INSERTAR_NOVEDAD_GUIA(:p_guideId, :p_novedad, :username)',
            "params" => ['p_guideId', 'p_novedad', 'username']
        ],
        "SP_LISTA_NOVEDAD_GUIA" => [
            "query" => 'CALL SP_LISTA_NOVEDAD_GUIA(:p_guideId)',
            "params" => ['p_guideId']
        ],
        "SP_ACTUALIZAR_GUIDE_PESO" => [
            "query" => 'CALL SP_ACTUALIZAR_GUIDE_PESO(:p_guide_id, :p_peso)',
            "params" => ['p_guide_id', 'p_peso']
        ],
        "SP_INSERTAR_NOVEDAD_GUIDE_SKU" => [
            "query" => 'CALL SP_INSERTAR_NOVEDAD_GUIDE_SKU(:p_id_guide, :p_novedad, :username)',
            "params" => ['p_id_guide', 'p_novedad', 'username']
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
        "SP_SEL_MONITORING_GUIDES" => [
            "query" => 'CALL SP_SEL_MONITORING_GUIDES(?,?,?)',
            "params" => ['id_massive_load', 'username']
        ],
        "SP_SEL_MONITORING_GUIDES_COUNT" => [
            "query" => 'CALL SP_SEL_MONITORING_GUIDES_COUNT(?,?,?)',
            "params" => ['id_massive_load', 'username']
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
            'headers_params' => ["id_driver","first_name","last_name","doc_number", "doc_type","email","phone","status","password"],
            'details_params' => ["id_vehicle", "id_provider", "vehicle_type","brand","model","plate_number","soat","status"]
        ],
        "SP_INS_DOMAIN" => [
            'query' => 'CALL SP_INS_DOMAIN(:header, :details, :username)',
            'headers_params' => [],
            'details_params' => []
        ],
        "SP_INS_USER" => [
            'query' => 'CALL SP_INS_USER(:header, :details, :username)',
            'headers_params' => ["id_user","username","first_name","last_name","doc_type","doc_number","user_email","password","status", "type", "id_role"],
            'details_params' => []
            // "id_orguser", "id_corporation", "id_organization","id_role","bydefault","status"
        ],
        "SP_UPDATE_SHIPPING_ORDER" => [
            'query' => 'CALL SP_UPDATE_SHIPPING_ORDER(:header, :details, :username)',
            'headers_params' => [],
            'details_params' => ["id_shipping_order", "id_shipping_order_detail", "id_guide", "operation"]
        ],
        "SP_INS_UNIT_LOAD" => [
            'query' => 'CALL SP_INS_UNIT_LOAD(:header, :details, :username)',
            'headers_params' => [],
            'details_params' => ['guide_number', 'seg_code', 'client_barcode', 'sku_description', 'sku_pieces', 'seller_name', 'client_info']
        ],
        "SP_INS_GUIDE_PESO" => [
            "query" => 'CALL SP_INS_GUIDE_PESO(:header, :details, :username)',
            'headers_params' => [],
            'details_params' => ["p_guide_id", "p_peso"]
        ],
    ];

    public function getFunctions()
    {
        return $this->functions;
    }
}
