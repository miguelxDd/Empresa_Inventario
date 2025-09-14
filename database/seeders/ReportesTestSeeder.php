<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportesTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear unidades
        $unidades = [
            ['codigo' => 'UND', 'nombre' => 'Unidad', 'abreviatura' => 'Und', 'activa' => true],
            ['codigo' => 'KG', 'nombre' => 'Kilogramo', 'abreviatura' => 'Kg', 'activa' => true],
            ['codigo' => 'LT', 'nombre' => 'Litro', 'abreviatura' => 'Lt', 'activa' => true]
        ];

        foreach ($unidades as $unidad) {
            DB::table('unidades')->insertOrIgnore($unidad);
        }

        // Crear categorías
        $categorias = [
            ['codigo' => 'CAT1', 'nombre' => 'Electrónicos', 'descripcion' => 'Productos electrónicos', 'activa' => true],
            ['codigo' => 'CAT2', 'nombre' => 'Oficina', 'descripcion' => 'Artículos de oficina', 'activa' => true],
            ['codigo' => 'CAT3', 'nombre' => 'Limpieza', 'descripcion' => 'Productos de limpieza', 'activa' => true]
        ];

        foreach ($categorias as $categoria) {
            DB::table('categorias')->insertOrIgnore($categoria);
        }

        // Crear bodegas
        $bodegas = [
            ['codigo' => 'BOD01', 'nombre' => 'Bodega Principal', 'ubicacion' => 'Calle 123', 'activa' => true, 'created_by' => 1],
            ['codigo' => 'BOD02', 'nombre' => 'Bodega Secundaria', 'ubicacion' => 'Avenida 456', 'activa' => true, 'created_by' => 1]
        ];

        foreach ($bodegas as $bodega) {
            DB::table('bodegas')->insertOrIgnore($bodega);
        }

        // Crear productos
        $productos = [
            [
                'sku' => 'PROD001',
                'nombre' => 'Laptop HP',
                'descripcion' => 'Laptop HP Core i5',
                'categoria_id' => 1,
                'unidad_id' => 1,
                'precio_venta' => 2500000,
                'activo' => true,
                'permite_negativo' => false,
                'created_by' => 1
            ],
            [
                'sku' => 'PROD002',
                'nombre' => 'Mouse Óptico',
                'descripcion' => 'Mouse óptico USB',
                'categoria_id' => 1,
                'unidad_id' => 1,
                'precio_venta' => 25000,
                'activo' => true,
                'permite_negativo' => false,
                'created_by' => 1
            ],
            [
                'sku' => 'PROD003',
                'nombre' => 'Papel Bond',
                'descripcion' => 'Resma papel bond carta',
                'categoria_id' => 2,
                'unidad_id' => 1,
                'precio_venta' => 12000,
                'activo' => true,
                'permite_negativo' => false,
                'created_by' => 1
            ]
        ];

        foreach ($productos as $producto) {
            DB::table('productos')->insertOrIgnore($producto);
        }

        // Crear existencias
        $existencias = [
            ['producto_id' => 1, 'bodega_id' => 1, 'cantidad' => 25, 'costo_promedio' => 2000000],
            ['producto_id' => 1, 'bodega_id' => 2, 'cantidad' => 15, 'costo_promedio' => 2000000],
            ['producto_id' => 2, 'bodega_id' => 1, 'cantidad' => 50, 'costo_promedio' => 20000],
            ['producto_id' => 2, 'bodega_id' => 2, 'cantidad' => 30, 'costo_promedio' => 20000],
            ['producto_id' => 3, 'bodega_id' => 1, 'cantidad' => 100, 'costo_promedio' => 10000],
            ['producto_id' => 3, 'bodega_id' => 2, 'cantidad' => 80, 'costo_promedio' => 10000]
        ];

        foreach ($existencias as $existencia) {
            DB::table('existencias')->insertOrIgnore($existencia);
        }

        // Crear movimientos de ejemplo
        $fechaHoy = Carbon::now();
        $movimientos = [
            [
                'fecha' => $fechaHoy->copy()->subDays(10),
                'tipo' => 'entrada',
                'bodega_destino_id' => 1,
                'estado' => 'confirmado',
                'numero_documento' => 'FC001',
                'observaciones' => 'Compra inicial de productos',
                'valor_total' => 5000000,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fecha' => $fechaHoy->copy()->subDays(5),
                'tipo' => 'transferencia',
                'bodega_origen_id' => 1,
                'bodega_destino_id' => 2,
                'estado' => 'confirmado',
                'referencia' => 'TRANS001',
                'observaciones' => 'Transferencia entre bodegas',
                'valor_total' => 1000000,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fecha' => $fechaHoy->copy()->subDays(2),
                'tipo' => 'salida',
                'bodega_origen_id' => 1,
                'estado' => 'confirmado',
                'numero_documento' => 'VT001',
                'observaciones' => 'Venta de productos',
                'valor_total' => 500000,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($movimientos as $movimiento) {
            $movimientoId = DB::table('movimientos')->insertGetId($movimiento);

            // Crear detalles del movimiento
            $detalles = [];
            switch ($movimiento['tipo']) {
                case 'entrada':
                    $detalles = [
                        ['movimiento_id' => $movimientoId, 'producto_id' => 1, 'cantidad' => 10, 'costo_unitario' => 2000000, 'total' => 20000000],
                        ['movimiento_id' => $movimientoId, 'producto_id' => 2, 'cantidad' => 50, 'costo_unitario' => 20000, 'total' => 1000000],
                        ['movimiento_id' => $movimientoId, 'producto_id' => 3, 'cantidad' => 100, 'costo_unitario' => 10000, 'total' => 1000000]
                    ];
                    break;
                case 'transferencia':
                    $detalles = [
                        ['movimiento_id' => $movimientoId, 'producto_id' => 1, 'cantidad' => 5, 'costo_unitario' => 2000000, 'total' => 10000000]
                    ];
                    break;
                case 'salida':
                    $detalles = [
                        ['movimiento_id' => $movimientoId, 'producto_id' => 2, 'cantidad' => 10, 'costo_unitario' => 20000, 'total' => 200000],
                        ['movimiento_id' => $movimientoId, 'producto_id' => 3, 'cantidad' => 20, 'costo_unitario' => 10000, 'total' => 200000]
                    ];
                    break;
            }

            foreach ($detalles as $detalle) {
                $detalle['created_at'] = now();
                $detalle['updated_at'] = now();
                DB::table('movimiento_detalles')->insert($detalle);
            }
        }

        // Crear algunos asientos contables de ejemplo
        $asientos = [
            [
                'fecha' => $fechaHoy->copy()->subDays(10),
                'numero' => 'AS001',
                'descripcion' => 'Asiento por compra de mercancía',
                'origen_tabla' => 'movimientos',
                'origen_id' => 1,
                'estado' => 'confirmado',
                'total_debe' => 5000000,
                'total_haber' => 5000000,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'fecha' => $fechaHoy->copy()->subDays(2),
                'numero' => 'AS002',
                'descripcion' => 'Asiento por venta de mercancía',
                'origen_tabla' => 'movimientos',
                'origen_id' => 3,
                'estado' => 'confirmado',
                'total_debe' => 500000,
                'total_haber' => 500000,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($asientos as $asiento) {
            DB::table('asientos')->insertOrIgnore($asiento);
        }

        echo "\n✅ Datos de prueba creados exitosamente para los reportes\n";
        echo "- 3 Unidades\n";
        echo "- 3 Categorías\n";
        echo "- 2 Bodegas\n";
        echo "- 3 Productos\n";
        echo "- 6 Existencias\n";
        echo "- 3 Movimientos con detalles\n";
        echo "- 2 Asientos contables\n";
    }
}
