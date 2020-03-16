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
            'idpedido_detalle' => 'required|numeric',
            'tipo_imagen' => 'required',
            'descripcion' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'imagen.required' => 'La imagen es requerida.',
            'imagen.mimes'  => 'Extension invalida.',
            'idpedido_detalle.required' => 'Falta idpedido_detalle',
            'idpedido_detalle.numeric' => 'pedido invalido',
            'tipo_imagen.*' => 'Tipo imagen inválido.',
            'descripcion.required' => 'Descripcion requerida.',
            'descripcion.*' => 'Descripción inválida.'
        ];
    }
}
