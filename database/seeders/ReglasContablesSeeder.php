<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ReglasContable;

class ReglasContablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reglas = [
            // === REGLAS BÁSICAS PARA MOVIMIENTOS ===
            [
                'tipo_movimiento' => 'ajuste',
                'categoria_producto_id' => null,
                'producto_id' => null,
                'cuenta_debe_id' => 7,    // Inventario
                'cuenta_haber_id' => 28,  // Capital por Inventario Inicial
                'prioridad' => 1,
                'activa' => true,
                'descripcion' => 'Registro de inventario inicial'
            ],
            [
                'tipo_movimiento' => 'entrada',
                'categoria_producto_id' => null,
                'producto_id' => null,
                'cuenta_debe_id' => 7,    // Inventario
                'cuenta_haber_id' => 23,  // Proveedores
                'prioridad' => 1,
                'activa' => true,
                'descripcion' => 'Compra de productos a crédito'
            ],
            [
                'tipo_movimiento' => 'salida',
                'categoria_producto_id' => null,
                'producto_id' => null,
                'cuenta_debe_id' => 41,   // Costo de Ventas
                'cuenta_haber_id' => 7,   // Inventario
                'prioridad' => 1,
                'activa' => true,
                'descripcion' => 'Venta de productos - costo'
            ],
            [
                'tipo_movimiento' => 'ajuste',
                'categoria_producto_id' => null,
                'producto_id' => null,
                'cuenta_debe_id' => 7,    // Inventario
                'cuenta_haber_id' => 36,  // Ganancia por Ajuste
                'prioridad' => 1,
                'activa' => true,
                'descripcion' => 'Ajuste positivo de inventario'
            ],
            [
                'tipo_movimiento' => 'ajuste',
                'categoria_producto_id' => null,
                'producto_id' => null,
                'cuenta_debe_id' => 46,   // Pérdida por Ajuste
                'cuenta_haber_id' => 7,   // Inventario
                'prioridad' => 1,
                'activa' => true,
                'descripcion' => 'Ajuste negativo de inventario'
            ]
        ];

        foreach ($reglas as $regla) {
            ReglasContable::create($regla);
        }

        $this->command->info('✅ Reglas contables creadas exitosamente: ' . count($reglas) . ' reglas.');
    }
}
