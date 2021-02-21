<?php

namespace App\Models\Functions;

class Diccionario
{
    private $diccionario = [
        "bill_load" => [
            "id_bill_load" => [
                "column" => "bl.id_bill_load",
                "type" => "string"
            ],
            "id_client" => [
                "column" => "bl.id_client",
                "type" => "string"
            ],
            "id_client_store" => [
                "column" => "bl.id_client_store",
                "type" => "string"
            ],
            "id_bill_load_template" => [
                "column" => "bl.id_bill_load_template",
                "type" => "string"
            ],
            "number_records" => [
                "column" => "bl.number_records",
                "type" => "string"
            ],
            "status" => [
                "column" => "bl.status",
                "type" => "string"
            ],
            "created_by" => [
                "column" => "bl.created_by",
                "type" => "string"
            ],
            "date_created" => [
                "column" => "bl.date_created",
                "type" => "string"
            ],
            "store_name" => [
                "column" => "cs.store_name",
                "type" => "string"
            ],
            "company_name" => [
                "column" => "cl.company_name",
                "type" => "string"
            ],
            "template_name" => [
                "column" => "lt.name",
                "type" => "string"
            ],
            "fechafilter" => [
                "column" => "DATE_FORMAT(bl.date_created, '%Y-%m-%d')",
                "type" => "string"
            ]
        ],
        "purchase_order" => [
            "id_purchase_order" => [
                "column" => "po.id_purchase_order",
                "type" => "string"
            ],
            "id_client" => [
                "column" => "po.id_client",
                "type" => "string"
            ],
            "id_client_store" => [
                "column" => "po.id_client_store",
                "type" => "string"
            ],
            "id_load_template" => [
                "column" => "po.id_load_template",
                "type" => "string"
            ],
            "number_records" => [
                "column" => "po.number_records",
                "type" => "string"
            ],
            "status" => [
                "column" => "po.status",
                "type" => "string"
            ],
            "created_by" => [
                "column" => "po.created_by",
                "type" => "string"
            ],
            "date_created" => [
                "column" => "po.date_created",
                "type" => "string"
            ],
            "store_name" => [
                "column" => "cs.store_name",
                "type" => "string"
            ],
            "company_name" => [
                "column" => "cl.company_name",
                "type" => "string"
            ],
            "template_name" => [
                "column" => "lt.name",
                "type" => "string"
            ],
            "fechafilter" => [
                "column" => "DATE_FORMAT(po.date_created, '%Y-%m-%d')",
                "type" => "string"
            ],
            "purchase_order_number" => [
                "column" => "po.purchase_order_number",
                "type" => "string"
            ],
            "buyer_name" => [
                "column" => "byr.company_name",
                "type" => "string"
            ]
        ],
        "reporte_inventario" => [
            "id_client" => [
                "column" => "cli.id_client",
                "type" => "string"
            ],
            "id_client_store" => [
                "column" => "cs.id_client_store",
                "type" => "string"
            ],
            "client_name" => [
                "column" => "cli.company_name",
                "type" => "string"
            ],
            "store_name" => [
                "column" => "cs.store_name",
                "type" => "string"
            ],
            "hallway" => [
                "column" => "inv.hallway",
                "type" => "string"
            ],
            "column" => [
                "column" => "inv.column",
                "type" => "string"
            ],
            "level" => [
                "column" => "inv.level",
                "type" => "string"
            ],
            "quantity" => [
                "column" => "inv.quantity",
                "type" => "string"
            ],
            "quarantine" => [
                "column" => "inv.quarantine",
                "type" => "string"
            ],
            "shrinkage" => [
                "column" => "inv.shrinkage",
                "type" => "string"
            ],
            "available" => [
                "column" => "inv.available",
                "type" => "string"
            ],
            "product_code" => [
                "column" => "p.product_code",
                "type" => "string"
            ],
            "product_description" => [
                "column" => "p.product_description",
                "type" => "string"
            ],
            "product_serie" => [
                "column" => "p.product_serie",
                "type" => "string"
            ],
            "product_lots" => [
                "column" => "p.product_lots",
                "type" => "string"
            ],
            "product_exp_date" => [
                "column" => "p.product_exp_date",
                "type" => "string"
            ],
            "product_available" => [
                "column" => "p.product_available",
                "type" => "string"
            ],
            "product_color" => [
                "column" => "p.product_color",
                "type" => "string"
            ],
            "product_size" => [
                "column" => "p.product_size",
                "type" => "string"
            ],
            "product_package_number" => [
                "column" => "p.product_package_number",
                "type" => "string"
            ],
            "product_unitp_box" => [
                "column" => "p.product_unitp_box",
                "type" => "string"
            ],
            "product_cmtr_pbox" => [
                "column" => "p.product_cmtr_pbox",
                "type" => "string"
            ],
            "product_cmtr_quantity" => [
                "column" => "p.product_cmtr_quantity",
                "type" => "string"
            ],
            "fechafilter" => [
                "column" => "DATE_FORMAT(p.date_created, '%Y-%m-%d')",
                "type" => "string"
            ],
        ],
        
        "reporte_inventario_producto" => [
            "id_client" => [
                "column" => "cli.id_client",
                "type" => "string"
            ],
            "id_client_store" => [
                "column" => "cli.id_client_store",
                "type" => "string"
            ],
            "client_name" => [
                "column" => "cli.client_name",
                "type" => "string"
            ],
            "store_name" => [
                "column" => "cs.store_name",
                "type" => "string"
            ],
            "product_code" => [
                "column" => "p.product_code",
                "type" => "string"
            ],
            "quantity" => [
                "column" => "p.product_quantity",
                "type" => "string"
            ],
            "quarantine" => [
                "column" => "p.product_quarantine_total",
                "type" => "string"
            ],
            "shrinkage" => [
                "column" => "p.product_shrinkage_total",
                "type" => "string"
            ],
            "available" => [
                "column" => "p.product_available_total",
                "type" => "string"
            ],
            "product_description" => [
                "column" => "p.product_description",
                "type" => "string"
            ],
            "product_serie" => [
                "column" => "p.product_serie",
                "type" => "string"
            ],
            "product_lots" => [
                "column" => "p.product_lots",
                "type" => "string"
            ],
            "product_exp_date" => [
                "column" => "p.product_exp_date",
                "type" => "string"
            ],
            "product_available" => [
                "column" => "p.product_available",
                "type" => "string"
            ],
            "product_color" => [
                "column" => "p.product_color",
                "type" => "string"
            ],
            "product_size" => [
                "column" => "p.product_size",
                "type" => "string"
            ],
            "product_package_number" => [
                "column" => "p.product_package_number",
                "type" => "string"
            ],
            "product_unitp_box" => [
                "column" => "p.product_unitp_box",
                "type" => "string"
            ],
            "product_cmtr_pbox" => [
                "column" => "p.product_cmtr_pbox",
                "type" => "string"
            ],
            "product_cmtr_quantity" => [
                "column" => "p.product_cmtr_quantity",
                "type" => "string"
            ],
            "fechafilter" => [
                "column" => "DATE_FORMAT(p.date_created, '%Y-%m-%d')",
                "type" => "string"
            ],
        ],

//asdfasdfasdfasdasdf
        "guide" => [
            "id_guide" => [
                "column" => "gd.id_guide",
                "type" => "string"
            ],
            "id_corporation" => [
                "column" => "gd.id_corporation",
                "type" => "string"
            ],
            "id_organization" => [
                "column" => "gd.id_organization",
                "type" => "string"
            ],
            "id_massive_load" => [
                "column" => "gd.id_massive_load",
                "type" => "string"
            ],
            "guide_number" => [
                "column" => "gd.guide_number",
                "type" => "string"
            ],
            "order_number" => [
                "column" => "gd.order_number",
                "type" => "string"
            ],
            "id_address" => [
                "column" => "gd.id_address",
                "type" => "string"
            ],
            "seg_code" => [
                "column" => "gd.seg_code",
                "type" => "string"
            ],
            "alt_code1" => [
                "column" => "gd.alt_code1",
                "type" => "string"
            ],
            "alt_code2" => [
                "column" => "gd.alt_code2",
                "type" => "string"
            ],
            "client_date" => [
                "column" => "gd.client_date",
                "type" => "string"
            ],
            "client_barcode" => [
                "column" => "gd.client_barcode",
                "type" => "string"
            ],
            "client_date2" => [
                "column" => "gd.client_date2",
                "type" => "string"
            ],
            "total_weight" => [
                "column" => "gd.total_weight",
                "type" => "string"
            ],
            "total_pieces" => [
                "column" => "gd.total_pieces",
                "type" => "string"
            ],
            "client_dni" => [
                "column" => "gd.client_dni",
                "type" => "string"
            ],
            "client_name" => [
                "column" => "gd.client_name",
                "type" => "string"
            ],
            "client_phone1" => [
                "column" => "gd.client_phone1",
                "type" => "string"
            ],
            "client_phone2" => [
                "column" => "gd.client_phone2",
                "type" => "string"
            ],
            "client_phone3" => [
                "column" => "gd.client_phone3",
                "type" => "string"
            ],
            "client_email" => [
                "column" => "gd.client_email",
                "type" => "string"
            ],
            "status" => [
                "column" => "gd.status",
                "type" => "string"
            ],
            "type" => [
                "column" => "gd.type",
                "type" => "string"
            ],
            "attempt" => [
                "column" => "gd.attempt",
                "type" => "string"
            ],
            "created_by" => [
                "column" => "gd.created_by",
                "type" => "string"
            ],
            "modified_by" => [
                "column" => "gd.modified_by",
                "type" => "string"
            ],
            "reportado_integracion" => [
                "column" => "gd.reportado_integracion",
                "type" => "string"
            ],
            "address" => [
                "column" => "adr.address",
                "type" => "string"
            ],
            "org_name" => [
                "column" => "org.name",
                "type" => "string"
            ],
            "province" => [
                "column" => "adr.province",
                "type" => "string"
            ],
            "district" => [
                "column" => "adr.district",
                "type" => "string"
            ],
            "department" => [
                "column" => "adr.department",
                "type" => "string"
            ],
            "fechafilter" => [
                "column" => "DATE_FORMAT(gd.date_created, '%Y-%m-%d')",
                "type" => "string"
            ]
        ],
        "img_monitor" => [
            "id_shipping_order" => [
                "column" => "sod.id_shipping_order",
                "type" => "string"
            ],
            "id_guide" => [
                "column" => "sod.id_guide",
                "type" => "string"
            ],
            "status" => [
                "column" => "sod.status",
                "type" => "string"
            ],
            "guide_barcode" => [
                "column" => "sod.guide_barcode",
                "type" => "string"
            ],
            "guide_number" => [
                "column" => "sod.guide_number",
                "type" => "string"
            ],
            "id_vehicle" => [
                "column" => "so.id_vehicle",
                "type" => "string"
            ],
            "fechafilter" => [
                "column" => "DATE_FORMAT(so.date_created, '%Y-%m-%d')",
                "type" => "string"
            ],
            "plate_number" => [
                "column" => "vh.plate_number",
                "type" => "string"
            ],
            "name" => [
                "column" => "pv.name",
                "type" => "string"
            ],
            "id_provider" => [
                "column" => "pv.id_provider",
                "type" => "string"
            ],
            "images_count" => [
                "column" =>  "(select count(id_guide_images) from guide_images where id_guide = sod.id_guide and id_shipping_order = sod.id_shipping_order)"
            ],
            "attempt" => [
                "column" => "gt.attempt"
            ]
        ]
    ];

    public function getDiccionario()
    {
        return $this->diccionario;
    }
}
