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
    ];

    public function getDiccionario()
    {
        return $this->diccionario;
    }
}
