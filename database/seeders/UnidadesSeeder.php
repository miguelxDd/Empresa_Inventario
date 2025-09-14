<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Unidade;

class UnidadesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unidades = [
            // Unidades básicas de medida
            ['codigo' => 'UND', 'nombre' => 'Unidad', 'abreviatura' => 'Und', 'activa' => true],
            ['codigo' => 'KG', 'nombre' => 'Kilogramo', 'abreviatura' => 'Kg', 'activa' => true],
            ['codigo' => 'LT', 'nombre' => 'Litro', 'abreviatura' => 'Lt', 'activa' => true],
            ['codigo' => 'MT', 'nombre' => 'Metro', 'abreviatura' => 'Mt', 'activa' => true],
            ['codigo' => 'M2', 'nombre' => 'Metro Cuadrado', 'abreviatura' => 'M²', 'activa' => true],
            ['codigo' => 'M3', 'nombre' => 'Metro Cúbico', 'abreviatura' => 'M³', 'activa' => true],
            
            // Unidades de peso y masa
            ['codigo' => 'GR', 'nombre' => 'Gramo', 'abreviatura' => 'Gr', 'activa' => true],
            ['codigo' => 'TON', 'nombre' => 'Tonelada', 'abreviatura' => 'Ton', 'activa' => true],
            ['codigo' => 'LB', 'nombre' => 'Libra', 'abreviatura' => 'Lb', 'activa' => true],
            ['codigo' => 'OZ', 'nombre' => 'Onza', 'abreviatura' => 'Oz', 'activa' => true],
            
            // Unidades de volumen y capacidad
            ['codigo' => 'ML', 'nombre' => 'Mililitro', 'abreviatura' => 'Ml', 'activa' => true],
            ['codigo' => 'GAL', 'nombre' => 'Galón', 'abreviatura' => 'Gal', 'activa' => true],
            ['codigo' => 'PT', 'nombre' => 'Pinta', 'abreviatura' => 'Pt', 'activa' => true],
            ['codigo' => 'QT', 'nombre' => 'Cuarto', 'abreviatura' => 'Qt', 'activa' => true],
            
            // Unidades de longitud
            ['codigo' => 'CM', 'nombre' => 'Centímetro', 'abreviatura' => 'Cm', 'activa' => true],
            ['codigo' => 'MM', 'nombre' => 'Milímetro', 'abreviatura' => 'Mm', 'activa' => true],
            ['codigo' => 'KM', 'nombre' => 'Kilómetro', 'abreviatura' => 'Km', 'activa' => true],
            ['codigo' => 'IN', 'nombre' => 'Pulgada', 'abreviatura' => 'In', 'activa' => true],
            ['codigo' => 'FT', 'nombre' => 'Pie', 'abreviatura' => 'Ft', 'activa' => true],
            ['codigo' => 'YD', 'nombre' => 'Yarda', 'abreviatura' => 'Yd', 'activa' => true],
            
            // Unidades de empaque y comerciales
            ['codigo' => 'CAJ', 'nombre' => 'Caja', 'abreviatura' => 'Caj', 'activa' => true],
            ['codigo' => 'PAQ', 'nombre' => 'Paquete', 'abreviatura' => 'Paq', 'activa' => true],
            ['codigo' => 'BOL', 'nombre' => 'Bolsa', 'abreviatura' => 'Bol', 'activa' => true],
            ['codigo' => 'SAC', 'nombre' => 'Saco', 'abreviatura' => 'Sac', 'activa' => true],
            ['codigo' => 'TAM', 'nombre' => 'Tambor', 'abreviatura' => 'Tam', 'activa' => true],
            ['codigo' => 'BAR', 'nombre' => 'Barril', 'abreviatura' => 'Bar', 'activa' => true],
            
            // Unidades especiales para inventario
            ['codigo' => 'LOT', 'nombre' => 'Lote', 'abreviatura' => 'Lot', 'activa' => true],
            ['codigo' => 'PAL', 'nombre' => 'Pallet', 'abreviatura' => 'Pal', 'activa' => true],
            ['codigo' => 'CON', 'nombre' => 'Contenedor', 'abreviatura' => 'Con', 'activa' => true],
            ['codigo' => 'JGO', 'nombre' => 'Juego', 'abreviatura' => 'Jgo', 'activa' => true],
            ['codigo' => 'PAR', 'nombre' => 'Par', 'abreviatura' => 'Par', 'activa' => true],
            ['codigo' => 'DOC', 'nombre' => 'Docena', 'abreviatura' => 'Doc', 'activa' => true],
            ['codigo' => 'CEN', 'nombre' => 'Centena', 'abreviatura' => 'Cen', 'activa' => true],
            ['codigo' => 'MIL', 'nombre' => 'Millar', 'abreviatura' => 'Mil', 'activa' => true],
            
            // Unidades de tiempo y servicios
            ['codigo' => 'HR', 'nombre' => 'Hora', 'abreviatura' => 'Hr', 'activa' => true],
            ['codigo' => 'DIA', 'nombre' => 'Día', 'abreviatura' => 'Día', 'activa' => true],
            ['codigo' => 'SEM', 'nombre' => 'Semana', 'abreviatura' => 'Sem', 'activa' => true],
            ['codigo' => 'MES', 'nombre' => 'Mes', 'abreviatura' => 'Mes', 'activa' => true],
            
            // Unidades de energía y potencia
            ['codigo' => 'KWH', 'nombre' => 'Kilovatio-hora', 'abreviatura' => 'kWh', 'activa' => true],
            ['codigo' => 'BTU', 'nombre' => 'BTU', 'abreviatura' => 'BTU', 'activa' => true],
        ];

        foreach ($unidades as $unidad) {
            Unidade::create($unidad);
        }

        $this->command->info('✅ Unidades de medida creadas exitosamente: ' . count($unidades) . ' unidades.');
    }
}
