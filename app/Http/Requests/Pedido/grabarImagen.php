<?php

namespace App\Http\Requests\Pedido;

use Illuminate\Foundation\Http\FormRequest;

class grabarImagen extends FormRequest
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
            'imagen' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20048',
            'id_order' => 'required|numeric',
            'guide_number' => 'required|string',
            // 'id_shipping_order_detail' => 'required|numeric',
            'tipo_imagen' => 'required',
            'descripcion' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'imagen.required' => 'La imagen es requerida.',
            'imagen.mimes'  => 'Extension invalida.',
            'id_order.required' => 'Falta id_order',
            'id_order.numeric' => 'pedido invalido',
            'guide_number.*' => 'pedido invalido',
            // 'id_shipping_order_detail.required' => 'Falta id_shipping_order_detail',
            // 'id_shipping_order_detail.numeric' => 'pedido invalido',
            'tipo_imagen.*' => 'Tipo imagen inválido.',
            'descripcion.required' => 'Descripcion requerida.',
            'descripcion.*' => 'Descripción inválida.'
        ];
    }
}
