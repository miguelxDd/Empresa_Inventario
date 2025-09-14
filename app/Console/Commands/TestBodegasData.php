<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\BodegaController;

class TestBodegasData extends Command
{
    protected $signature = 'test:bodegas-data';
    protected $description = 'Prueba el método de datos de bodegas para DataTables';

    public function handle()
    {
        $this->info('=== PRUEBA DE DATOS DE BODEGAS ===');
        
        try {
            $controller = new BodegaController();
            $response = $controller->data();
            $data = json_decode($response->getContent(), true);
            
            if (isset($data['data']) && is_array($data['data'])) {
                $this->info("✓ Datos obtenidos correctamente");
                $this->info("✓ Total de registros: " . count($data['data']));
                
                if (count($data['data']) > 0) {
                    $primer = $data['data'][0];
                    $this->info("✓ Ejemplo de primer registro:");
                    $this->line("  - ID: {$primer['id']}");
                    $this->line("  - Código: {$primer['codigo']}");
                    $this->line("  - Nombre: {$primer['nombre']}");
                    $this->line("  - Ubicación: {$primer['ubicacion']}");
                    $this->line("  - Responsable: {$primer['responsable']}");
                    $this->line("  - Estado: " . strip_tags($primer['estado']));
                    $this->line("  - Tiene acciones: " . (strlen($primer['acciones']) > 100 ? 'SÍ' : 'NO'));
                }
            } else {
                $this->error('✗ Formato de respuesta inválido');
                $this->line('Respuesta recibida:');
                $this->line(json_encode($data, JSON_PRETTY_PRINT));
            }
            
        } catch (\Exception $e) {
            $this->error('✗ Error al obtener datos: ' . $e->getMessage());
        }
        
        $this->info('=== PRUEBA COMPLETADA ===');
        return 0;
    }
}
