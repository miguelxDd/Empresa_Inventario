<?php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->bind('env', 'local');

use App\Models\MovimientoInventario;
use App\Models\MovimientoDetalle;
use App\Models\Existencia;
use Illuminate\Support\Facades\DB;

echo "=== PRUEBA DE MOVIMIENTO DE INVENTARIO ===" . PHP_EOL;

// Verificar estado inicial de existencias
echo "Estado inicial de existencias:" . PHP_EOL;
$existenciasIniciales = Existencia::all();
if ($existenciasIniciales->count() > 0) {
    foreach ($existenciasIniciales as $existencia) {
        echo "- Producto {$existencia->producto_id}, Bodega {$existencia->bodega_id}: {$existencia->cantidad}" . PHP_EOL;
    }
} else {
    echo "- No hay existencias registradas" . PHP_EOL;
}

DB::beginTransaction();
try {
    // Crear movimiento de entrada
    $movimiento = MovimientoInventario::create([
        'numero' => 'MOV-' . date('YmdHis'),
        'tipo' => 'entrada',
        'bodega_destino_id' => 1,
        'observaciones' => 'Prueba de entrada de inventario',
        'total' => 340,
        'estado' => 'pendiente'
    ]);
    
    echo "✓ Movimiento creado: ID {$movimiento->id}" . PHP_EOL;
    
    // Crear detalles del movimiento
    $detalles = [
        ['producto_id' => 1, 'cantidad' => 100, 'costo_unitario' => 2.50, 'total' => 250],
        ['producto_id' => 2, 'cantidad' => 50, 'costo_unitario' => 1.80, 'total' => 90]
    ];
    
    foreach ($detalles as $detalle) {
        MovimientoDetalle::create([
            'movimiento_id' => $movimiento->id,
            'producto_id' => $detalle['producto_id'],
            'cantidad' => $detalle['cantidad'],
            'costo_unitario' => $detalle['costo_unitario'],
            'total' => $detalle['total']
        ]);
    }
    
    echo "✓ Detalles del movimiento creados" . PHP_EOL;
    
    // Simular actualización de existencias usando el método del controlador
    $controller = new \App\Http\Controllers\MovimientoInventarioController();
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('actualizarExistencias');
    $method->setAccessible(true);
    $method->invoke($controller, $movimiento, $detalles);
    
    echo "✓ Existencias actualizadas" . PHP_EOL;
    
    DB::commit();
    echo "✓ Transacción completada exitosamente" . PHP_EOL;
    
} catch (Exception $e) {
    DB::rollBack();
    echo "✗ Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}

// Verificar estado final de existencias
echo PHP_EOL . "Estado final de existencias:" . PHP_EOL;
$existenciasFinales = Existencia::all();
if ($existenciasFinales->count() > 0) {
    foreach ($existenciasFinales as $existencia) {
        echo "- Producto {$existencia->producto_id}, Bodega {$existencia->bodega_id}: {$existencia->cantidad}" . PHP_EOL;
    }
} else {
    echo "- No hay existencias registradas" . PHP_EOL;
}

echo PHP_EOL . "=== FIN DE LA PRUEBA ===" . PHP_EOL;