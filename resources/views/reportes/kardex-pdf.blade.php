<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kardex - {{ $data['producto']['sku'] ?? 'N/A' }}</title>
    <style>
        @page {
            size: A4;
            margin: 1cm;
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 11px;
            line-height: 1.3;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 15px;
        }
        
        .header h1 {
            color: #667eea;
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }
        
        .header h2 {
            color: #333;
            margin: 8px 0 0 0;
            font-size: 16px;
        }
        
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }
        
        .info-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 10px;
        }
        
        .info-row {
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
            font-size: 10px;
        }
        
        .info-value {
            font-size: 10px;
        }
        
        .movements-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 9px;
        }
        
        .movements-table th,
        .movements-table td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
        }
        
        .movements-table th {
            background-color: #667eea;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 8px;
        }
        
        .movements-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .text-end {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .saldo-inicial {
            background-color: #e3f2fd !important;
            font-weight: bold;
        }
        
        .summary-section {
            margin-top: 20px;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            page-break-inside: avoid;
        }
        
        .summary-title {
            color: #667eea;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #667eea;
            padding-bottom: 3px;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
        }
        
        .summary-row {
            display: table-row;
        }
        
        .summary-cell {
            display: table-cell;
            width: 50%;
            padding: 4px;
        }
        
        .summary-item {
            background: white;
            padding: 5px;
            margin: 2px;
            border-radius: 3px;
            border-left: 3px solid #667eea;
            font-size: 10px;
        }
        
        .summary-label {
            font-weight: bold;
            display: inline-block;
            width: 80px;
        }
        
        .badge {
            padding: 2px 6px;
            border-radius: 10px;
            color: white;
            font-size: 8px;
            font-weight: bold;
        }
        
        .badge.bg-success { background-color: #28a745; }
        .badge.bg-danger { background-color: #dc3545; }
        .badge.bg-warning { background-color: #ffc107; color: #212529; }
        .badge.bg-info { background-color: #17a2b8; }
        .badge.bg-secondary { background-color: #6c757d; }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
        
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
            .header { margin-bottom: 15px; }
        }
    </style>
    <script>
        window.onload = function() {
            setTimeout(() => {
                window.print();
            }, 500);
        }
    </script>
</head>
<body>
    <div class="no-print" style="position: fixed; top: 10px; right: 10px; z-index: 1000;">
        <button onclick="window.print()" style="background: #667eea; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer;">
            🖨️ Imprimir/Guardar PDF
        </button>
    </div>

    <div class="header">
        <h1>KARDEX DE PRODUCTO</h1>
        <h2>{{ $data['producto']['sku'] ?? 'N/A' }} - {{ $data['producto']['nombre'] ?? 'N/A' }}</h2>
    </div>
    
    <div class="info-section">
        <div class="info-column">
            <div class="info-row">
                <span class="info-label">Producto:</span>
                <span class="info-value">{{ $data['producto']['sku'] ?? 'N/A' }} - {{ $data['producto']['nombre'] ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Descripción:</span>
                <span class="info-value">{{ $data['producto']['descripcion'] ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Bodega:</span>
                <span class="info-value">
                @if($data['bodega'])
                    {{ $data['bodega']['codigo'] }} - {{ $data['bodega']['nombre'] }}
                @else
                    Todas las bodegas
                @endif
                </span>
            </div>
        </div>
        <div class="info-column">
            <div class="info-row">
                <span class="info-label">Período:</span>
                <span class="info-value">
                {{ \Carbon\Carbon::parse($data['periodo']['fecha_inicio'])->format('d/m/Y') }} al 
                {{ \Carbon\Carbon::parse($data['periodo']['fecha_fin'])->format('d/m/Y') }}
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha Reporte:</span>
                <span class="info-value">{{ now()->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Generado por:</span>
                <span class="info-value">{{ auth()->user()->name ?? 'Sistema' }}</span>
            </div>
        </div>
    </div>
    
    <table class="movements-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Observaciones</th>
                <th>Bodega Origen</th>
                <th>Bodega Destino</th>
                <th>Entrada</th>
                <th>Salida</th>
                <th>Saldo</th>
                <th>Costo Unit.</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            <tr class="saldo-inicial">
                <td colspan="5" class="text-center"><strong>SALDO INICIAL</strong></td>
                <td class="text-end">-</td>
                <td class="text-end">-</td>
                <td class="text-end"><strong>{{ number_format($data['saldo_inicial'], 2) }}</strong></td>
                <td class="text-end">-</td>
                <td class="text-end">-</td>
            </tr>
            
            @foreach($data['movimientos'] as $movimiento)
                @php
                    $entrada = (float)$movimiento['entrada'];
                    $salida = (float)$movimiento['salida'];
                    $saldo = (float)$movimiento['saldo'];
                    $costoUnitario = (float)$movimiento['costo_unitario'];
                    $valorEntrada = (float)($movimiento['valor_entrada'] ?? 0);
                    $valorSalida = (float)($movimiento['valor_salida'] ?? 0);
                    
                    $tipoClass = match(strtolower($movimiento['tipo_movimiento'])) {
                        'entrada' => 'bg-success',
                        'salida' => 'bg-danger',
                        'ajuste' => 'bg-warning',
                        'transferencia' => 'bg-info',
                        default => 'bg-secondary'
                    };
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($movimiento['fecha'])->format('d/m/Y') }}</td>
                    <td class="text-center">
                        <span class="badge {{ $tipoClass }}">{{ $movimiento['tipo_movimiento'] }}</span>
                    </td>
                    <td>{{ $movimiento['observaciones'] ?: '-' }}</td>
                    <td>{{ $movimiento['bodega_origen'] ?: '-' }}</td>
                    <td>{{ $movimiento['bodega_destino'] ?: '-' }}</td>
                    <td class="text-end">{{ $entrada > 0 ? number_format($entrada, 2) : '-' }}</td>
                    <td class="text-end">{{ $salida > 0 ? number_format($salida, 2) : '-' }}</td>
                    <td class="text-end"><strong>{{ number_format($saldo, 2) }}</strong></td>
                    <td class="text-end">${{ number_format($costoUnitario, 2) }}</td>
                    <td class="text-end">
                        @if($valorEntrada > 0)
                            ${{ number_format($valorEntrada, 2) }}
                        @elseif($valorSalida > 0)
                            ${{ number_format($valorSalida, 2) }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="summary-section">
        <div class="summary-title">RESUMEN DEL PERÍODO</div>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell">
                    <div class="summary-item">
                        <span class="summary-label">Total Entradas:</span>
                        <span>{{ number_format($data['resumen']['total_entradas'], 2) }}</span>
                    </div>
                </div>
                <div class="summary-cell">
                    <div class="summary-item">
                        <span class="summary-label">Valor Entradas:</span>
                        <span>${{ number_format($data['resumen']['valor_total_entradas'], 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="summary-row">
                <div class="summary-cell">
                    <div class="summary-item">
                        <span class="summary-label">Total Salidas:</span>
                        <span>{{ number_format($data['resumen']['total_salidas'], 2) }}</span>
                    </div>
                </div>
                <div class="summary-cell">
                    <div class="summary-item">
                        <span class="summary-label">Valor Salidas:</span>
                        <span>${{ number_format($data['resumen']['valor_total_salidas'], 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="summary-row">
                <div class="summary-cell">
                    <div class="summary-item">
                        <span class="summary-label">Saldo Final:</span>
                        <span><strong>{{ number_format($data['resumen']['saldo_final'], 2) }}</strong></span>
                    </div>
                </div>
                <div class="summary-cell">
                    <div class="summary-item">
                        <span class="summary-label">Total Movimientos:</span>
                        <span><strong>{{ $data['resumen']['total_movimientos'] }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>Sistema de Inventario - Generado el {{ now()->format('d/m/Y \a \l\a\s H:i:s') }}</p>
    </div>
</body>
</html>