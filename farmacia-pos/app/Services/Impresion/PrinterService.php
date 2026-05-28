<?php

namespace App\Services\Impresion;

use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class PrinterService
{
    protected ?Printer $printer = null;

    public function connect(?string $tipo = null, ?string $direccion = null): Printer
    {
        $config = \App\Models\Configuracion::first();
        $tipo ??= $config?->tipo_impresora ?? 'file';
        $direccion ??= $config?->direccion_impresora ?? '/dev/usb/lp0';

        $connector = match ($tipo) {
            'network' => new NetworkPrintConnector(
                parse_url($direccion, PHP_URL_HOST) ?: $direccion,
                parse_url($direccion, PHP_URL_PORT) ?: 9100
            ),
            'windows' => new WindowsPrintConnector($direccion),
            default => new FilePrintConnector($direccion),
        };

        $this->printer = new Printer($connector);

        return $this->printer;
    }

    public function imprimirTicket(array $ticketData): void
    {
        $printer = $this->printer ?? $this->connect();

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
        $printer->text($ticketData['encabezado']['farmacia'] . "\n");
        $printer->selectPrintMode();
        $printer->text($ticketData['encabezado']['direccion'] . "\n");
        $printer->text("Tel: {$ticketData['encabezado']['telefono']}\n");
        $printer->text("NIT: {$ticketData['encabezado']['nit']}\n");
        $printer->feed(1);

        $printer->text("Factura: {$ticketData['encabezado']['numero_venta']}\n");
        $printer->text("Fecha: {$ticketData['encabezado']['fecha']}\n");
        $printer->text("Cajero: {$ticketData['encabezado']['cajero']}\n");
        $printer->feed(1);

        $printer->setEmphasis(true);
        $printer->text(str_repeat('-', 32) . "\n");
        $printer->setEmphasis(false);

        foreach ($ticketData['items'] as $item) {
            $linea = sprintf(
                "%-20s %4s x %s\n",
                mb_substr($item['producto'], 0, 20),
                $item['cantidad'],
                number_format($item['precio_unitario'], 2)
            );
            $printer->text($linea);
            $printer->text(sprintf("%30s\n", number_format($item['total'], 2)));
        }

        $printer->setEmphasis(true);
        $printer->text(str_repeat('-', 32) . "\n");
        $printer->setEmphasis(false);

        $printer->text(sprintf("%-20s %10s\n", 'Subtotal:', number_format($ticketData['totales']['subtotal'], 2)));
        $printer->text(sprintf("%-20s %10s\n", 'Descuento:', number_format($ticketData['totales']['descuento'], 2)));
        $printer->text(sprintf("%-20s %10s\n", 'Total:', number_format($ticketData['totales']['total_final'], 2)));

        if ($ticketData['totales']['recibido_efectivo'] > 0) {
            $printer->text(sprintf("%-20s %10s\n", 'Efectivo:', number_format($ticketData['totales']['recibido_efectivo'], 2)));
            $printer->text(sprintf("%-20s %10s\n", 'Cambio:', number_format($ticketData['totales']['cambio'], 2)));
        }

        $printer->feed(2);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text($ticketData['pie']['gracias'] . "\n");
        $printer->feed(2);

        $printer->cut();
        $printer->close();
    }

    public function __destruct()
    {
        if ($this->printer) {
            $this->printer->close();
        }
    }
}
