<?php

namespace App\Http\Requests\Pedido;

use Illuminate\Foundation\Http\FormRequest;

class obtenerImagen extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'idpedido_detalle' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'idpedido_detalle.required' => 'Falta idpedido_detalle',
            'idpedido_detalle.numeric' => 'pedido invalido',
        ];
    }
}
