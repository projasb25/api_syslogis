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
            'data' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'data.required' => 'falta el campo data'
        ];
    }
}
