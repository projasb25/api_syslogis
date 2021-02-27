<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseOrderLoadRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function attributes()
    {
        return [
            'id_client' => 'id_client',
            'id_client_store' => 'id_client_store',
            'data.*.product_code' => 'product_code',
            'data.*.product_description' => 'product_description',
            'data.*.product_quantity' => 'product_quantity',
        ];
    }

    public function rules()
    {
        return [
            'data' => 'required',
            'id_client' => 'required|numeric',
            'id_client_store' => 'required|numeric',
            "id_load_template" => 'required|numeric',
            'id_buyer' => 'required|numeric',
            "id_provider" => 'required|numeric',
            "id_vehicle" => 'required|numeric',
            "document_type" => 'string',
            "document_number" => 'string',
            'purchase_order_number' => 'required',
            'data.*.product_code' => 'required|min:4|string',
            'data.*.product_description' => 'string|min:6',
            'data.*.product_quantity' => 'required|min:1|numeric',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'El campo :attribute es requerido',
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
