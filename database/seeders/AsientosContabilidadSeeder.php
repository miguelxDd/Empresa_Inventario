<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asiento;
use App\Models\AsientosDetalle;
use App\Models\Cuenta;
use Carbon\Carbon;

class AsientosContabilidadSeeder extends Seeder
{
    public function run()
    {
        // Obtener algunas cuentas para los asientos
        $cuentas = Cuenta::where('activa', true)->get()->keyBy('codigo');
        
        // Si no hay suficientes cuentas, crear algunas básicas
        if ($cuentas->count() < 10) {
            $this->crearCuentasBasicas();
            $cuentas = Cuenta::where('activa', true)->get()->keyBy('codigo');
        }

        $fechaInicio = Carbon::now()->startOfMonth();
        $fechaFin = Carbon::now();

        // Crear asientos de ejemplo
        $this->crearAsientoCompra($cuentas, $fechaInicio->copy()->addDays(1));
        $this->crearAsientoVenta($cuentas, $fechaInicio->copy()->addDays(2));
        $this->crearAsientoGasto($cuentas, $fechaInicio->copy()->addDays(3));
        $this->crearAsientoPago($cuentas, $fechaInicio->copy()->addDays(5));
        $this->crearAsientoDepreciacion($cuentas, $fechaInicio->copy()->addDays(10));
        $this->crearAsientoNomina($cuentas, $fechaInicio->copy()->addDays(15));
        $this->crearAsientoIngreso($cuentas, $fechaInicio->copy()->addDays(20));
        $this->crearAsientoAjuste($cuentas, $fechaInicio->copy()->addDays(25));
    }

    private function crearCuentasBasicas()
    {
        $cuentasBasicas = [
            ['codigo' => '1105', 'nombre' => 'Caja', 'tipo' => 'activo', 'nivel' => 2],
            ['codigo' => '1110', 'nombre' => 'Bancos', 'tipo' => 'activo', 'nivel' => 2],
            ['codigo' => '1205', 'nombre' => 'Clientes', 'tipo' => 'activo', 'nivel' => 2],
            ['codigo' => '1435', 'nombre' => 'Inventario Mercaderías', 'tipo' => 'activo', 'nivel' => 2],
            ['codigo' => '1705', 'nombre' => 'Maquinaria y Equipo', 'tipo' => 'activo', 'nivel' => 2],
            ['codigo' => '2105', 'nombre' => 'Proveedores', 'tipo' => 'pasivo', 'nivel' => 2],
            ['codigo' => '2505', 'nombre' => 'Salarios por Pagar', 'tipo' => 'pasivo', 'nivel' => 2],
            ['codigo' => '3105', 'nombre' => 'Capital Social', 'tipo' => 'patrimonio', 'nivel' => 2],
            ['codigo' => '4105', 'nombre' => 'Ventas', 'tipo' => 'ingreso', 'nivel' => 2],
            ['codigo' => '5105', 'nombre' => 'Costo de Ventas', 'tipo' => 'gasto', 'nivel' => 2],
            ['codigo' => '5205', 'nombre' => 'Gastos de Administración', 'tipo' => 'gasto', 'nivel' => 2],
            ['codigo' => '5305', 'nombre' => 'Gastos de Ventas', 'tipo' => 'gasto', 'nivel' => 2],
        ];

        foreach ($cuentasBasicas as $cuenta) {
            Cuenta::firstOrCreate(
                ['codigo' => $cuenta['codigo']],
                $cuenta + ['activa' => true]
            );
        }
    }

    private function crearAsientoCompra($cuentas, $fecha)
    {
        $asiento = Asiento::create([
            'fecha' => $fecha,
            'numero' => 'ASI-001',
            'descripcion' => 'Compra de mercaderías a crédito',
            'estado' => 'confirmado',
            'total_debe' => 5000000,
            'total_haber' => 5000000,
            'created_by' => 1,
        ]);

        // Debe: Inventario
        AsientosDetalle::create([
            'asiento_id' => $asiento->id,
            'cuenta_id' => $cuentas->get('1435')->id ?? $cuentas->first()->id,
            'debe' => 5000000,
            'haber' => 0,
            'concepto' => 'Compra mercaderías según factura #1001',
        ]);

        // Haber: Proveedores
        AsientosDetalle::create([
            'asiento_id' => $asiento->id,
            'cuenta_id' => $cuentas->get('2105')->id ?? $cuentas->skip(1)->first()->id,
            'debe' => 0,
            'haber' => 5000000,
            'concepto' => 'Deuda con proveedor ABC S.A.',
        ]);
    }

    private function crearAsientoVenta($cuentas, $fecha)
    {
        $asiento = Asiento::create([
            'fecha' => $fecha,
            'numero' => 'ASI-002',
            'descripcion' => 'Venta de mercaderías al contado',
            'estado' => 'confirmado',
            'total_debe' => 3000000,
            'total_haber' => 3000000,
            'created_by' => 1,
        ]);

        // Debe: Caja
        AsientosDetalle::create([
            'asiento_id' => $asiento->id,
            'cuenta_id' => $cuentas->get('1105')->id ?? $cuentas->first()->id,
            'debe' => 3000000,
            'haber' => 0,
            'concepto' => 'Venta al contado según factura #2001',
        ]);

        // Haber: Ventas
        AsientosDetalle::create([
            'asiento_id' => $asiento->id,
            'cuenta_id' => $cuentas->get('4105')->id ?? $cuentas->skip(8)->first()->id,
            'debe' => 0,
            'haber' => 3000000,
            'concepto' => 'Ingresos por ventas',
        ]);
    }

    private function crearAsientoGasto($cuentas, $fecha)
    {
        $asiento = Asiento::create([
            'fecha' => $fecha,
            'numero' => 'ASI-003',
            'descripcion' => 'Pago gastos de administración',
            'estado' => 'confirmado',
            'total_debe' => 800000,
            'total_haber' => 800000,
            'created_by' => 1,
        ]);

        // Debe: Gastos de Administración
        AsientosDetalle::create([
            'asiento_id' => $asiento->id,
            'cuenta_id' => $cuentas->get('5205')->id ?? $cuentas->skip(10)->first()->id,
            'debe' => 800000,
            'haber' => 0,
            'concepto' => 'Servicios públicos oficina',
        ]);

        // Haber: Bancos
        AsientosDetalle::create([
            'asiento_id' => $asiento->id,
            'cuenta_id' => $cuentas->get('1110')->id ?? $cuentas->skip(1)->first()->id,
            'debe' => 0,
            'haber' => 800000,
            'concepto' => 'Pago cheque #001234',
        ]);
    }

    private function crearAsientoPago($cuentas, $fecha)
    {
        $asiento = Asiento::create([
            'fecha' => $fecha,
            'numero' => 'ASI-004',
            'descripcion' => 'Pago a proveedor',
            'estado' => 'confirmado',
            'total_debe' => 2000000,
            'total_haber' => 2000000,
            'created_by' => 1,
        ]);

        // Debe: Proveedores
        AsientosDetalle::create([
            'asiento_id' => $asiento->id,
            'cuenta_id' => $cuentas->get('2105')->id ?? $cuentas->skip(5)->first()->id,
            'debe' => 2000000,
            'haber' => 0,
            'concepto' => 'Abono a cuenta proveedor ABC S.A.',
        ]);

        // Haber: Bancos
        AsientosDetalle::create([
            'asiento_id' => $asiento->id,
            'cuenta_id' => $cuentas->get('1110')->id ?? $cuentas->skip(1)->first()->id,
            'debe' => 0,
            'haber' => 2000000,
            'concepto' => 'Transferencia bancaria',
        ]);
    }

    private function crearAsientoDepreciacion($cuentas, $fecha)
    {
        // Crear cuenta de depreciación si no existe
        $cuentaDepreciacion = Cuenta::firstOrCreate(
            ['codigo' => '1715'],
            [
                'nombre' => 'Depreciación Acumulada Maquinaria',
                'tipo' => 'activo',
                'nivel' => 2,
                'activa' => true
            ]
        );

        $cuentaGastoDepreciacion = Cuenta::firstOrCreate(
            ['codigo' => '5405'],
            [
                'nombre' => 'Gasto Depreciación',
                'tipo' => 'gasto',
                'nivel' => 2,
                'activa' => true
            ]
        );

        $asiento = Asiento::create([
            'fecha' => $fecha,
            'numero' => 'ASI-005',
            'descripcion' => 'Depreciación mensual maquinaria',
            'estado' => 'confirmado',
            'total_debe' => 500000,
            'total_haber' => 500000,
            'created_by' => 1,
        ]);

        // Debe: Gasto Depreciación
        AsientosDetalle::create([
            'asiento_id' => $asiento->id,
            'cuenta_id' => $cuentaGastoDepreciacion->id,
            'debe' => 500000,
            'haber' => 0,
            'concepto' => 'Depreciación del mes',
        ]);

        // Haber: Depreciación Acumulada
        AsientosDetalle::create([
            'asiento_id' => $asiento->id,
            'cuenta_id' => $cuentaDepreciacion->id,
            'debe' => 0,
            'haber' => 500000,
            'concepto' => 'Acumulación depreciación',
        ]);
    }

    private function crearAsientoNomina($cuentas, $fecha)
    {
        $asiento = Asiento::create([
            'fecha' => $fecha,
            'numero' => 'ASI-006',
            'descripcion' => 'Provision nómina quincenal',
            'estado' => 'confirmado',
            'total_debe' => 1500000,
            'total_haber' => 1500000,
            'created_by' => 1,
        ]);

        // Debe: Gastos de Administración
        AsientosDetalle::create([
            'asiento_id' => $asiento->id,
            'cuenta_id' => $cuentas->get('5205')->id ?? $cuentas->skip(10)->first()->id,
            'debe' => 1500000,
            'haber' => 0,
            'concepto' => 'Sueldos personal administrativo',
        ]);

        // Haber: Salarios por Pagar
        AsientosDetalle::create([
            'asiento_id' => $asiento->id,
            'cuenta_id' => $cuentas->get('2505')->id ?? $cuentas->skip(6)->first()->id,
            'debe' => 0,
            'haber' => 1500000,
            'concepto' => 'Provision quincenal',
        ]);
    }

    private function crearAsientoIngreso($cuentas, $fecha)
    {
        $asiento = Asiento::create([
            'fecha' => $fecha,
            'numero' => 'ASI-007',
            'descripcion' => 'Venta a crédito',
            'estado' => 'confirmado',
            'total_debe' => 4500000,
            'total_haber' => 4500000,
            'created_by' => 1,
        ]);

        // Debe: Clientes
        AsientosDetalle::create([
            'asiento_id' => $asiento->id,
            'cuenta_id' => $cuentas->get('1205')->id ?? $cuentas->skip(2)->first()->id,
            'debe' => 4500000,
            'haber' => 0,
            'concepto' => 'Venta a crédito Cliente XYZ',
        ]);

        // Haber: Ventas
        AsientosDetalle::create([
            'asiento_id' => $asiento->id,
            'cuenta_id' => $cuentas->get('4105')->id ?? $cuentas->skip(8)->first()->id,
            'debe' => 0,
            'haber' => 4500000,
            'concepto' => 'Ingresos por ventas',
        ]);
    }

    private function crearAsientoAjuste($cuentas, $fecha)
    {
        $asiento = Asiento::create([
            'fecha' => $fecha,
            'numero' => 'ASI-008',
            'descripcion' => 'Ajuste inventario físico',
            'origen_tabla' => 'movimientos',
            'origen_id' => 1,
            'estado' => 'confirmado',
            'total_debe' => 300000,
            'total_haber' => 300000,
            'created_by' => 1,
        ]);

        // Debe: Costo de Ventas
        AsientosDetalle::create([
            'asiento_id' => $asiento->id,
            'cuenta_id' => $cuentas->get('5105')->id ?? $cuentas->skip(9)->first()->id,
            'debe' => 300000,
            'haber' => 0,
            'concepto' => 'Ajuste por faltante inventario',
        ]);

        // Haber: Inventario
        AsientosDetalle::create([
            'asiento_id' => $asiento->id,
            'cuenta_id' => $cuentas->get('1435')->id ?? $cuentas->skip(3)->first()->id,
            'debe' => 0,
            'haber' => 300000,
            'concepto' => 'Salida por ajuste físico',
        ]);
    }
}
