<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SINConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nit' => 'required|string|max:20',
            'razon_social' => 'required|string|max:200',
            'nombre_comercial' => 'nullable|string|max:200',
            'codigo_sucursal' => 'nullable|string|max:4',
            'direccion' => 'nullable|string|max:300',
            'telefono' => 'nullable|string|max:20',
            'ciudad' => 'nullable|string|max:100',
            'tipo_documento_sector' => 'nullable|string|max:2',
            'leyenda_1' => 'nullable|string|max:300',
            'leyenda_2' => 'nullable|string|max:300',
        ];
    }

    public function messages(): array
    {
        return [
            'nit.required' => 'El NIT es requerido.',
            'razon_social.required' => 'La razón social es requerida.',
        ];
    }
}
