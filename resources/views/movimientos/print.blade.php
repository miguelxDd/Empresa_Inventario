<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimiento de Inventario #{{ $movimiento->numero }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .document-title {
            font-size: 18px;
            font-weight: bold;
            color: #555;
        }
        .info-section {
            margin-bottom: 25px;
        }
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            width: 120px;
            color: #555;
        }
        .info-value {
            flex: 1;
        }
        .bodegas-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .bodega-box {
            width: 45%;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 4px;
        }
        .bodega-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #555;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .summary-section {
            margin-top: 30px;
            border-top: 2px solid #333;
            padding-top: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .summary-label {
            font-weight: bold;
        }
        .total-row {
            border-top: 1px solid #333;
            margin-top: 10px;
            padding-top: 10px;
            font-size: 14px;
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            text-align: center;
            color: #666;
            font-size: 11px;
        }
        .signatures {
            margin-top: 60px;
            display: flex;
            justify-content: space-around;
        }
        .signature-box {
            text-align: center;
            width: 200px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
        }
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            .no-print {
                display: none !important;
            }
        }
        .badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-entrada {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-salida {
            background-color: #f8d7da;
            color: #721c24;
        }
        .badge-transferencia {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .badge-ajuste {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-confirmado {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-pendiente {
            background-color: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <!-- Botón de impresión (no se muestra al imprimir) -->
    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">
            🖨️ Imprimir
        </button>
        <button onclick="window.close()" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-left: 10px;">
            ✖️ Cerrar
        </button>
    </div>

    <!-- Encabezado -->
    <div class="header">
        <div class="company-name">EMPRESA INVENTARIO</div>
        <div class="document-title">MOVIMIENTO DE INVENTARIO</div>
    </div>

    <!-- Información general -->
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Número:</div>
            <div class="info-value"><strong>{{ $movimiento->numero }}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-label">Tipo:</div>
            <div class="info-value">
                <span class="badge badge-{{ strtolower($movimiento->tipo) }}">
                    {{ strtoupper($movimiento->tipo) }}
                </span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Fecha:</div>
            <div class="info-value">{{ \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y H:i') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Estado:</div>
            <div class="info-value">
                <span class="badge badge-{{ strtolower($movimiento->estado) }}">
                    {{ strtoupper($movimiento->estado) }}
                </span>
            </div>
        </div>
        @if($movimiento->observaciones)
        <div class="info-row">
            <div class="info-label">Observaciones:</div>
            <div class="info-value">{{ $movimiento->observaciones }}</div>
        </div>
        @endif
    </div>

    <!-- Bodegas involucradas -->
    <div class="bodegas-section">
        @if($movimiento->bodegaOrigen)
        <div class="bodega-box">
            <div class="bodega-title">📦 BODEGA ORIGEN</div>
            <div><strong>Nombre:</strong> {{ $movimiento->bodegaOrigen->nombre }}</div>
            <div><strong>Código:</strong> {{ $movimiento->bodegaOrigen->codigo }}</div>
            @if($movimiento->bodegaOrigen->ubicacion)
            <div><strong>Ubicación:</strong> {{ $movimiento->bodegaOrigen->ubicacion }}</div>
            @endif
        </div>
        @endif

        @if($movimiento->bodegaDestino)
        <div class="bodega-box">
            <div class="bodega-title">📦 BODEGA DESTINO</div>
            <div><strong>Nombre:</strong> {{ $movimiento->bodegaDestino->nombre }}</div>
            <div><strong>Código:</strong> {{ $movimiento->bodegaDestino->codigo }}</div>
            @if($movimiento->bodegaDestino->ubicacion)
            <div><strong>Ubicación:</strong> {{ $movimiento->bodegaDestino->ubicacion }}</div>
            @endif
        </div>
        @endif
    </div>

    <!-- Detalles del movimiento -->
    <table class="table">
        <thead>
            <tr>
                <th style="width: 15%">Código</th>
                <th style="width: 40%">Producto</th>
                <th style="width: 15%" class="text-center">Cantidad</th>
                <th style="width: 15%" class="text-right">Costo Unit.</th>
                <th style="width: 15%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detalles as $detalle)
            <tr>
                <td>{{ $detalle->producto->sku ?? $detalle->producto->codigo ?? 'N/A' }}</td>
                <td>{{ $detalle->producto->nombre ?? 'Producto no encontrado' }}</td>
                <td class="text-center">{{ number_format($detalle->cantidad, 2) }}</td>
                <td class="text-right">${{ number_format($detalle->costo_unitario ?? 0, 2) }}</td>
                <td class="text-right">${{ number_format($detalle->total ?? ($detalle->cantidad * ($detalle->costo_unitario ?? 0)), 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">No hay productos registrados en este movimiento</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Resumen -->
    <div class="summary-section">
        <div class="summary-row">
            <div class="summary-label">Total de Productos:</div>
            <div>{{ $detalles->count() }}</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">Total Cantidad:</div>
            <div>{{ number_format($detalles->sum('cantidad'), 2) }}</div>
        </div>
        <div class="summary-row total-row">
            <div class="summary-label">VALOR TOTAL:</div>
            <div>${{ number_format($detalles->sum(function($d) { return $d->total ?? ($d->cantidad * ($d->costo_unitario ?? 0)); }), 2) }}</div>
        </div>
    </div>

    <!-- Información adicional -->
    @if($movimiento->asiento_id)
    <div style="margin-top: 30px; padding: 15px; background-color: #f8f9fa; border-radius: 4px;">
        <strong>Información Contable:</strong><br>
        Asiento Contable ID: #{{ $movimiento->asiento_id }}
    </div>
    @endif

    <!-- Firmas -->
    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line">Elaborado por</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Revisado por</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Autorizado por</div>
        </div>
    </div>

    <!-- Pie de página -->
    <div class="footer">
        <div>Documento generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</div>
        <div>Sistema de Inventarios - Empresa Inventario</div>
    </div>

    <script>
        // Auto-imprimir si se especifica en la URL
        if (window.location.search.includes('autoprint=1')) {
            window.onload = function() {
                window.print();
            };
        }
    </script>
</body>
</html>