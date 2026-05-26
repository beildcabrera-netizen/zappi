<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('cliente');

        return [
            'nombre' => 'required|string|max:150',
            'documento' => 'nullable|string|max:20|unique:clientes,documento,' . $id,
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:500',
            'activo' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del cliente es requerido.',
            'documento.unique' => 'Ya existe un cliente con ese documento.',
        ];
    }
}
