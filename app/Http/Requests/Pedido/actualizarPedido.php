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
            'idpedido_detalle' => 'required|numeric',
            'estado' => 'required|string',
            'observacion' => 'string|nullable'
        ];
    }

    public function messages()
    {
        return [
            'idpedido_detalle.required' => 'Falta idpedido_detalle',
            'idpedido_detalle.numeric' => 'pedido invalido',
            'estado.*' => 'estado inválido.',
            'observacion.*' => 'Obseracion inválida.'
        ];
    }
}
