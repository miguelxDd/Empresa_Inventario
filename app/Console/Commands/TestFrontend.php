<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bodega;
use App\Models\User;

class TestFrontend extends Command
{
    protected $signature = 'test:frontend';
    protected $description = 'Prueba las funcionalidades del frontend';

    public function handle()
    {
        $this->info('=== PRUEBA DE DATOS PARA FRONTEND ===');
        
        // Verificar bodegas
        $bodegas = Bodega::all();
        $this->info("Bodegas disponibles: " . $bodegas->count());
        
        foreach ($bodegas as $bodega) {
            $estado = $bodega->activa ? 'ACTIVA' : 'INACTIVA';
            $this->line("- ID: {$bodega->id}, Código: {$bodega->codigo}, Nombre: {$bodega->nombre}, Estado: {$estado}");
        }
        
        $this->newLine();
        
        // Verificar usuarios (responsables)
        $usuarios = User::all();
        $this->info("Usuarios disponibles: " . $usuarios->count());
        
        foreach ($usuarios as $usuario) {
            $this->line("- ID: {$usuario->id}, Nombre: {$usuario->name}, Email: {$usuario->email}");
        }
        
        $this->newLine();
        $this->info('=== PRUEBA DE RUTAS ===');
        
        // Verificar que las rutas existen
        $rutas = [
            'bodegas.index',
            'bodegas.store',
            'bodegas.edit',
            'bodegas.update',
            'bodegas.destroy',
            'bodegas.toggle',
            'bodegas.inventario',
            'bodegas.options'
        ];
        
        foreach ($rutas as $ruta) {
            try {
                $url = route($ruta, ['bodega' => 1], false);
                $this->info("✓ Ruta '{$ruta}' existe: {$url}");
            } catch (\Exception $e) {
                $this->error("✗ Ruta '{$ruta}' no existe: " . $e->getMessage());
            }
        }
        
        $this->newLine();
        $this->info('=== PRUEBA COMPLETADA ===');
    }
}
