<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.presentacion_vendida' => 'required|string|in:unidad,blister,caja,frasco,tubo',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.subtotal' => 'required|numeric|min:0',
            'items.*.descuento_item' => 'nullable|numeric|min:0',
            'items.*.total_item' => 'required|numeric|min:0',
            'items.*.receta_numero' => 'nullable|string|max:50',
            'items.*.receta_medico' => 'nullable|string|max:255',
            'items.*.receta_foto_url' => 'nullable|string|max:500',

            'cliente_tipo' => 'nullable|string|in:consumidor_final,con_nit',
            'cliente_nit' => 'nullable|string|max:20',
            'cliente_complemento' => 'nullable|string|max:10',
            'cliente_razon_social' => 'nullable|string|max:255',

            'subtotal' => 'required|numeric|min:0',
            'descuento_total' => 'nullable|numeric|min:0',
            'total_venta' => 'required|numeric|min:0',
            'total_final' => 'required|numeric|min:0',

            'metodo_pago' => 'required|string|in:efectivo,qr_bancario,tarjeta_debito,tarjeta_credito,transferencia',
            'tipo_documento' => 'nullable|string|in:nota_venta,factura_manual',
            'generar_factura' => 'boolean',

            'recibido_efectivo' => 'nullable|numeric|min:0',
            'cambio' => 'nullable|numeric|min:0',
            'codigo_transaccion_qr' => 'nullable|string|max:255',
            'referencia_transferencia' => 'nullable|string|max:255',

            'caja_id' => 'nullable|exists:cash_registers,id',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
