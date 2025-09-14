<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\BodegaController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\Api\InventoryMovementController;

Route::get('/', function () {
    return view('welcome');
});

// Ruta de diagnóstico
Route::get('/diagnostico', function () {
    return view('diagnostico');
})->name('diagnostico');

// Rutas para el CRUD de Productos
Route::resource('productos', ProductoController::class);
Route::patch('productos/{producto}/toggle', [ProductoController::class, 'toggle'])->name('productos.toggle');
Route::get('productos/{producto}/existencias', [ProductoController::class, 'existencias'])->name('productos.existencias');
Route::get('productos-options', [ProductoController::class, 'getFormOptions'])->name('productos.options');

// Rutas para el CRUD de Bodegas
Route::resource('bodegas', BodegaController::class);
Route::get('bodegas-data', [BodegaController::class, 'data'])->name('bodegas.data');
Route::get('bodegas-statistics', [BodegaController::class, 'statistics'])->name('bodegas.statistics');
Route::get('bodegas-stats', [BodegaController::class, 'stats'])->name('bodegas.stats');
Route::post('bodegas/{bodega}/toggle', [BodegaController::class, 'toggle'])->name('bodegas.toggle');
Route::get('bodegas/{bodega}/inventario', [BodegaController::class, 'inventario'])->name('bodegas.inventario');
Route::get('bodegas-options', [BodegaController::class, 'getOptions'])->name('bodegas.options');
Route::get('users-list', [BodegaController::class, 'getUsersList'])->name('users.list');

// Rutas para Movimientos de Inventario
Route::resource('movimientos', MovimientoInventarioController::class)->only(['index', 'create', 'store']);
Route::get('movimientos-options', [MovimientoInventarioController::class, 'getFormOptions'])->name('movimientos.options');
Route::get('movimientos-list', [MovimientoInventarioController::class, 'getMovements'])->name('movimientos.list');
Route::get('producto-cost', [MovimientoInventarioController::class, 'getProductCost'])->name('producto.cost');

// Rutas para Reportes
Route::prefix('reportes')->name('reportes.')->group(function () {
    Route::get('/', [ReporteController::class, 'index'])->name('index');
    
    // Kardex
    Route::get('/kardex', [ReporteController::class, 'kardex'])->name('kardex');
    Route::post('/kardex/generar', [ReporteController::class, 'generateKardex'])->name('kardex.generar');
    
    // Existencias
    Route::get('/existencias', [ReporteController::class, 'existencias'])->name('existencias');
    Route::get('/existencias/data', [ReporteController::class, 'getExistencias'])->name('existencias.data');
    Route::get('/existencias/export', [ReporteController::class, 'exportExistenciasCSV'])->name('existencias.export');
    
    // Asientos Contables
    Route::get('/asientos', [ReporteController::class, 'asientos'])->name('asientos');
    Route::get('/asientos/data', [ReporteController::class, 'getAsientos'])->name('asientos.data');
    
    // Opciones generales
    Route::get('/options', [ReporteController::class, 'getOptions'])->name('options');
});

// Rutas para Contabilidad
Route::prefix('contabilidad')->name('contabilidad.')->group(function () {
    // Libro Diario
    Route::get('/diario', [\App\Http\Controllers\DiarioController::class, 'index'])->name('diario');
    Route::get('/diario/data', [\App\Http\Controllers\DiarioController::class, 'getData'])->name('diario.data');
    Route::get('/diario/export', [\App\Http\Controllers\DiarioController::class, 'export'])->name('diario.export');
    
    // Libro Mayor
    Route::get('/mayor', [\App\Http\Controllers\MayorController::class, 'index'])->name('mayor');
    Route::get('/mayor/data', [\App\Http\Controllers\MayorController::class, 'getData'])->name('mayor.data');
    Route::get('/mayor/export', [\App\Http\Controllers\MayorController::class, 'export'])->name('mayor.export');
    
    // Balanza de Comprobación
    Route::get('/balanza', [\App\Http\Controllers\BalanzaController::class, 'index'])->name('balanza');
    Route::get('/balanza/data', [\App\Http\Controllers\BalanzaController::class, 'getData'])->name('balanza.data');
    Route::get('/balanza/export', [\App\Http\Controllers\BalanzaController::class, 'export'])->name('balanza.export');
    
    // API para autocomplete de cuentas
    Route::get('/cuentas/search', [\App\Http\Controllers\CuentaController::class, 'search'])->name('cuentas.search');
});

// Rutas para probar los modelos generados (API)
Route::prefix('api')->group(function () {
    Route::get('/productos', [ProductoController::class, 'index']);
    Route::get('/productos/{id}', [ProductoController::class, 'show']);
    Route::get('/productos/categoria/{categoriaId}', [ProductoController::class, 'porCategoria']);
    Route::get('/inventario/estadisticas', [ProductoController::class, 'estadisticas']);
    
    // Rutas para movimientos de inventario
    Route::prefix('movimientos')->group(function () {
        Route::post('/', [InventoryMovementController::class, 'createAndPost']);
        Route::get('/{id}', [InventoryMovementController::class, 'show']);
        Route::patch('/{id}/cancel', [InventoryMovementController::class, 'cancel']);
        Route::get('/config/options', [InventoryMovementController::class, 'getOptions']);
        Route::get('/config/examples', [InventoryMovementController::class, 'getExamples']);
    });
});

// Ruta de prueba para verificar conexión a BD
Route::get('/test-db', function () {
    try {
        $productos = \App\Models\Producto::count();
        $categorias = \App\Models\Categoria::count();
        $bodegas = \App\Models\Bodega::count();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Conexión a base de datos exitosa',
            'data' => [
                'productos' => $productos,
                'categorias' => $categorias,
                'bodegas' => $bodegas
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error de conexión: ' . $e->getMessage()
        ], 500);
    }
});
