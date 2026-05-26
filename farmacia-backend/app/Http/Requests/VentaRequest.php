<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => 'nullable|exists:clientes,id',
            'nombre_cliente' => 'nullable|string|max:150',
            'documento_cliente' => 'nullable|string|max:20',
            'descuento' => 'numeric|min:0',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia',
            'notas' => 'nullable|string|max:500',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'metodo_pago.required' => 'El método de pago es requerido.',
            'metodo_pago.in' => 'El método de pago debe ser efectivo, tarjeta o transferencia.',
            'detalles.required' => 'Debe agregar al menos un producto.',
            'detalles.*.cantidad.min' => 'La cantidad debe ser al menos 1.',
        ];
    }
}
