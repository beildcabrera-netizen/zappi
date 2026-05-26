<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('categorium') ?? $this->route('categoria');

        return [
            'nombre' => 'required|string|max:100|unique:categorias,nombre,' . $id,
            'descripcion' => 'nullable|string|max:500',
            'activo' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la categoría es requerido.',
            'nombre.unique' => 'Ya existe una categoría con ese nombre.',
        ];
    }
}
