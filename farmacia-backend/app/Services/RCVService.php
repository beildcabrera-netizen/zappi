<?php

namespace App\Services;

use App\Models\Venta;
use Illuminate\Support\Collection;

class RCVService
{
    public function generarDesdeVentas(array $filtros = []): Collection
    {
        $query = Venta::with(['detalles.producto.categoria', 'cliente', 'registroSin'])
            ->where('estado', 'completada');

        if (!empty($filtros['desde'])) {
            $query->whereDate('created_at', '>=', $filtros['desde']);
        }

        if (!empty($filtros['hasta'])) {
            $query->whereDate('created_at', '<=', $filtros['hasta']);
        }

        if (!empty($filtros['cliente_id'])) {
            $query->where('cliente_id', $filtros['cliente_id']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function exportarCSV(Collection $ventas): string
    {
        $csv = "NIT,RazonSocial,NumeroFactura,CUF,CodigoAutorizacion,Fecha,Total,Estado\n";

        foreach ($ventas as $venta) {
            $registro = $venta->registroSin;
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,%.2f,%s\n",
                $venta->documento_cliente ?? '0',
                str_replace(',', ' ', $venta->nombre_cliente ?? 'Consumidor Final'),
                $registro->numero_factura ?? '',
                $registro->cuf ?? '',
                $registro->codigo_autorizacion ?? '',
                $venta->created_at->format('Y-m-d H:i:s'),
                $venta->total,
                $registro->estado_sin ?? 'pendiente'
            );
        }

        return $csv;
    }
}
