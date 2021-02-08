<?php

namespace App\Http\Requests\Web\Publica;

use Illuminate\Foundation\Http\FormRequest;

class MassiveLoadInsertRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function attributes()
    {
        return [
            'data.*.seg_code' => "seg_code",
            'data.*.guide_number' => "guide_number",
            'data.*.alt_code1' => "alt_code1",
            'data.*.sku_description' => "sku_description",
            'data.*.client_dni' => "client_dni",
            'data.*.client_name' => "client_name",
            'data.*.client_address' => "client_address",
            'data.*.department' => "department",
            'data.*.district' => "district",
            'data.*.province' => "province",
            'data.*.client_email' => "client_email",
            'data.*.client_phone1' => "client_phone1",
            'data.*.client_phone2' => "client_phone2",
            'data.*.client_barcode' => "client_barcode",
            'data.*.coord_latitude' => "coord_latitude",
            'data.*.coord_longitude' => "coord_longitude",
            'data.*.ubigeo' => "ubigeo",
            'data.*.sku_code' => "sku_code",
            'data.*.client_date' => "client_date",
        ];
    }

    public function rules()
    {
        return [
            'data' => 'required',
            'data.*.seg_code' => "required|string|min:2",
            'data.*.guide_number' => "required|string|min:2",
            'data.*.alt_code1' => "string|min:2",
            'data.*.sku_description' => "required|string|min:2",
            'data.*.client_dni' => "required|numeric|min:8",
            'data.*.client_name' => "required|string|min:6",
            'data.*.client_address' => "required|string|min:10",
            'data.*.department' => "required|string|min:2",
            'data.*.district' => "required|string|min:2",
            'data.*.province' => "required|string|min:2",
            'data.*.client_email' => "email",
            'data.*.client_phone1' => "string|min:7",
            'data.*.client_phone2' => "string|min:7",
            'data.*.client_barcode' => "string|min:2",
            'data.*.coord_latitude' => "numeric",
            'data.*.coord_longitude' => "numeric",
            'data.*.ubigeo' => "string|min:6|numeric",
            'data.*.sku_code' => "string|min:2",
            'data.*.client_date' => 'date|date_format:Y-m-d'
        ];
    }

    public function messages()
    {
        return [
            'data.required' => 'El campo data es requerido',
            'data.*.required' => 'El campo :attribute es requerido',
            'data.*.min' => 'El campo :attribute debe tener al menos :min caracteres',
            'data.*.email' => 'El campo :attribute debe tener un formato de correo valido',
            'data.*.numeric' => 'El campo :attribute debe ser numerico',
            'data.*.string' => 'El campo :attribute debe ser una cadena de texto',
            'data.*.date' => 'El campo :attribute debe ser una fecha valida',
            'data.*.date_format' => 'El campo :attribute debe tener el formato Y-m-d',
        ];
    }
}
