<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cuenta;

class CuentasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Plan de cuentas completo para inventario
        $cuentas = [
            // === ACTIVOS ===
            // Nivel 1 - Activos
            ['codigo' => '1', 'nombre' => 'ACTIVOS', 'tipo' => 'activo', 'nivel' => 1, 'padre_id' => null, 'activa' => true],
            
            // Nivel 2 - Activos Corrientes
            ['codigo' => '11', 'nombre' => 'ACTIVOS CORRIENTES', 'tipo' => 'activo', 'nivel' => 2, 'padre_id' => 1, 'activa' => true],
            ['codigo' => '12', 'nombre' => 'ACTIVOS NO CORRIENTES', 'tipo' => 'activo', 'nivel' => 2, 'padre_id' => 1, 'activa' => true],
            
            // Nivel 3 - Subcuentas de Activos Corrientes
            ['codigo' => '1101', 'nombre' => 'INVENTARIOS', 'tipo' => 'activo', 'nivel' => 3, 'padre_id' => 2, 'activa' => true],
            ['codigo' => '1102', 'nombre' => 'BANCOS Y CUENTAS POR COBRAR', 'tipo' => 'activo', 'nivel' => 3, 'padre_id' => 2, 'activa' => true],
            ['codigo' => '1103', 'nombre' => 'CUENTAS POR COBRAR COMERCIALES', 'tipo' => 'activo', 'nivel' => 3, 'padre_id' => 2, 'activa' => true],
            
            // Nivel 4 - Cuentas detalle de Inventarios
            ['codigo' => '110101', 'nombre' => 'Inventario de Productos Terminados', 'tipo' => 'activo', 'nivel' => 4, 'padre_id' => 4, 'activa' => true],
            ['codigo' => '110102', 'nombre' => 'Inventario de Materias Primas', 'tipo' => 'activo', 'nivel' => 4, 'padre_id' => 4, 'activa' => true],
            ['codigo' => '110103', 'nombre' => 'Inventario en Tránsito', 'tipo' => 'activo', 'nivel' => 4, 'padre_id' => 4, 'activa' => true],
            ['codigo' => '110104', 'nombre' => 'Inventario de Repuestos', 'tipo' => 'activo', 'nivel' => 4, 'padre_id' => 4, 'activa' => true],
            
            // Nivel 4 - Cuentas detalle de Bancos
            ['codigo' => '110201', 'nombre' => 'Bancos Cuenta Corriente', 'tipo' => 'activo', 'nivel' => 4, 'padre_id' => 5, 'activa' => true],
            ['codigo' => '110202', 'nombre' => 'Caja Chica', 'tipo' => 'activo', 'nivel' => 4, 'padre_id' => 5, 'activa' => true],
            ['codigo' => '110203', 'nombre' => 'Bancos Cuenta de Ahorros', 'tipo' => 'activo', 'nivel' => 4, 'padre_id' => 5, 'activa' => true],
            
            // Activos No Corrientes
            ['codigo' => '1201', 'nombre' => 'PROPIEDAD PLANTA Y EQUIPO', 'tipo' => 'activo', 'nivel' => 3, 'padre_id' => 3, 'activa' => true],
            ['codigo' => '120101', 'nombre' => 'Maquinaria y Equipo', 'tipo' => 'activo', 'nivel' => 4, 'padre_id' => 14, 'activa' => true],
            ['codigo' => '120102', 'nombre' => 'Equipo de Computación', 'tipo' => 'activo', 'nivel' => 4, 'padre_id' => 14, 'activa' => true],
            
            // === PASIVOS ===
            // Nivel 1 - Pasivos
            ['codigo' => '2', 'nombre' => 'PASIVOS', 'tipo' => 'pasivo', 'nivel' => 1, 'padre_id' => null, 'activa' => true],
            
            // Nivel 2 - Pasivos Corrientes
            ['codigo' => '21', 'nombre' => 'PASIVOS CORRIENTES', 'tipo' => 'pasivo', 'nivel' => 2, 'padre_id' => 17, 'activa' => true],
            ['codigo' => '22', 'nombre' => 'PASIVOS NO CORRIENTES', 'tipo' => 'pasivo', 'nivel' => 2, 'padre_id' => 17, 'activa' => true],
            
            // Nivel 3 - Subcuentas de Pasivos
            ['codigo' => '2101', 'nombre' => 'CUENTAS POR PAGAR COMERCIALES', 'tipo' => 'pasivo', 'nivel' => 3, 'padre_id' => 18, 'activa' => true],
            ['codigo' => '2102', 'nombre' => 'OBLIGACIONES LABORALES', 'tipo' => 'pasivo', 'nivel' => 3, 'padre_id' => 18, 'activa' => true],
            ['codigo' => '2103', 'nombre' => 'OBLIGACIONES FISCALES', 'tipo' => 'pasivo', 'nivel' => 3, 'padre_id' => 18, 'activa' => true],
            
            // Nivel 4 - Cuentas detalle
            ['codigo' => '210101', 'nombre' => 'Proveedores Nacionales', 'tipo' => 'pasivo', 'nivel' => 4, 'padre_id' => 20, 'activa' => true],
            ['codigo' => '210102', 'nombre' => 'Proveedores Internacionales', 'tipo' => 'pasivo', 'nivel' => 4, 'padre_id' => 20, 'activa' => true],
            ['codigo' => '210103', 'nombre' => 'Acreedores Varios', 'tipo' => 'pasivo', 'nivel' => 4, 'padre_id' => 20, 'activa' => true],
            
            // === PATRIMONIO ===
            // Nivel 1 - Patrimonio
            ['codigo' => '3', 'nombre' => 'PATRIMONIO', 'tipo' => 'patrimonio', 'nivel' => 1, 'padre_id' => null, 'activa' => true],
            
            // Nivel 2 - Subcuentas de Patrimonio
            ['codigo' => '31', 'nombre' => 'CAPITAL SOCIAL', 'tipo' => 'patrimonio', 'nivel' => 2, 'padre_id' => 25, 'activa' => true],
            ['codigo' => '32', 'nombre' => 'RESULTADOS', 'tipo' => 'patrimonio', 'nivel' => 2, 'padre_id' => 25, 'activa' => true],
            
            // Nivel 3 - Cuentas de Capital
            ['codigo' => '3101', 'nombre' => 'Capital por Inventario Inicial', 'tipo' => 'patrimonio', 'nivel' => 3, 'padre_id' => 26, 'activa' => true],
            ['codigo' => '3102', 'nombre' => 'Capital Suscrito y Pagado', 'tipo' => 'patrimonio', 'nivel' => 3, 'padre_id' => 26, 'activa' => true],
            ['codigo' => '3201', 'nombre' => 'Utilidades Retenidas', 'tipo' => 'patrimonio', 'nivel' => 3, 'padre_id' => 27, 'activa' => true],
            ['codigo' => '3202', 'nombre' => 'Resultado del Ejercicio', 'tipo' => 'patrimonio', 'nivel' => 3, 'padre_id' => 27, 'activa' => true],
            
            // === INGRESOS ===
            // Nivel 1 - Ingresos
            ['codigo' => '4', 'nombre' => 'INGRESOS', 'tipo' => 'ingreso', 'nivel' => 1, 'padre_id' => null, 'activa' => true],
            
            // Nivel 2 - Ingresos Operacionales
            ['codigo' => '41', 'nombre' => 'INGRESOS OPERACIONALES', 'tipo' => 'ingreso', 'nivel' => 2, 'padre_id' => 31, 'activa' => true],
            ['codigo' => '49', 'nombre' => 'OTROS INGRESOS', 'tipo' => 'ingreso', 'nivel' => 2, 'padre_id' => 31, 'activa' => true],
            
            // Nivel 3 - Subcuentas de Ingresos
            ['codigo' => '4101', 'nombre' => 'Ventas de Productos', 'tipo' => 'ingreso', 'nivel' => 3, 'padre_id' => 32, 'activa' => true],
            ['codigo' => '4102', 'nombre' => 'Prestación de Servicios', 'tipo' => 'ingreso', 'nivel' => 3, 'padre_id' => 32, 'activa' => true],
            ['codigo' => '4901', 'nombre' => 'Ganancia por Ajuste de Inventario', 'tipo' => 'ingreso', 'nivel' => 3, 'padre_id' => 33, 'activa' => true],
            ['codigo' => '4902', 'nombre' => 'Ingresos Extraordinarios', 'tipo' => 'ingreso', 'nivel' => 3, 'padre_id' => 33, 'activa' => true],
            
            // === GASTOS ===
            // Nivel 1 - Gastos
            ['codigo' => '5', 'nombre' => 'COSTOS', 'tipo' => 'gasto', 'nivel' => 1, 'padre_id' => null, 'activa' => true],
            ['codigo' => '6', 'nombre' => 'GASTOS', 'tipo' => 'gasto', 'nivel' => 1, 'padre_id' => null, 'activa' => true],
            
            // Nivel 2 - Costos
            ['codigo' => '51', 'nombre' => 'COSTO DE VENTAS', 'tipo' => 'gasto', 'nivel' => 2, 'padre_id' => 37, 'activa' => true],
            ['codigo' => '52', 'nombre' => 'COSTOS DE PRODUCCIÓN', 'tipo' => 'gasto', 'nivel' => 2, 'padre_id' => 37, 'activa' => true],
            
            // Nivel 3 - Cuentas de Costos
            ['codigo' => '5101', 'nombre' => 'Costo de Ventas - Productos Terminados', 'tipo' => 'gasto', 'nivel' => 3, 'padre_id' => 39, 'activa' => true],
            ['codigo' => '5102', 'nombre' => 'Costo de Ventas - Servicios', 'tipo' => 'gasto', 'nivel' => 3, 'padre_id' => 39, 'activa' => true],
            ['codigo' => '5201', 'nombre' => 'Materia Prima Directa', 'tipo' => 'gasto', 'nivel' => 3, 'padre_id' => 40, 'activa' => true],
            ['codigo' => '5202', 'nombre' => 'Mano de Obra Directa', 'tipo' => 'gasto', 'nivel' => 3, 'padre_id' => 40, 'activa' => true],
            
            // Nivel 2 - Gastos Operacionales
            ['codigo' => '61', 'nombre' => 'GASTOS OPERACIONALES', 'tipo' => 'gasto', 'nivel' => 2, 'padre_id' => 38, 'activa' => true],
            ['codigo' => '62', 'nombre' => 'OTROS GASTOS', 'tipo' => 'gasto', 'nivel' => 2, 'padre_id' => 38, 'activa' => true],
            
            // Nivel 3 - Gastos Operacionales
            ['codigo' => '6101', 'nombre' => 'Pérdida por Ajuste de Inventario', 'tipo' => 'gasto', 'nivel' => 3, 'padre_id' => 45, 'activa' => true],
            ['codigo' => '6102', 'nombre' => 'Gastos de Administración', 'tipo' => 'gasto', 'nivel' => 3, 'padre_id' => 45, 'activa' => true],
            ['codigo' => '6103', 'nombre' => 'Gastos de Ventas', 'tipo' => 'gasto', 'nivel' => 3, 'padre_id' => 45, 'activa' => true],
            ['codigo' => '6201', 'nombre' => 'Gastos Financieros', 'tipo' => 'gasto', 'nivel' => 3, 'padre_id' => 46, 'activa' => true],
            ['codigo' => '6202', 'nombre' => 'Gastos Extraordinarios', 'tipo' => 'gasto', 'nivel' => 3, 'padre_id' => 46, 'activa' => true],
        ];

        foreach ($cuentas as $index => $cuenta) {
            // Ajustar padre_id basado en el ID real insertado
            if ($cuenta['padre_id'] !== null) {
                $cuenta['padre_id'] = $cuenta['padre_id'];
            }
            
            Cuenta::create($cuenta);
        }

        $this->command->info('✅ Plan de cuentas creado exitosamente con ' . count($cuentas) . ' cuentas.');
    }
}
