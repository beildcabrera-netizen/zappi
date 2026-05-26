<?php

namespace App\Services;

use App\Helpers\SINHelper;
use App\Models\ConfiguracionSin;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;

class FacturaService
{
    public function generarFactura(Venta $venta): string
    {
        $venta->load('detalles.producto', 'cliente', 'user');

        $config = ConfiguracionSin::where('activo', true)->first();

        $pdf = Pdf::loadView('facturas.venta', [
            'venta' => $venta,
            'config' => $config,
        ]);

        $pdf->setPaper([0, 0, 226.77, 841.89], 'portrait');

        return $pdf->output();
    }

    public function generarTicket(Venta $venta): string
    {

        $venta->load('detalles.producto', 'cliente', 'user');

        $config = ConfiguracionSin::where('activo', true)->first();

        $pdf = Pdf::loadView('facturas.ticket', [
            'venta' => $venta,
            'config' => $config,
        ]);

        $pdf->setPaper([0, 0, 226.77, 500], 'portrait');

        return $pdf->output();
    }
}
