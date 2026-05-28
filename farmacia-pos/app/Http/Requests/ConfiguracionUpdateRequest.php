<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfiguracionUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nombre_farmacia' => 'required|string|max:255',
            'nit_farmacia' => 'required|string|max:50',
            'razon_social_farmacia' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:500',
            'telefono' => 'nullable|string|max:50',
            'ciudad' => 'nullable|string|max:100',
            'departamento' => 'nullable|string|max:100',
            'actividad_economica' => 'nullable|string|max:255',
            'logo_url' => 'nullable|string|max:500',
            'impresora_default' => 'nullable|string|max:255',
            'iva_porcentaje' => 'required|numeric|min:0|max:100',
            'moneda_simbolo' => 'nullable|string|max:10',
            'tasa_cero_habilitada' => 'boolean',
            'tiempo_expiracion_venta_caja' => 'nullable|integer|min:1',
            'alerta_stock_dias' => 'nullable|integer|min:1',
            'llave_dosificacion' => 'nullable|string|max:500',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
