<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Cuenta;
use App\Models\ReglasContable;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Bodega;
use App\Models\Existencia;

class ContabilidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando seeder de contabilidad...');
        
        DB::transaction(function () {
            $this->command->info('📊 Creando cuentas contables...');
            $this->seedCuentas();
            
            $this->command->info('📋 Configurando reglas contables...');
            $this->seedReglasContables();
            
            $this->command->info('📦 Creando productos y categorías...');
            $productoIds = $this->seedProductosConCuentas();
            
            $this->command->info('📈 Configurando existencias iniciales...');
            $this->seedExistenciasIniciales($productoIds);
        });
        
        $this->command->info('✅ Seeder de contabilidad completado!');
    }

    private function seedCuentas()
    {
        // Crear cuentas principales (sin padre)
        $cuentasPrincipales = [
            ['codigo' => '1100', 'nombre' => 'ACTIVOS CORRIENTES', 'tipo' => 'activo', 'nivel' => 1, 'activa' => true, 'padre_id' => null],
            ['codigo' => '2100', 'nombre' => 'PASIVOS CORRIENTES', 'tipo' => 'pasivo', 'nivel' => 1, 'activa' => true, 'padre_id' => null],
            ['codigo' => '5100', 'nombre' => 'COSTO DE VENTAS', 'tipo' => 'gasto', 'nivel' => 1, 'activa' => true, 'padre_id' => null],
            ['codigo' => '6100', 'nombre' => 'GASTOS OPERACIONALES', 'tipo' => 'gasto', 'nivel' => 1, 'activa' => true, 'padre_id' => null],
        ];

        foreach ($cuentasPrincipales as $cuenta) {
            $cuentaCreada = Cuenta::firstOrCreate(['codigo' => $cuenta['codigo']], $cuenta);
            if ($cuentaCreada->wasRecentlyCreated) {
                $this->command->info("✓ Creada cuenta principal: {$cuenta['codigo']} - {$cuenta['nombre']}");
            }
        }

        // Obtener IDs para referencias
        $activoId = Cuenta::where('codigo', '1100')->first()->id;
        $pasivoId = Cuenta::where('codigo', '2100')->first()->id;
        $costoId = Cuenta::where('codigo', '5100')->first()->id;
        $gastoId = Cuenta::where('codigo', '6100')->first()->id;

        // Crear cuentas secundarias (con padre)
        $cuentasSecundarias = [
            ['codigo' => '1200', 'nombre' => 'INVENTARIOS', 'tipo' => 'activo', 'nivel' => 2, 'activa' => true, 'padre_id' => $activoId],
            ['codigo' => '2110', 'nombre' => 'Cuentas por Pagar', 'tipo' => 'pasivo', 'nivel' => 2, 'activa' => true, 'padre_id' => $pasivoId],
            ['codigo' => '5110', 'nombre' => 'Costo de Mercaderías', 'tipo' => 'gasto', 'nivel' => 2, 'activa' => true, 'padre_id' => $costoId],
            ['codigo' => '6120', 'nombre' => 'Diferencias en Inventario', 'tipo' => 'gasto', 'nivel' => 2, 'activa' => true, 'padre_id' => $gastoId],
        ];

        foreach ($cuentasSecundarias as $cuenta) {
            $cuentaCreada = Cuenta::firstOrCreate(['codigo' => $cuenta['codigo']], $cuenta);
            if ($cuentaCreada->wasRecentlyCreated) {
                $this->command->info("✓ Creada cuenta secundaria: {$cuenta['codigo']} - {$cuenta['nombre']}");
            }
        }

        // Crear cuenta final de inventario
        $inventarioId = Cuenta::where('codigo', '1200')->first()->id;
        $cuentaInventario = [
            'codigo' => '1210', 
            'nombre' => 'Inventario de Mercaderías', 
            'tipo' => 'activo', 
            'nivel' => 3, 
            'activa' => true, 
            'padre_id' => $inventarioId
        ];

        $cuenta = Cuenta::firstOrCreate(['codigo' => $cuentaInventario['codigo']], $cuentaInventario);
        if ($cuenta->wasRecentlyCreated) {
            $this->command->info("✓ Creada cuenta de inventario: {$cuentaInventario['codigo']} - {$cuentaInventario['nombre']}");
        }
    }

    private function seedReglasContables()
    {
        // Verificar si la tabla existe
        if (!DB::getSchemaBuilder()->hasTable('reglas_contables')) {
            $this->command->info('Tabla reglas_contables no existe, saltando seeders de reglas...');
            return;
        }

        // Obtener IDs de cuentas necesarias
        $inventario = Cuenta::where('codigo', '1210')->first();
        $cuentasPorPagar = Cuenta::where('codigo', '2110')->first();
        $costoMercaderias = Cuenta::where('codigo', '5110')->first();
        $diferenciasInventario = Cuenta::where('codigo', '6120')->first();

        if (!$inventario || !$cuentasPorPagar || !$costoMercaderias || !$diferenciasInventario) {
            $this->command->error('No se encontraron todas las cuentas necesarias para las reglas');
            return;
        }

        $reglas = [
            // Reglas GENERALES para ENTRADA (compras)
            [
                'tipo_movimiento' => 'entrada',
                'categoria_producto_id' => null,
                'producto_id' => null,
                'cuenta_debe_id' => $inventario->id,     // DEBE: Inventario (incrementa activo)
                'cuenta_haber_id' => $cuentasPorPagar->id, // HABER: Cuentas por Pagar (incrementa pasivo)
                'prioridad' => 1,
                'activa' => true,
                'descripcion' => 'Regla general para entradas de inventario por compra'
            ],
            
            // Reglas GENERALES para SALIDA (ventas)
            [
                'tipo_movimiento' => 'salida',
                'categoria_producto_id' => null,
                'producto_id' => null,
                'cuenta_debe_id' => $costoMercaderias->id,  // DEBE: Costo de Mercaderías (incrementa gasto)
                'cuenta_haber_id' => $inventario->id,       // HABER: Inventario (disminuye activo)
                'prioridad' => 1,
                'activa' => true,
                'descripcion' => 'Regla general para salidas de inventario por venta'
            ],
            
            // Reglas GENERALES para AJUSTES
            [
                'tipo_movimiento' => 'ajuste',
                'categoria_producto_id' => null,
                'producto_id' => null,
                'cuenta_debe_id' => $diferenciasInventario->id, // DEBE: Diferencias en Inventario (gasto si ajuste negativo)
                'cuenta_haber_id' => $inventario->id,           // HABER: Inventario (ajuste)
                'prioridad' => 1,
                'activa' => true,
                'descripcion' => 'Regla general para ajustes de inventario'
            ],
        ];

        foreach ($reglas as $regla) {
            try {
                $reglaCreada = ReglasContable::firstOrCreate(
                    [
                        'tipo_movimiento' => $regla['tipo_movimiento'],
                        'categoria_producto_id' => $regla['categoria_producto_id'],
                        'producto_id' => $regla['producto_id']
                    ],
                    $regla
                );
                
                if ($reglaCreada->wasRecentlyCreated) {
                    $this->command->info("✓ Creada regla para: {$regla['tipo_movimiento']} - {$regla['descripcion']}");
                }
            } catch (\Exception $e) {
                $this->command->error("✗ Error creando regla {$regla['tipo_movimiento']}: " . $e->getMessage());
            }
        }
    }

    private function seedProductosConCuentas()
    {
        // Obtener o crear unidades con IDs válidos
        $unidadUnd = DB::table('unidades')->where('codigo', 'UND')->first();
        $unidadPaq = DB::table('unidades')->where('codigo', 'PAQ')->first();
        $unidadLt = DB::table('unidades')->where('codigo', 'LT')->first();
        
        if (!$unidadUnd) {
            $unidadId1 = DB::table('unidades')->insertGetId([
                'codigo' => 'UND',
                'nombre' => 'Unidad',
                'abreviatura' => 'und',
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $this->command->info("✓ Creada unidad: UND - Unidad (ID: {$unidadId1})");
        } else {
            $unidadId1 = $unidadUnd->id;
        }
        
        if (!$unidadPaq) {
            $unidadId2 = DB::table('unidades')->insertGetId([
                'codigo' => 'PAQ',
                'nombre' => 'Paquete',
                'abreviatura' => 'paq',
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $this->command->info("✓ Creada unidad: PAQ - Paquete (ID: {$unidadId2})");
        } else {
            $unidadId2 = $unidadPaq->id;
        }
        
        if (!$unidadLt) {
            $unidadId3 = DB::table('unidades')->insertGetId([
                'codigo' => 'LT',
                'nombre' => 'Litro',
                'abreviatura' => 'lt',
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $this->command->info("✓ Creada unidad: LT - Litro (ID: {$unidadId3})");
        } else {
            $unidadId3 = $unidadLt->id;
        }

        // Crear categorías si no existen y obtener sus IDs
        $categorias = [
            ['codigo' => 'TECH', 'nombre' => 'Tecnología', 'descripcion' => 'Productos tecnológicos', 'activa' => true],
            ['codigo' => 'OFIC', 'nombre' => 'Oficina', 'descripcion' => 'Artículos de oficina', 'activa' => true],
            ['codigo' => 'LIMP', 'nombre' => 'Limpieza', 'descripcion' => 'Productos de limpieza', 'activa' => true],
        ];

        $categoriaIds = [];
        foreach ($categorias as $cat) {
            try {
                $categoria = Categoria::firstOrCreate(['codigo' => $cat['codigo']], $cat);
                $categoriaIds[$cat['codigo']] = $categoria->id;
                if ($categoria->wasRecentlyCreated) {
                    $this->command->info("✓ Creada categoría: {$cat['codigo']} - {$cat['nombre']} (ID: {$categoria->id})");
                }
            } catch (\Exception $e) {
                $this->command->error("✗ Error creando categoría {$cat['codigo']}: " . $e->getMessage());
            }
        }

                // Crear productos de ejemplo usando IDs dinámicos
        $productos = [
            ['sku' => 'TECH-001', 'nombre' => 'Laptop Dell XPS 13', 'descripcion' => 'Laptop empresarial de alto rendimiento', 'categoria_id' => $categoriaIds['TECH'], 'unidad_id' => $unidadId1, 'precio_venta' => 1200.00, 'precio_compra_promedio' => 1000.00, 'activo' => true, 'created_by' => 1, 'updated_by' => 1],
            ['sku' => 'TECH-002', 'nombre' => 'Mouse Logitech', 'descripcion' => 'Mouse óptico inalámbrico', 'categoria_id' => $categoriaIds['TECH'], 'unidad_id' => $unidadId1, 'precio_venta' => 25.50, 'precio_compra_promedio' => 20.00, 'activo' => true, 'created_by' => 1, 'updated_by' => 1],
            ['sku' => 'OF-001', 'nombre' => 'Resma Papel A4', 'descripcion' => 'Papel bond 75g 500 hojas', 'categoria_id' => $categoriaIds['OFIC'], 'unidad_id' => $unidadId2, 'precio_venta' => 5.75, 'precio_compra_promedio' => 4.50, 'activo' => true, 'created_by' => 1, 'updated_by' => 1],
            ['sku' => 'OF-002', 'nombre' => 'Bolígrafos Bic x12', 'descripcion' => 'Caja de 12 bolígrafos azules', 'categoria_id' => $categoriaIds['OFIC'], 'unidad_id' => $unidadId2, 'precio_venta' => 3.20, 'precio_compra_promedio' => 2.50, 'activo' => true, 'created_by' => 1, 'updated_by' => 1],
            ['sku' => 'LIM-001', 'nombre' => 'Detergente Líquido 1L', 'descripcion' => 'Detergente concentrado multiusos', 'categoria_id' => $categoriaIds['LIMP'], 'unidad_id' => $unidadId3, 'precio_venta' => 4.50, 'precio_compra_promedio' => 3.50, 'activo' => true, 'created_by' => 1, 'updated_by' => 1],
            ['sku' => 'LIM-002', 'nombre' => 'Papel Higiénico x4', 'descripcion' => 'Paquete de 4 rollos doble hoja', 'categoria_id' => $categoriaIds['LIMP'], 'unidad_id' => $unidadId2, 'precio_venta' => 6.80, 'precio_compra_promedio' => 5.20, 'activo' => true, 'created_by' => 1, 'updated_by' => 1],
        ];

        $productoIds = [];
        foreach ($productos as $prod) {
            try {
                $producto = Producto::firstOrCreate(['sku' => $prod['sku']], $prod);
                $productoIds[] = $producto->id;
                if ($producto->wasRecentlyCreated) {
                    $this->command->info("✓ Creado producto: {$prod['sku']} - {$prod['nombre']} (ID: {$producto->id})");
                }
            } catch (\Exception $e) {
                $this->command->error("✗ Error creando producto {$prod['sku']}: " . $e->getMessage());
            }
        }
        
        return $productoIds;
    }

    private function seedExistenciasIniciales($productoIds = [])
    {
        // Crear bodega principal si no existe
        $bodega = Bodega::firstOrCreate(
            ['codigo' => 'PRIN'],
            ['nombre' => 'Bodega Principal', 'ubicacion' => 'Planta 1 - Sector A', 'activa' => true, 'created_by' => 1, 'updated_by' => 1]
        );

        // Existencias iniciales para testing usando IDs dinámicos de productos
        if (count($productoIds) > 0) {
            $existencias = [
                ['producto_id' => $productoIds[0], 'bodega_id' => $bodega->id, 'cantidad' => 10, 'costo_promedio' => 1150.00, 'stock_minimo' => 5, 'stock_maximo' => 50],
                ['producto_id' => $productoIds[1], 'bodega_id' => $bodega->id, 'cantidad' => 50, 'costo_promedio' => 24.00, 'stock_minimo' => 20, 'stock_maximo' => 200],
                ['producto_id' => $productoIds[2], 'bodega_id' => $bodega->id, 'cantidad' => 100, 'costo_promedio' => 5.50, 'stock_minimo' => 50, 'stock_maximo' => 500],
                ['producto_id' => $productoIds[3], 'bodega_id' => $bodega->id, 'cantidad' => 200, 'costo_promedio' => 3.00, 'stock_minimo' => 100, 'stock_maximo' => 1000],
                ['producto_id' => $productoIds[4], 'bodega_id' => $bodega->id, 'cantidad' => 75, 'costo_promedio' => 4.25, 'stock_minimo' => 25, 'stock_maximo' => 300],
                ['producto_id' => $productoIds[5], 'bodega_id' => $bodega->id, 'cantidad' => 150, 'costo_promedio' => 6.50, 'stock_minimo' => 50, 'stock_maximo' => 500],
            ];

            foreach ($existencias as $existencia) {
                try {
                    Existencia::updateOrCreate(
                        ['producto_id' => $existencia['producto_id'], 'bodega_id' => $existencia['bodega_id']],
                        $existencia
                    );
                    $this->command->info("✓ Creada existencia para producto ID {$existencia['producto_id']} en bodega {$bodega->nombre}");
                } catch (\Exception $e) {
                    $this->command->error("✗ Error creando existencia: " . $e->getMessage());
                }
            }
        } else {
            $this->command->warn("No se pudieron crear existencias porque no hay productos válidos");
        }
    }
}
