<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class TestBodegasRoutes extends Command
{
    protected $signature = 'test:bodegas-routes';
    protected $description = 'Prueba las rutas de bodegas';

    public function handle()
    {
        $this->info('=== PRUEBA DE RUTAS DE BODEGAS ===');
        
        $routes = [
            'bodegas.index',
            'bodegas.data',
            'bodegas.statistics',
            'bodegas.store',
            'bodegas.edit',
            'bodegas.update',
            'bodegas.destroy',
            'bodegas.toggle',
            'bodegas.inventario',
            'bodegas.options',
            'users.list'
        ];
        
        foreach ($routes as $routeName) {
            try {
                if ($routeName === 'bodegas.edit' || $routeName === 'bodegas.update' || 
                    $routeName === 'bodegas.destroy' || $routeName === 'bodegas.toggle' || 
                    $routeName === 'bodegas.inventario') {
                    $url = route($routeName, ['bodega' => 1]);
                } else {
                    $url = route($routeName);
                }
                $this->info("✓ Ruta '{$routeName}' existe: {$url}");
            } catch (\Exception $e) {
                $this->error("✗ Ruta '{$routeName}' NO existe: " . $e->getMessage());
            }
        }
        
        $this->info('=== PRUEBA COMPLETADA ===');
        return 0;
    }
}
