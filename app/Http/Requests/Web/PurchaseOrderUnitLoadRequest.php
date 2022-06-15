<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseOrderUnitLoadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function attributes()
    {
        return [
            'id_client' => 'id_client',
            'id_client_store' => 'id_client_store',
            'exit_date' => 'exit_date',
            'client_phone' => 'client_phone',
            'client_name' => 'client_name',
            'document_type' => 'document_type',
            'document_number' => 'document_number',
            'data.*.product_code' => 'product_code',
            'data.*.product_description' => 'product_description',
            'data.*.product_quantity' => 'product_quantity',
            'data.*.id_product' => 'id_product',
            'data.*.id_inventory' => 'id_inventory',
            'data.*.batch' => 'batch',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'data' => 'required',
            'id_client' => 'required|numeric',
            'id_client_store' => 'required|numeric',
            "document_type" => 'string',
            "document_number" => 'string',
            'exit_date' => 'required|date',
            'client_phone' => 'string',
            'client_name' => 'required|min:4|string',
            'data.*.product_code' => 'required|min:4|string',
            'data.*.product_description' => 'string|min:6',
            'data.*.product_quantity' => 'required|min:1|numeric',
            'data.*.id_product' => 'required|min:1|numeric',
            'data.*.id_inventory' => 'required|min:1|numeric',
            'data.*.batch' => 'required|string',
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
