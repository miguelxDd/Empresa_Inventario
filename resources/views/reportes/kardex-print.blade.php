<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kardex - {{ $data['producto']['sku'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #667eea;
            margin: 0;
            font-size: 24px;
        }
        
        .header h2 {
            color: #333;
            margin: 10px 0 0 0;
            font-size: 18px;
        }
        
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        
        .info-column {
            width: 48%;
        }
        
        .info-row {
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        
        .movements-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .movements-table th,
        .movements-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        
        .movements-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        .movements-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .movements-table tbody tr:hover {
            background-color: #f5f5f5;
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
            margin-top: 30px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        
        .summary-title {
            color: #667eea;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 1px solid #667eea;
            padding-bottom: 5px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 8px;
            background: white;
            border-radius: 3px;
            border-left: 3px solid #667eea;
        }
        
        .summary-label {
            font-weight: bold;
        }
        
        .badge {
            padding: 3px 8px;
            border-radius: 12px;
            color: white;
            font-size: 10px;
            font-weight: bold;
        }
        
        .badge.bg-success { background-color: #28a745; }
        .badge.bg-danger { background-color: #dc3545; }
        .badge.bg-warning { background-color: #ffc107; color: #212529; }
        .badge.bg-info { background-color: #17a2b8; }
        .badge.bg-secondary { background-color: #6c757d; }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .header {
                margin-bottom: 20px;
                padding-bottom: 15px;
            }
            
            .movements-table {
                page-break-inside: avoid;
            }
            
            .summary-section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>KARDEX DE PRODUCTO</h1>
        <h2>{{ $data['producto']['sku'] ?? 'N/A' }} - {{ $data['producto']['nombre'] ?? 'N/A' }}</h2>
    </div>
    
    <div class="info-section">
        <div class="info-column">
            <div class="info-row">
                <span class="info-label">Producto:</span>
                {{ $data['producto']['sku'] ?? 'N/A' }} - {{ $data['producto']['nombre'] ?? 'N/A' }}
            </div>
            <div class="info-row">
                <span class="info-label">Descripción:</span>
                {{ $data['producto']['descripcion'] ?? 'N/A' }}
            </div>
            <div class="info-row">
                <span class="info-label">Bodega:</span>
                @if($data['bodega'])
                    {{ $data['bodega']['codigo'] }} - {{ $data['bodega']['nombre'] }}
                @else
                    Todas las bodegas
                @endif
            </div>
        </div>
        <div class="info-column">
            <div class="info-row">
                <span class="info-label">Período:</span>
                {{ \Carbon\Carbon::parse($data['periodo']['fecha_inicio'])->format('d/m/Y') }} al 
                {{ \Carbon\Carbon::parse($data['periodo']['fecha_fin'])->format('d/m/Y') }}
            </div>
            <div class="info-row">
                <span class="info-label">Fecha Reporte:</span>
                {{ now()->format('d/m/Y H:i:s') }}
            </div>
            <div class="info-row">
                <span class="info-label">Generado por:</span>
                {{ auth()->user()->name ?? 'Sistema' }}
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
                <th class="text-end">Entrada</th>
                <th class="text-end">Salida</th>
                <th class="text-end">Saldo</th>
                <th class="text-end">Costo Unit.</th>
                <th class="text-end">Valor</th>
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
            <div class="summary-item">
                <span class="summary-label">Total Entradas:</span>
                <span>{{ number_format($data['resumen']['total_entradas'], 2) }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Valor Entradas:</span>
                <span>${{ number_format($data['resumen']['valor_total_entradas'], 2) }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Total Salidas:</span>
                <span>{{ number_format($data['resumen']['total_salidas'], 2) }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Valor Salidas:</span>
                <span>${{ number_format($data['resumen']['valor_total_salidas'], 2) }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Saldo Final:</span>
                <span><strong>{{ number_format($data['resumen']['saldo_final'], 2) }}</strong></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Total Movimientos:</span>
                <span><strong>{{ $data['resumen']['total_movimientos'] }}</strong></span>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>Sistema de Inventario - Generado el {{ now()->format('d/m/Y \a \l\a\s H:i:s') }}</p>
    </div>
    
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>