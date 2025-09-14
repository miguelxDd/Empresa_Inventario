<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';

use App\Models\User;
use App\Models\Cuenta;
use App\Models\Producto;
use App\Models\Bodega;

echo "=== VERIFICANDO CONEXIÓN A BASE DE DATOS ===" . PHP_EOL;

try {
    echo "Productos: " . Producto::count() . PHP_EOL;
    echo "Bodegas: " . Bodega::count() . PHP_EOL;
    echo "Users: " . User::count() . PHP_EOL;
    echo "Cuentas: " . Cuenta::count() . PHP_EOL;
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "=== VERIFICANDO MODELOS ===" . PHP_EOL;

// Crear usuario si no existe
if (User::count() === 0) {
    try {
        $user = User::create([
            'name' => 'Usuario Test',
            'email' => 'test@empresa.com',
            'password' => bcrypt('password123')
        ]);
        echo "✅ Usuario creado con ID: " . $user->id . PHP_EOL;
    } catch (Exception $e) {
        echo "❌ Error creando usuario: " . $e->getMessage() . PHP_EOL;
        exit(1);
    }
} else {
    $user = User::first();
    echo "✅ Usuario existente con ID: " . $user->id . PHP_EOL;
}

// Verificar datos necesarios
try {
    $producto = Producto::first();
    $bodega = Bodega::first();
    $cuentaInventario = Cuenta::where('codigo', '1435')->first(); // Inventarios
    $cuentaProveedores = Cuenta::where('codigo', '2205')->first(); // Proveedores
    
    if (!$producto) {
        echo "❌ No hay productos en la base de datos" . PHP_EOL;
        exit(1);
    }
    
    if (!$bodega) {
        echo "❌ No hay bodegas en la base de datos" . PHP_EOL;
        exit(1);
    }
    
    if (!$cuentaInventario) {
        echo "❌ No se encontró cuenta de inventario (código 1435)" . PHP_EOL;
        exit(1);
    }
    
    if (!$cuentaProveedores) {
        echo "❌ No se encontró cuenta de proveedores (código 2205)" . PHP_EOL;
        exit(1);
    }
    
    echo "✅ Todos los modelos necesarios están disponibles" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error verificando modelos: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "=== VERIFICANDO SERVICIO ===" . PHP_EOL;

try {
    $service = app(\App\Services\InventoryMovementService::class);
    echo "✅ Servicio InventoryMovementService cargado correctamente" . PHP_EOL;
} catch (Exception $e) {
    echo "❌ Error cargando servicio: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

echo PHP_EOL . "✅ TODAS LAS VERIFICACIONES PASARON" . PHP_EOL;
