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
            'data.*.hallway' => 'hallway',
            'data.*.level' => 'level',
            'data.*.column' => 'column',
            'data.*.shrinkage' => 'shrinkage',
            'data.*.quarantine' => 'quarantine',
        ];
    }

    public function rules()
    {
        return [
            'data' => 'required',
            'id_client' => 'required|numeric',
            'id_client_store' => 'required|numeric',
            "id_load_template" => 'required|numeric',
            "entrance_guide" => 'required',
            "entry_purchase_order" => 'required',
            'data.*.product_code' => 'required|min:4|string',
            'data.*.product_alt_code1' => 'min:4|string',
            'data.*.product_alt_code2' => 'min:4|string',
            'data.*.product_description' => 'string',
            'data.*.product_serie' => 'min:4|string',
            'data.*.product_lots' => 'min:1|string',
            'data.*.product_exp_date' => 'min:4|string',
            'data.*.product_available' => 'min:2|string',
            'data.*.product_quantity' => 'required|min:1|numeric',
            'data.*.product_color' => 'min:1|string',
            'data.*.product_size' => 'min:4|string',
            'data.*.product_package_number' => 'min:1|string',
            'data.*.product_unitp_box' => 'min:1|numeric',
            'data.*.product_cmtr_pbox' => 'min:1|numeric',
            'data.*.product_cmtr_quantity' => 'min:1|numeric',
            'data.*.hallway' => 'min:0|numeric',
            'data.*.level' => 'min:0|numeric',
            'data.*.column' => 'min:0|numeric',
            'data.*.shrinkage' => 'min:0|numeric',
            'data.*.quarantine' => 'min:0|numeric',
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
