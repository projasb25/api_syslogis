<?php

namespace App\Models\Functions;

class Diccionario
{
    private $diccionario = [
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
        ]
    ];

    public function getDiccionario()
    {
        return $this->diccionario;
    }
}