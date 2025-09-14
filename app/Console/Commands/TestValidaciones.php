<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bodega;
use App\Models\Producto;
use App\Models\User;

class TestValidaciones extends Command
{
    protected $signature = 'test:validaciones';
    protected $description = 'Probar las validaciones de productos y movimientos';

    public function handle()
    {
        $this->info('=== PROBANDO VALIDACIONES ===');
        
        // Test 1: Verificar bodegas activas
        $bodegasActivas = Bodega::where('activa', true)->get(['id', 'codigo', 'nombre']);
        $this->info('Bodegas activas encontradas: ' . $bodegasActivas->count());
        
        foreach ($bodegasActivas->take(5) as $bodega) {
            $this->line("  - ID: {$bodega->id}, Código: {$bodega->codigo}, Nombre: {$bodega->nombre}");
        }

        // Test 2: Verificar productos activos
        $productosActivos = Producto::where('activo', true)->get(['id', 'sku', 'nombre']);
        $this->info('Productos activos encontrados: ' . $productosActivos->count());
        
        foreach ($productosActivos->take(3) as $producto) {
            $this->line("  - ID: {$producto->id}, SKU: {$producto->sku}, Nombre: {$producto->nombre}");
        }

        // Test 3: Verificar usuarios
        $usuarios = User::count();
        $this->info('Usuarios en el sistema: ' . $usuarios);

        // Test 4: Datos para prueba de movimiento
        if ($bodegasActivas->count() >= 2 && $productosActivos->count() >= 1) {
            $bodega1 = $bodegasActivas->first();
            $bodega2 = $bodegasActivas->skip(1)->first();
            $producto = $productosActivos->first();

            $this->info('');
            $this->info('=== DATOS PARA PRUEBA DE MOVIMIENTO ===');
            $this->info('Bodega Origen: ID ' . $bodega1->id . ' (' . $bodega1->nombre . ')');
            $this->info('Bodega Destino: ID ' . $bodega2->id . ' (' . $bodega2->nombre . ')');
            $this->info('Producto: ID ' . $producto->id . ' (' . $producto->nombre . ')');
            
            $this->info('');
            $this->info('Ejemplo de payload para movimiento de transferencia:');
            $this->line(json_encode([
                'fecha' => now()->format('Y-m-d'),
                'tipo' => 'transferencia',
                'referencia' => 'TEST-001',
                'observaciones' => 'Transferencia de prueba',
                'bodega_origen_id' => $bodega1->id,
                'bodega_destino_id' => $bodega2->id,
                'detalles' => [
                    [
                        'producto_id' => $producto->id,
                        'cantidad' => 10
                    ]
                ]
            ], JSON_PRETTY_PRINT));
        } else {
            $this->error('No hay suficientes datos para generar ejemplo de movimiento.');
        }

        $this->info('');
        $this->info('=== VALIDACIONES COMPLETADAS ===');
        
        return 0;
    }
}
