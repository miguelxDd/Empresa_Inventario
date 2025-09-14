<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Bodega;

class BodegasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bodegas = [
            // Bodegas principales
            [
                'codigo' => 'CENTRAL',
                'nombre' => 'Bodega Central',
                'ubicacion' => 'Av. Principal 123, Zona Industrial',
                'activa' => true,
                'responsable_id' => null,
                'created_by' => 1,
                'updated_by' => null
            ],
            [
                'codigo' => 'SECUNDARIA',
                'nombre' => 'Bodega Secundaria',
                'ubicacion' => 'Calle Secundaria 456, Zona Comercial',
                'activa' => true,
                'responsable_id' => null,
                'created_by' => 1,
                'updated_by' => null
            ],
            
            // Bodegas especializadas por tipo de producto
            [
                'codigo' => 'REFRIGERADOS',
                'nombre' => 'Bodega de Productos Refrigerados',
                'ubicacion' => 'Av. Principal 123, Zona Industrial - Sector B',
                'activa' => true,
                'responsable_id' => null,
                'created_by' => 1,
                'updated_by' => null
            ],
            [
                'codigo' => 'CONGELADOS',
                'nombre' => 'Bodega de Productos Congelados',
                'ubicacion' => 'Av. Principal 123, Zona Industrial - Sector C',
                'activa' => true,
                'responsable_id' => null,
                'created_by' => 1,
                'updated_by' => null
            ],
            [
                'codigo' => 'QUIMICOS',
                'nombre' => 'Bodega de Productos Químicos',
                'ubicacion' => 'Zona Industrial - Área Restringida',
                'activa' => true,
                'responsable_id' => null,
                'created_by' => 1,
                'updated_by' => null
            ],
            [
                'codigo' => 'FARMACEUTICOS',
                'nombre' => 'Bodega Farmacéutica',
                'ubicacion' => 'Zona Especial Farmacéutica',
                'activa' => true,
                'responsable_id' => null,
                'created_by' => 1,
                'updated_by' => null
            ],
            
            // Bodegas por ubicación geográfica
            [
                'codigo' => 'NORTE',
                'nombre' => 'Bodega Zona Norte',
                'ubicacion' => 'Zona Norte - Av. Norte 789',
                'activa' => true,
                'responsable_id' => null,
                'created_by' => 1,
                'updated_by' => null
            ],
            [
                'codigo' => 'SUR',
                'nombre' => 'Bodega Zona Sur',
                'ubicacion' => 'Zona Sur - Av. Sur 321',
                'activa' => true,
                'responsable_id' => null,
                'created_by' => 1,
                'updated_by' => null
            ],
            [
                'codigo' => 'TRANSITO',
                'nombre' => 'Bodega en Tránsito',
                'ubicacion' => 'Virtual - Control de tránsito',
                'activa' => true,
                'responsable_id' => null,
                'created_by' => 1,
                'updated_by' => null
            ],
            [
                'codigo' => 'DAÑADOS',
                'nombre' => 'Bodega de Productos Dañados',
                'ubicacion' => 'Área de Control de Calidad',
                'activa' => true,
                'responsable_id' => null,
                'created_by' => 1,
                'updated_by' => null
            ]
        ];

        foreach ($bodegas as $bodega) {
            Bodega::create($bodega);
        }

        $this->command->info('✅ Bodegas creadas exitosamente: ' . count($bodegas) . ' bodegas.');
    }
}
