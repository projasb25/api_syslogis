<?php

namespace App\Models\Functions;

class Diccionario
{
    private $diccionario = [
        "client_order" => [
            // "client_phone3" => [
            //     "column" => "gd.client_phone3",
            //     "type" => "string"
            // ],
            "fechafilter" => [
                "column" => "DATE_FORMAT(o.date_created, '%Y-%m-%d')",
                "type" => "string"
            ]
        ],
        "SP_SEL_ORDER_USER" => [
            "id_order" => [
                "column" => "o.id_order",
                "type" => "string"
            ],
            "guide_number" => [
                "column" => "o.guide_number",
                "type" => "string"
            ],
            "pickup_contact_name" => [
                "column" => "o.pickup_contact_name",
                "type" => "string"
            ],
            "pickup_address" => [
                "column" => "o.pickup_address",
                "type" => "string"
            ],
            "delivery_address" => [
                "column" => "o.delivery_address",
                "type" => "string"
            ],
            "delivery_contact_name" => [
                "column" => "o.delivery_contact_name",
                "type" => "string"
            ],
            "status" => [
                "column" => "o.status",
                "type" => "string"
            ],
            "pickup_phone" => [
                "column" => "o.pickup_phone",
                "type" => "string"
            ],
            "pickup_reference" => [
                "column" => "o.pickup_reference",
                "type" => "string"
            ],
            "delivery_phone" => [
                "column" => "o.delivery_phone",
                "type" => "string"
            ],
            "delivery_reference" => [
                "column" => "o.delivery_reference",
                "type" => "string"
            ],
            "driver_name" => [
                "column" => "CONCAT(usr.first_name,' ',usr.last_name)",
                "type" => "string"
            ],
            "plate_number" => [
                "column" => "d2.plate_number",
                "type" => "string"
            ],
            "fechafilter" => [
                "column" => "DATE_FORMAT(o.date_created, '%Y-%m-%d')",
                "type" => "string"
            ]
        ],
        "SP_SEL_ORDER_USER" => [
            'id_massive_load' => [
                'column' => "ml.id_massive_load",
                'type' => "string"
            ],
            'number_records' => [
                'column' => "ml.number_records",
                'type' => "string"
            ],
            'status' => [
                'column' => "ml.status",
                'type' => "string"
            ],
            'created_by' => [
                'column' => "ml.created_by",
                'type' => "string"
            ],
            "fechafilter" => [
                "column" => "DATE_FORMAT(ml.date_created, '%Y-%m-%d')",
                "type" => "string"
            ]
        ],
    ];

    public function getDiccionario()
    {
        return $this->diccionario;
    }
}
