<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'numero_factura' => 'nullable|string|max:50',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'nit_proveedor' => 'nullable|string|max:20',
            'nombre_proveedor' => 'nullable|string|max:150',
            'fecha_compra' => 'required|date',
            'notas' => 'nullable|string|max:1000',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.numero_lote' => 'required|string|max:100',
            'detalles.*.fecha_vencimiento' => 'required|date|after:today',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_compra.required' => 'La fecha de compra es requerida.',
            'detalles.required' => 'Debe agregar al menos un producto.',
            'detalles.*.producto_id.required' => 'El producto es requerido.',
            'detalles.*.cantidad.min' => 'La cantidad debe ser al menos 1.',
            'detalles.*.fecha_vencimiento.after' => 'La fecha de vencimiento debe ser posterior a hoy.',
        ];
    }
}
