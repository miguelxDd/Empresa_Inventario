# Manual de Testing - Sistema de Inventario

## Descripción General

Este documento proporciona ejemplos completos para probar el `InventoryMovementService` y sus endpoints API.

## Configuración Inicial

### 1. Ejecutar las Migraciones y Seeders

```cmd
cd c:\xampp2025\htdocs\Empresa_Inventario
php artisan migrate:fresh --seed
```

Este comando:
- Recrea la base de datos completamente
- Ejecuta todas las migraciones (21 archivos)
- Ejecuta todos los seeders creando datos de prueba

### 2. Verificar Datos de Prueba

```cmd
php artisan tinker
```

```php
// Verificar que hay datos
App\Models\Producto::count(); // Debería mostrar 22
App\Models\Bodega::count(); // Debería mostrar 10
App\Models\User::count(); // Debería mostrar 4
App\Models\Cuenta::count(); // Debería mostrar 54

// Ver algunos productos disponibles
App\Models\Producto::with('categoria', 'unidadMedida')->take(3)->get();

// Ver bodegas disponibles
App\Models\Bodega::take(3)->get();

// Ver cuentas contables
App\Models\Cuenta::where('tipo_cuenta', 'inventario')->first();
```

## Testing del Servicio

### 3. Pruebas Unitarias con PHPUnit

```cmd
# Ejecutar todas las pruebas
php artisan test

# Ejecutar solo las pruebas de inventario
php artisan test --filter InventoryMovement

# Ejecutar con más detalle
php artisan test --filter InventoryMovement --verbose
```

### 4. Pruebas Manuales con Artisan Tinker

#### 4.1 Movimiento de Entrada

```cmd
php artisan tinker
```

```php
use App\Services\InventoryMovementService;
use App\Models\Producto;
use App\Models\Bodega;
use App\Models\User;
use App\Models\Cuenta;

$service = app(InventoryMovementService::class);

// Obtener IDs necesarios
$producto = Producto::first();
$bodega = Bodega::first();
$usuario = User::first();
$cuentaInventario = Cuenta::where('tipo_cuenta', 'inventario')->first();
$cuentaProveedores = Cuenta::where('codigo_cuenta', '2205')->first() ?? $cuentaInventario;

// Crear movimiento de entrada
$entrada = [
    'tipo_movimiento' => 'entrada',
    'fecha_movimiento' => now()->format('Y-m-d'),
    'observaciones' => 'Compra de mercancía - Test manual',
    'usuario_id' => $usuario->id,
    'bodega_id' => $bodega->id,
    'detalles' => [
        [
            'producto_id' => $producto->id,
            'cantidad' => 100,
            'precio_unitario' => 15000.00,
            'observaciones' => 'Entrada inicial de prueba'
        ]
    ],
    'asiento_contable' => [
        'cuenta_debito' => $cuentaInventario->id,
        'cuenta_credito' => $cuentaProveedores->id,
        'concepto' => 'Compra de inventario - Test manual'
    ]
];

$resultado = $service->createAndPost($entrada);

// Verificar resultado
echo "Movimiento ID: " . $resultado->movimiento->id;
echo "Número: " . $resultado->movimiento->numero;
echo "Estado: " . $resultado->movimiento->estado;
echo "Asiento ID: " . ($resultado->asiento ? $resultado->asiento->id : 'Sin asiento');
```

#### 4.2 Movimiento de Salida

```php
// Crear movimiento de salida (después de tener existencias)
$salida = [
    'tipo_movimiento' => 'salida',
    'fecha_movimiento' => now()->format('Y-m-d'),
    'observaciones' => 'Venta de mercancía - Test manual',
    'usuario_id' => $usuario->id,
    'bodega_id' => $bodega->id,
    'detalles' => [
        [
            'producto_id' => $producto->id,
            'cantidad' => 30,
            'precio_unitario' => 20000.00,
            'observaciones' => 'Venta de prueba'
        ]
    ],
    'asiento_contable' => [
        'cuenta_debito' => Cuenta::where('codigo_cuenta', '1305')->first()->id ?? $cuentaInventario->id,
        'cuenta_credito' => $cuentaInventario->id,
        'concepto' => 'Venta de inventario - Test manual'
    ]
];

$resultadoSalida = $service->createAndPost($salida);
echo "Salida creada - ID: " . $resultadoSalida->movimiento->id;
```

#### 4.3 Movimiento de Transferencia

```php
$bodegaDestino = Bodega::where('id', '!=', $bodega->id)->first();

$transferencia = [
    'tipo_movimiento' => 'transferencia',
    'fecha_movimiento' => now()->format('Y-m-d'),
    'observaciones' => 'Transferencia entre bodegas - Test manual',
    'usuario_id' => $usuario->id,
    'bodega_id' => $bodega->id,
    'bodega_destino_id' => $bodegaDestino->id,
    'detalles' => [
        [
            'producto_id' => $producto->id,
            'cantidad' => 20,
            'precio_unitario' => 15000.00,
            'observaciones' => 'Transferencia de prueba'
        ]
    ],
    'asiento_contable' => [
        'cuenta_debito' => $cuentaInventario->id,
        'cuenta_credito' => $cuentaInventario->id,
        'concepto' => 'Transferencia de inventario - Test manual'
    ]
];

$resultadoTransfer = $service->createAndPost($transferencia);
echo "Transferencia creada - ID: " . $resultadoTransfer->movimiento->id;
```

## Testing de la API

### 5. Probar Endpoints con cURL o Postman

#### 5.1 Obtener Opciones de Configuración

```cmd
curl -X GET http://localhost/Empresa_Inventario/public/api/movimientos/config/options
```

#### 5.2 Obtener Ejemplos de Payloads

```cmd
curl -X GET http://localhost/Empresa_Inventario/public/api/movimientos/config/examples
```

#### 5.3 Crear Movimiento de Entrada

```cmd
curl -X POST http://localhost/Empresa_Inventario/public/api/movimientos ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -d "{\"tipo_movimiento\":\"entrada\",\"fecha_movimiento\":\"2025-01-02\",\"observaciones\":\"Compra via API\",\"usuario_id\":1,\"bodega_id\":1,\"detalles\":[{\"producto_id\":1,\"cantidad\":50,\"precio_unitario\":12000.00,\"observaciones\":\"Test API\"}],\"asiento_contable\":{\"cuenta_debito\":1,\"cuenta_credito\":2,\"concepto\":\"Compra API\"}}"
```

#### 5.4 Consultar Movimiento

```cmd
curl -X GET http://localhost/Empresa_Inventario/public/api/movimientos/1
```

#### 5.5 Cancelar Movimiento

```cmd
curl -X PATCH http://localhost/Empresa_Inventario/public/api/movimientos/1/cancel ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -d "{\"motivo_cancelacion\":\"Cancelación de prueba\"}"
```

## Verificación de Resultados

### 6. Verificar en Base de Datos

```cmd
php artisan tinker
```

```php
// Verificar movimientos creados
use App\Models\Movimiento;
use App\Models\DetalleMovimiento;
use App\Models\Asiento;
use App\Models\ExistenciaProducto;

// Ver últimos movimientos
Movimiento::with('detalles.producto', 'asiento')->latest()->take(3)->get();

// Ver existencias actuales
ExistenciaProducto::with('producto', 'bodega')->where('cantidad', '>', 0)->get();

// Ver asientos contables generados
Asiento::with('detalles.cuenta')->latest()->take(3)->get();

// Verificar que los totales cuadren
$movimiento = Movimiento::with('detalles')->latest()->first();
$totalCalculado = $movimiento->detalles->sum(function($d) { return $d->cantidad * $d->costo_unitario; });
echo "Total en movimiento: " . $movimiento->valor_total;
echo "Total calculado: " . $totalCalculado;
```

### 7. Verificar Stored Procedures

```php
// Verificar que los SPs se ejecutaron correctamente
use Illuminate\Support\Facades\DB;

// Verificar movimiento procesado
$movimientoId = 1; // Cambiar por el ID del movimiento creado
$resultado = DB::select('SELECT * FROM movimientos_inventario WHERE id = ?', [$movimientoId]);
print_r($resultado);

// Verificar existencias actualizadas
$existencias = DB::select('SELECT * FROM existencias_producto WHERE cantidad > 0');
print_r($existencias);
```

## Casos de Error

### 8. Probar Manejo de Errores

```php
// Test: Salida sin existencias suficientes
try {
    $salidaError = [
        'tipo_movimiento' => 'salida',
        'fecha_movimiento' => now()->format('Y-m-d'),
        'observaciones' => 'Salida imposible',
        'usuario_id' => $usuario->id,
        'bodega_id' => $bodega->id,
        'detalles' => [
            [
                'producto_id' => $producto->id,
                'cantidad' => 999999, // Cantidad imposible
                'precio_unitario' => 20000.00,
                'observaciones' => 'Test error'
            ]
        ],
        'asiento_contable' => [
            'cuenta_debito' => $cuentaInventario->id,
            'cuenta_credito' => $cuentaInventario->id,
            'concepto' => 'Test error'
        ]
    ];
    
    $service->createAndPost($salidaError);
} catch (\App\Exceptions\InventoryException $e) {
    echo "Error capturado correctamente: " . $e->getMessage();
}
```

## Logs y Debugging

### 9. Verificar Logs

```cmd
# Ver logs de Laravel
type storage\logs\laravel.log

# Ver últimas entradas del log
php artisan log:clear
# Después de ejecutar operaciones, revisar el log nuevamente
```

### 10. Habilitar Query Log

```php
// En tinker, para ver todas las queries ejecutadas
DB::enableQueryLog();

// Ejecutar operación
$service->createAndPost($entrada);

// Ver queries ejecutadas
$queries = DB::getQueryLog();
foreach($queries as $query) {
    echo $query['query'] . "\n";
    print_r($query['bindings']);
    echo "Time: " . $query['time'] . "ms\n\n";
}
```

## Notas Importantes

1. **Stored Procedures**: El sistema usa SPs para manejar la lógica de inventario. El servicio los llama pero no duplica la lógica.

2. **Transacciones**: Todas las operaciones están envueltas en transacciones DB para garantizar consistencia.

3. **Validación**: El servicio valida datos antes de procesarlos y maneja errores de los SPs.

4. **Asientos Contables**: Se generan automáticamente para cada movimiento usando el SP `generar_asiento_contable`.

5. **Estados**: Los movimientos pueden estar en estado 'pendiente', 'procesado' o 'cancelado'.

6. **Auditoría**: Todos los movimientos quedan registrados con usuario, fecha y observaciones para auditoría.
