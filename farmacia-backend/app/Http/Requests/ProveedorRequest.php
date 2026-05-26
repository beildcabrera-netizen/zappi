<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('proveedor');

        return [
            'nombre' => 'required|string|max:150',
            'nit' => 'nullable|string|max:20|unique:proveedores,nit,' . $id,
            'contacto' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:500',
            'activo' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del proveedor es requerido.',
            'nit.unique' => 'Ya existe un proveedor con ese NIT.',
        ];
    }
}
