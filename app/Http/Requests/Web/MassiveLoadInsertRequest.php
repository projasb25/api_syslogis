<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class MassiveLoadInsertRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'data' => 'required',
            'id_corporation' => 'required|numeric',
            'id_organization' => 'required|numeric'
        ];
    }

    public function messages()
    {
        return [
            'data.required' => 'falta el campo data',
            'id_corporation.required' => 'falta el campo id_corporation',
            'id_corporation.numeric' => 'id_corporation inválido',
            'id_organization.required' => 'falta el campo id_organization',
            'id_organization.numeric' => 'id_organization inválido'
        ];
    }
}
