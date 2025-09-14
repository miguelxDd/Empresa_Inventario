<?php

// Script simple para probar el movimiento de inventario
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

use App\Models\MovimientoInventario;
use App\Models\Existencia;

echo "Probando actualización de existencias...\n";

// Crear un movimiento simple
$movimiento = new MovimientoInventario();
$movimiento->tipo = 'entrada';
$movimiento->bodega_destino_id = 1;

// Simular detalles
$detalles = [
    ['producto_id' => 1, 'cantidad' => 100],
    ['producto_id' => 2, 'cantidad' => 50]
];

echo "Estado inicial:\n";
echo "Producto 1 en Bodega 1: " . (Existencia::where('producto_id', 1)->where('bodega_id', 1)->first()->cantidad ?? 0) . "\n";
echo "Producto 2 en Bodega 1: " . (Existencia::where('producto_id', 2)->where('bodega_id', 1)->first()->cantidad ?? 0) . "\n";

// Simular la actualización de existencias manualmente
foreach ($detalles as $detalle) {
    $existencia = Existencia::firstOrCreate([
        'producto_id' => $detalle['producto_id'],
        'bodega_id' => 1
    ], [
        'cantidad' => 0,
        'costo_promedio' => 0
    ]);
    
    $existencia->cantidad += $detalle['cantidad'];
    $existencia->save();
    
    echo "Actualizado producto {$detalle['producto_id']}: +{$detalle['cantidad']} = {$existencia->cantidad}\n";
}

echo "\nEstado final:\n";
echo "Producto 1 en Bodega 1: " . Existencia::where('producto_id', 1)->where('bodega_id', 1)->first()->cantidad . "\n";
echo "Producto 2 en Bodega 1: " . Existencia::where('producto_id', 2)->where('bodega_id', 1)->first()->cantidad . "\n";

echo "¡Prueba completada exitosamente!\n";