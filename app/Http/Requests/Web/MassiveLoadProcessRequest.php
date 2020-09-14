<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class MassiveLoadProcessRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'data' => 'required',
            'id_massive_load' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'data.required' => 'falta el campo data',
            'id_massive_load.required' => 'falta el campo id_massive_load',
            'id_massive_load.numeric' => 'id_massive_load inválido',
        ];
    }
}
