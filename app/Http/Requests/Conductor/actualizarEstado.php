<?php

namespace App\Http\Requests\Conductor;

use Illuminate\Foundation\Http\FormRequest;

class actualizarEstado extends FormRequest
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
            'estado' => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            'estado.required' => 'Estado requerido.',
            'estado.boolean'  => 'Request inválida.',
        ];
    }
}
