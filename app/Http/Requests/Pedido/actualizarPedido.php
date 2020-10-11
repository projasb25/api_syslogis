<?php

namespace App\Http\Requests\Pedido;

use Illuminate\Foundation\Http\FormRequest;

class actualizarPedido extends FormRequest
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

    public function rules()
    {
        return [
            'id_shipping_order_detail' => 'required|numeric',
            'estado' => 'required|string',
            'observacion' => 'string|nullable',
            'latitud' => 'string|numeric',
            'longitud' => 'string|numeric'
        ];
    }

    public function messages()
    {
        return [
            'id_shipping_order_detail.required' => 'Falta id_shipping_order_detail',
            'id_shipping_order_detail.numeric' => 'pedido invalido',
            'estado.*' => 'estado inválido.',
            'observacion.*' => 'Obseracion inválida.',
            'latitud.*' => 'Latitud Inválida',
            'longitud.*' => 'Longitud Inválida.'
        ];
    }
}
