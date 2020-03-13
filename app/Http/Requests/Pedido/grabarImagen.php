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
        ];
    }

    public function messages()
    {
        return [
            'imagen.required' => 'La imagen es requerida.',
            'imagen.mimes'  => 'Extension invalida.',
        ];
    }
}
