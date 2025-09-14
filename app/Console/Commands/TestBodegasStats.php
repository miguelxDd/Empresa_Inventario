<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\BodegaController;

class TestBodegasStats extends Command
{
    protected $signature = 'test:bodegas-stats';
    protected $description = 'Prueba el método de estadísticas de bodegas';

    public function handle()
    {
        $this->info('=== PRUEBA DE ESTADÍSTICAS DE BODEGAS ===');
        
        try {
            $controller = new BodegaController();
            $response = $controller->statistics();
            $data = json_decode($response->getContent(), true);
            
            $this->info('Respuesta del método statistics:');
            $this->line(json_encode($data, JSON_PRETTY_PRINT));
            
            if ($data['success']) {
                $stats = $data['data'];
                $this->info("✓ Total bodegas: {$stats['total']}");
                $this->info("✓ Bodegas activas: {$stats['activas']}");
                $this->info("✓ Bodegas inactivas: {$stats['inactivas']}");
                $this->info("✓ Con inventario: {$stats['con_inventario']}");
            } else {
                $this->error('✗ Error en estadísticas: ' . $data['message']);
            }
            
        } catch (\Exception $e) {
            $this->error('✗ Error al obtener estadísticas: ' . $e->getMessage());
        }
        
        $this->info('=== PRUEBA COMPLETADA ===');
        return 0;
    }
}
