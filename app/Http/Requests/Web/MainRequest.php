<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class MainRequest extends FormRequest
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
            'method' => 'required|string',
            'data' => 'present|nullable'
        ];
    }

    public function messages()
    {
        return [
            'method.required' => 'falta el campo method',
            'method.string'  => 'Metodo inválido',
            'data.present' => 'falta el campo data'
        ];
    }
}
