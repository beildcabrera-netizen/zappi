<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('producto');

        return [
            'codigo' => 'required|string|max:50|unique:productos,codigo,' . $id,
            'nombre' => 'required|string|max:200',
            'principio_activo' => 'nullable|string|max:200',
            'concentracion' => 'nullable|string|max:100',
            'forma_farmaceutica' => 'nullable|string|max:100',
            'presentacion' => 'nullable|string|max:100',
            'registro_sanitario' => 'nullable|string|max:50',
            'categoria_id' => 'nullable|exists:categorias,id',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'ganancia_porcentaje' => 'numeric|min:0|max:100',
            'stock_minimo' => 'integer|min:0',
            'descripcion' => 'nullable|string|max:1000',
            'activo' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'El código del producto es requerido.',
            'codigo.unique' => 'Ya existe un producto con ese código.',
            'nombre.required' => 'El nombre del producto es requerido.',
            'precio_compra.required' => 'El precio de compra es requerido.',
            'precio_venta.required' => 'El precio de venta es requerido.',
            'categoria_id.exists' => 'La categoría seleccionada no existe.',
        ];
    }
}
