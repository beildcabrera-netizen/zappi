<?php

namespace App\Exports;

use App\Models\ManualInvoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RegistroVentasExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldQueue
{
    use Exportable;

    public function __construct(
        protected ?string $fechaDesde = null,
        protected ?string $fechaHasta = null,
    ) {}

    public function query()
    {
        $query = ManualInvoice::with(['talonario', 'vendedor'])
            ->orderBy('fecha_emision')
            ->orderBy('numero_factura');

        if ($this->fechaDesde) {
            $query->whereDate('fecha_emision', '>=', $this->fechaDesde);
        }

        if ($this->fechaHasta) {
            $query->whereDate('fecha_emision', '<=', $this->fechaHasta);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'N° Factura',
            'N° Autorización',
            'Código de Control',
            'Fecha Emisión',
            'NIT/CI Cliente',
            'Complemento',
            'Razón Social',
            'Importe Total',
            'Subtotal',
            'Descuentos',
            'Base Débito Fiscal',
            'Débito Fiscal',
            'Estado',
            'Vendedor',
        ];
    }

    public function map($factura): array
    {
        return [
            $factura->numero_completo,
            $factura->codigo_autorizacion,
            $factura->codigo_control,
            $factura->fecha_emision->format('d/m/Y'),
            $factura->nit_cliente,
            $factura->complemento ?? '',
            $factura->razon_social_cliente ?? '',
            number_format($factura->importe_total, 2, '.', ''),
            number_format($factura->subtotal, 2, '.', ''),
            number_format($factura->descuentos, 2, '.', ''),
            number_format($factura->base_debito_fiscal, 2, '.', ''),
            number_format($factura->debito_fiscal, 2, '.', ''),
            $factura->estado === 'V' ? 'Válida' : $factura->estado,
            $factura->vendedor?->nombre ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
