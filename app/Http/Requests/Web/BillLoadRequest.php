<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class BillLoadRequest extends FormRequest
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
            'data.*.product_code' => 'product_code',
            'data.*.product_alt_code1' => 'product_alt_code1',
            'data.*.product_alt_code2' => 'product_alt_code2',
            'data.*.product_description' => 'product_description',
            'data.*.product_serie' => 'product_serie',
            'data.*.product_lots' => 'product_lots',
            'data.*.product_exp_date' => 'product_exp_date',
            'data.*.product_available' => 'product_available',
            'data.*.product_quantity' => 'product_quantity',
            'data.*.product_color' => 'product_color',
            'data.*.product_size' => 'product_size',
            'data.*.product_package_number' => 'product_package_number',
            'data.*.product_unitp_box' => 'product_unitp_box',
            'data.*.product_cmtr_pbox' => 'product_cmtr_pbox',
            'data.*.product_cmtr_quantity' => 'product_cmtr_quantity',
        ];
    }

    public function rules()
    {
        return [
            'data' => 'required',
            'id_client' => 'required|numeric',
            'id_client_store' => 'required|numeric',
            'data.*.product_code' => 'required|min:4|string',
            'data.*.product_alt_code1' => 'required|min:2',
            'data.*.product_alt_code2' => 'required|min:2',
            'data.*.product_description' => 'required|min:2',
            'data.*.product_serie' => 'required|min:2',
            'data.*.product_lots' => 'required|min:2',
            'data.*.product_exp_date' => 'required|min:2',
            'data.*.product_available' => 'required|min:2',
            'data.*.product_quantity' => 'required|min:2',
            'data.*.product_color' => 'required|min:2',
            'data.*.product_size' => 'required|min:2',
            'data.*.product_package_number' => 'required|min:2',
            'data.*.product_unitp_box' => 'required|min:2',
            'data.*.product_cmtr_pbox' => 'required|min:2',
            'data.*.product_cmtr_quantity' => 'required|min:2',
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
