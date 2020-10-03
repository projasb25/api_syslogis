<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class ChangeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id_organization' => 'required|numeric'
        ];
    }

    public function messages()
    {
        return [
            'id_organization.required' => 'falta el campo id_organization',
            'id_organization.numeric' => 'id_organization inválido'
        ];
    }
}
