<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'codigo_barras' => [
                'nullable', 'string', 'max:100',
                Rule::unique('products')->ignore($this->route('producto')),
            ],
            'codigo_interno' => [
                'nullable', 'string', 'max:100',
                Rule::unique('products')->ignore($this->route('producto')),
            ],
            'nombre_comercial' => 'required|string|max:255',
            'nombre_generico' => 'nullable|string|max:255',
            'principio_activo' => 'nullable|string|max:255',
            'concentracion' => 'nullable|string|max:100',
            'forma_farmaceutica' => 'nullable|string|max:100',
            'laboratorio' => 'nullable|string|max:255',
            'registro_sanitario' => 'nullable|string|max:100',
            'presentacion_entrada' => 'nullable|string|max:100',
            'unidades_por_blister' => 'nullable|integer|min:1',
            'blisters_por_caja' => 'nullable|integer|min:1',
            'fraccionamiento_habilitado' => 'boolean',
            'precio_venta_unidad' => 'required|numeric|min:0',
            'precio_venta_blister' => 'nullable|numeric|min:0',
            'precio_venta_caja' => 'nullable|numeric|min:0',
            'costo_compra_unidad' => 'nullable|numeric|min:0',
            'stock_unidades' => 'nullable|integer|min:0',
            'stock_blisters' => 'nullable|integer|min:0',
            'stock_cajas' => 'nullable|integer|min:0',
            'stock_minimo_alertas' => 'nullable|integer|min:0',
            'estante' => 'nullable|string|max:50',
            'seccion' => 'nullable|string|max:100',
            'ubicacion_detalle' => 'nullable|string|max:255',
            'controlado' => 'boolean',
            'tipo_controlado' => 'nullable|string|max:50',
            'refrigerado' => 'boolean',
            'foto_url' => 'nullable|string|max:500',
            'activo' => 'boolean',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
