<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 5mm; }
        body { font-family: 'Courier New', monospace; font-size: 9pt; line-height: 1.2; }
        .header { text-align: center; margin-bottom: 10px; }
        .header h1 { font-size: 11pt; margin: 0; }
        .header p { margin: 2px 0; font-size: 8pt; }
        .line { border-top: 1px dashed #000; margin: 5px 0; }
        .item { margin: 3px 0; }
        .item .name { font-size: 8pt; }
        .item .details { font-size: 8pt; }
        .totals { margin-top: 5px; }
        .totals .row { display: flex; justify-content: space-between; }
        .footer { text-align: center; font-size: 7pt; margin-top: 10px; }
        .leyenda { font-size: 6pt; text-align: center; margin-top: 5px; }
        table { width: 100%; font-size: 8pt; border-collapse: collapse; }
        th, td { padding: 2px 0; text-align: left; }
        th { border-bottom: 1px solid #000; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $config->razon_social ?? 'Farmacia' }}</h1>
        <p>{{ $config->direccion ?? '' }}</p>
        <p>Tel: {{ $config->telefono ?? '' }}</p>
        <p>NIT: {{ $config->nit ?? '' }}</p>
        <p>Sucursal: {{ $config->codigo_sucursal ?? '0' }}</p>
        <div class="line"></div>
        <p>FACTURA No: {{ $venta->codigo_factura }}</p>
        <p>Fecha: {{ $venta->created_at->format('d/m/Y H:i') }}</p>
        <p>Cliente: {{ $venta->nombre_cliente ?? 'Consumidor Final' }}</p>
        <p>NIT/CI: {{ $venta->documento_cliente ?? '0' }}</p>
    </div>

    <div class="line"></div>

    <table>
        <thead>
            <tr>
                <th>Prod</th>
                <th class="text-right">P.U.</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $detalle)
            <tr>
                <td>{{ $detalle->cantidad }}x {{ Str::limit($detalle->producto->nombre, 20) }}</td>
                <td class="text-right">{{ number_format($detalle->precio_unitario, 2) }}</td>
                <td class="text-right">{{ number_format($detalle->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    <div class="totals">
        <div class="row"><span>Subtotal Bs:</span><span>{{ number_format($venta->subtotal, 2) }}</span></div>
        <div class="row"><span>Descuento Bs:</span><span>{{ number_format($venta->descuento, 2) }}</span></div>
        <div class="row"><span>ICE/IEHD/IPJ:</span><span>0.00</span></div>
        <div class="row"><span>IVA 13% Bs:</span><span>{{ number_format($venta->iva, 2) }}</span></div>
        <div class="row" style="font-weight:bold; font-size:10pt; margin-top:3px;">
            <span>TOTAL Bs:</span><span>{{ number_format($venta->total, 2) }}</span>
        </div>
    </div>

    <div class="line"></div>

    <p>Método de pago: {{ ucfirst($venta->metodo_pago) }}</p>

    @if($venta->registroSin)
    <p>CUF: {{ $venta->registroSin->cuf }}</p>
    <p>No. Factura: {{ $venta->registroSin->numero_factura }}</p>
    <p>Código Autorización: {{ $venta->registroSin->codigo_autorizacion }}</p>
    @endif

    <div class="leyenda">
        <p>{{ $config->leyenda_1 ?? 'Esta factura contribuye al desarrollo del país.' }}</p>
        <p>{{ $config->leyenda_2 ?? 'El consumo de medicamentos debe ser bajo prescripción médica.' }}</p>
    </div>

    <div class="footer">
        <p>¡Gracias por su compra!</p>
        <p>Vendido por: {{ $venta->user->name }}</p>
    </div>
</body>
</html>
