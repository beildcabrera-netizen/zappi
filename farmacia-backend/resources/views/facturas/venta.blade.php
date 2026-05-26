<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 10mm; }
        body { font-family: 'Arial', sans-serif; font-size: 10pt; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 14pt; margin: 0; }
        .header p { margin: 2px 0; }
        .info { margin-bottom: 15px; }
        .info table { width: 100%; }
        .info td { padding: 2px 5px; }
        table.items { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table.items th { background: #f0f0f0; border: 1px solid #ddd; padding: 6px; text-align: left; }
        table.items td { border: 1px solid #ddd; padding: 5px; }
        .text-right { text-align: right; }
        .totals { width: 300px; margin-left: auto; }
        .totals td { padding: 3px 5px; }
        .footer { text-align: center; font-size: 8pt; margin-top: 20px; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $config->razon_social ?? 'Farmacia' }}</h1>
        <p>{{ $config->direccion ?? '' }}</p>
        <p>Tel: {{ $config->telefono ?? '' }} | NIT: {{ $config->nit ?? '' }}</p>
        <p>Sucursal: {{ $config->codigo_sucursal ?? '0' }}</p>
        <hr>
        <h2>FACTURA</h2>
        <p>No: {{ $venta->codigo_factura }}</p>
        <p>Fecha: {{ $venta->created_at->format('d/m/Y H:i') }}</p>
    </div>

    <div class="info">
        <table>
            <tr><td><strong>Cliente:</strong></td><td>{{ $venta->nombre_cliente ?? 'Consumidor Final' }}</td></tr>
            <tr><td><strong>NIT/CI:</strong></td><td>{{ $venta->documento_cliente ?? '0' }}</td></tr>
            <tr><td><strong>Método de pago:</strong></td><td>{{ ucfirst($venta->metodo_pago) }}</td></tr>
        </table>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Código</th>
                <th>Producto</th>
                <th class="text-right">Cant.</th>
                <th class="text-right">P. Unit.</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $detalle)
            <tr>
                <td>{{ $detalle->producto->codigo }}</td>
                <td>{{ $detalle->producto->nombre }}</td>
                <td class="text-right">{{ $detalle->cantidad }}</td>
                <td class="text-right">{{ number_format($detalle->precio_unitario, 2) }}</td>
                <td class="text-right">{{ number_format($detalle->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal Bs:</td><td class="text-right">{{ number_format($venta->subtotal, 2) }}</td></tr>
        <tr><td>Descuento Bs:</td><td class="text-right">{{ number_format($venta->descuento, 2) }}</td></tr>
        <tr><td>ICE/IEHD/IPJ Bs:</td><td class="text-right">0.00</td></tr>
        <tr><td>IVA 13% Bs:</td><td class="text-right">{{ number_format($venta->iva, 2) }}</td></tr>
        <tr style="font-weight:bold; font-size:12pt;">
            <td>TOTAL Bs:</td><td class="text-right">{{ number_format($venta->total, 2) }}</td>
        </tr>
    </table>

    @if($venta->registroSin)
    <div class="info">
        <table>
            <tr><td><strong>CUF:</strong></td><td>{{ $venta->registroSin->cuf }}</td></tr>
            <tr><td><strong>No. Factura:</strong></td><td>{{ $venta->registroSin->numero_factura }}</td></tr>
            <tr><td><strong>Código Autorización:</strong></td><td>{{ $venta->registroSin->codigo_autorizacion }}</td></tr>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>{{ $config->leyenda_1 ?? 'Esta factura contribuye al desarrollo del país.' }}</p>
        <p>{{ $config->leyenda_2 ?? 'El consumo de medicamentos debe ser bajo prescripción médica.' }}</p>
        <p>Vendido por: {{ $venta->user->name }}</p>
    </div>
</body>
</html>
