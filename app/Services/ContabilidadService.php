<?php

namespace App\Services;

use App\Models\Asiento;
use App\Models\AsientosDetalle;
use App\Models\Cuenta;
use App\Models\Existencia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Servicio para reportes contables
 * Libro Mayor, Balance General y Conciliación de Inventarios
 */
class ContabilidadService
{
    /**
     * Generar Libro Mayor por rango de fechas
     * 
     * @param string $fechaDesde YYYY-MM-DD
     * @param string $fechaHasta YYYY-MM-DD
     * @param array $cuentasIds Array de IDs de cuentas (opcional)
     * @return array
     */
    public function libroMayor(string $fechaDesde, string $fechaHasta, array $cuentasIds = []): array
    {
        $query = DB::table('asientos as a')
            ->join('asientos_detalle as d', 'd.asiento_id', '=', 'a.id')
            ->join('cuentas as c', 'c.id', '=', 'd.cuenta_id')
            ->where('a.estado', 'confirmado')
            ->whereBetween('a.fecha', [$fechaDesde, $fechaHasta]);

        if (!empty($cuentasIds)) {
            $query->whereIn('c.id', $cuentasIds);
        }

        $movimientos = $query->select([
                'c.id as cuenta_id',
                'c.codigo',
                'c.nombre as cuenta_nombre',
                'c.tipo as cuenta_tipo',
                'a.fecha',
                'a.numero as asiento_numero',
                'a.descripcion',
                'd.debe',
                'd.haber',
                'd.concepto'
            ])
            ->orderBy('c.codigo')
            ->orderBy('a.fecha')
            ->orderBy('a.numero')
            ->get();

        // Agrupar por cuenta
        $cuentas = [];
        foreach ($movimientos as $mov) {
            $cuentaId = $mov->cuenta_id;
            
            if (!isset($cuentas[$cuentaId])) {
                $cuentas[$cuentaId] = [
                    'cuenta_id' => $cuentaId,
                    'codigo' => $mov->codigo,
                    'nombre' => $mov->cuenta_nombre,
                    'tipo' => $mov->cuenta_tipo,
                    'saldo_inicial' => $this->obtenerSaldoInicial($cuentaId, $fechaDesde),
                    'total_debe' => 0,
                    'total_haber' => 0,
                    'saldo_final' => 0,
                    'movimientos' => []
                ];
            }

            $cuentas[$cuentaId]['total_debe'] += $mov->debe;
            $cuentas[$cuentaId]['total_haber'] += $mov->haber;
            
            $cuentas[$cuentaId]['movimientos'][] = [
                'fecha' => $mov->fecha,
                'asiento_numero' => $mov->asiento_numero,
                'descripcion' => $mov->descripcion,
                'concepto' => $mov->concepto,
                'debe' => $mov->debe,
                'haber' => $mov->haber
            ];
        }

        // Calcular saldos finales
        foreach ($cuentas as &$cuenta) {
            $naturaleza = $this->obtenerNaturalezaCuenta($cuenta['tipo']);
            $saldoFinal = $cuenta['saldo_inicial'];
            
            if ($naturaleza === 'debito') {
                $saldoFinal += $cuenta['total_debe'] - $cuenta['total_haber'];
            } else {
                $saldoFinal += $cuenta['total_haber'] - $cuenta['total_debe'];
            }
            
            $cuenta['saldo_final'] = $saldoFinal;
            $cuenta['naturaleza'] = $naturaleza;
        }

        return [
            'periodo' => [
                'desde' => $fechaDesde,
                'hasta' => $fechaHasta
            ],
            'cuentas' => array_values($cuentas),
            'resumen' => [
                'total_cuentas' => count($cuentas),
                'total_debe' => array_sum(array_column($cuentas, 'total_debe')),
                'total_haber' => array_sum(array_column($cuentas, 'total_haber'))
            ]
        ];
    }

    /**
     * Generar Balance General a una fecha de corte
     * 
     * @param string $fechaCorte YYYY-MM-DD
     * @return array
     */
    public function balanceGeneral(string $fechaCorte): array
    {
        $saldos = DB::table('asientos as a')
            ->join('asientos_detalle as d', 'd.asiento_id', '=', 'a.id')
            ->join('cuentas as c', 'c.id', '=', 'd.cuenta_id')
            ->where('a.estado', 'confirmado')
            ->where('a.fecha', '<=', $fechaCorte)
            ->select([
                'c.id',
                'c.codigo',
                'c.nombre',
                'c.tipo',
                'c.nivel',
                'c.padre_id',
                DB::raw('SUM(d.debe) as total_debe'),
                DB::raw('SUM(d.haber) as total_haber')
            ])
            ->groupBy('c.id', 'c.codigo', 'c.nombre', 'c.tipo', 'c.nivel', 'c.padre_id')
            ->get();

        $activos = [];
        $pasivos = [];
        $patrimonio = [];

        foreach ($saldos as $saldo) {
            $naturaleza = $this->obtenerNaturalezaCuenta($saldo->tipo);
            $saldoFinal = $naturaleza === 'debito' 
                ? $saldo->total_debe - $saldo->total_haber
                : $saldo->total_haber - $saldo->total_debe;

            $cuenta = [
                'id' => $saldo->id,
                'codigo' => $saldo->codigo,
                'nombre' => $saldo->nombre,
                'nivel' => $saldo->nivel,
                'padre_id' => $saldo->padre_id,
                'saldo' => $saldoFinal
            ];

            switch ($saldo->tipo) {
                case 'activo':
                    if ($saldoFinal != 0) $activos[] = $cuenta;
                    break;
                case 'pasivo':
                    if ($saldoFinal != 0) $pasivos[] = $cuenta;
                    break;
                case 'patrimonio':
                    if ($saldoFinal != 0) $patrimonio[] = $cuenta;
                    break;
            }
        }

        $totalActivos = array_sum(array_column($activos, 'saldo'));
        $totalPasivos = array_sum(array_column($pasivos, 'saldo'));
        $totalPatrimonio = array_sum(array_column($patrimonio, 'saldo'));

        return [
            'fecha_corte' => $fechaCorte,
            'activos' => $this->organizarCuentasJerarquia($activos),
            'pasivos' => $this->organizarCuentasJerarquia($pasivos),
            'patrimonio' => $this->organizarCuentasJerarquia($patrimonio),
            'totales' => [
                'activos' => $totalActivos,
                'pasivos' => $totalPasivos,
                'patrimonio' => $totalPatrimonio,
                'balance_check' => abs($totalActivos - ($totalPasivos + $totalPatrimonio)) < 0.01
            ]
        ];
    }

    /**
     * Conciliación de Inventarios (Subledger vs GL)
     * 
     * @param string $fechaCorte YYYY-MM-DD
     * @return array
     */
    public function conciliacionInventarios(string $fechaCorte): array
    {
        // Total GL (General Ledger) - Cuentas de inventario
        $cuentasInventario = Cuenta::where('tipo', 'activo')
            ->where('codigo', 'like', '1.1.%') // Ajustar según plan de cuentas
            ->pluck('id');

        $totalGL = DB::table('asientos as a')
            ->join('asientos_detalle as d', 'd.asiento_id', '=', 'a.id')
            ->whereIn('d.cuenta_id', $cuentasInventario)
            ->where('a.estado', 'confirmado')
            ->where('a.fecha', '<=', $fechaCorte)
            ->select(DB::raw('SUM(d.debe - d.haber) as saldo'))
            ->value('saldo') ?? 0;

        // Total Subledger - Existencias
        $totalSubledger = Existencia::join('productos as p', 'existencias.producto_id', '=', 'p.id')
            ->whereNull('p.deleted_at') // Solo productos activos
            ->select(DB::raw('SUM(existencias.cantidad * existencias.costo_promedio) as total'))
            ->value('total') ?? 0;

        // Detalle por producto
        $detalleProductos = DB::table('existencias as e')
            ->join('productos as p', 'e.producto_id', '=', 'p.id')
            ->join('bodegas as b', 'e.bodega_id', '=', 'b.id')
            ->whereNull('p.deleted_at')
            ->where('e.cantidad', '>', 0)
            ->select([
                'p.id as producto_id',
                'p.nombre as producto_nombre',
                'p.sku',
                'b.nombre as bodega_nombre',
                'e.cantidad',
                'e.costo_promedio',
                DB::raw('e.cantidad * e.costo_promedio as valor_total')
            ])
            ->orderBy('p.nombre')
            ->orderBy('b.nombre')
            ->get();

        $diferencia = $totalGL - $totalSubledger;
        $tolerancia = 0.01; // 1 centavo de tolerancia
        $conciliado = abs($diferencia) <= $tolerancia;

        return [
            'fecha_corte' => $fechaCorte,
            'totales' => [
                'general_ledger' => $totalGL,
                'subledger' => $totalSubledger,
                'diferencia' => $diferencia,
                'conciliado' => $conciliado,
                'tolerancia' => $tolerancia
            ],
            'detalle_productos' => $detalleProductos->toArray(),
            'cuentas_inventario_ids' => $cuentasInventario->toArray(),
            'resumen' => [
                'productos_con_stock' => $detalleProductos->count(),
                'total_cantidad' => $detalleProductos->sum('cantidad'),
                'valor_promedio' => $detalleProductos->count() > 0 
                    ? $totalSubledger / $detalleProductos->sum('cantidad') 
                    : 0
            ]
        ];
    }

    /**
     * Obtener saldo inicial de una cuenta antes de una fecha
     */
    private function obtenerSaldoInicial(int $cuentaId, string $fecha): float
    {
        $saldo = DB::table('asientos as a')
            ->join('asientos_detalle as d', 'd.asiento_id', '=', 'a.id')
            ->where('d.cuenta_id', $cuentaId)
            ->where('a.estado', 'confirmado')
            ->where('a.fecha', '<', $fecha)
            ->select(DB::raw('SUM(d.debe - d.haber) as saldo'))
            ->value('saldo') ?? 0;

        $cuenta = Cuenta::find($cuentaId);
        $naturaleza = $this->obtenerNaturalezaCuenta($cuenta->tipo);

        return $naturaleza === 'debito' ? $saldo : -$saldo;
    }

    /**
     * Obtener naturaleza de la cuenta según su tipo
     */
    private function obtenerNaturalezaCuenta(string $tipo): string
    {
        return match($tipo) {
            'activo', 'gasto' => 'debito',
            'pasivo', 'patrimonio', 'ingreso' => 'credito',
            default => 'debito'
        };
    }

    /**
     * Organizar cuentas en jerarquía (padre-hijo)
     */
    private function organizarCuentasJerarquia(array $cuentas): array
    {
        $jerarquia = [];
        $cuentasPorId = collect($cuentas)->keyBy('id');

        foreach ($cuentas as $cuenta) {
            if ($cuenta['padre_id'] === null) {
                // Cuenta de nivel superior
                $cuenta['hijos'] = $this->obtenerHijos($cuenta['id'], $cuentasPorId);
                $jerarquia[] = $cuenta;
            }
        }

        return $jerarquia;
    }

    /**
     * Obtener cuentas hijas recursivamente
     */
    private function obtenerHijos(int $padreId, $cuentasPorId): array
    {
        $hijos = [];
        
        foreach ($cuentasPorId as $cuenta) {
            if ($cuenta['padre_id'] === $padreId) {
                $cuenta['hijos'] = $this->obtenerHijos($cuenta['id'], $cuentasPorId);
                $hijos[] = $cuenta;
            }
        }

        return $hijos;
    }

    /**
     * Verificar asientos descuadrados
     */
    public function verificarAsientosDescuadrados(): array
    {
        return DB::table('asientos as a')
            ->join('asientos_detalle as d', 'd.asiento_id', '=', 'a.id')
            ->where('a.estado', 'confirmado')
            ->select([
                'a.id',
                'a.fecha',
                'a.numero',
                'a.descripcion',
                DB::raw('SUM(d.debe) as total_debe'),
                DB::raw('SUM(d.haber) as total_haber'),
                DB::raw('ABS(SUM(d.debe) - SUM(d.haber)) as diferencia')
            ])
            ->groupBy('a.id', 'a.fecha', 'a.numero', 'a.descripcion')
            ->havingRaw('ABS(SUM(d.debe) - SUM(d.haber)) > 0.01')
            ->orderBy('a.fecha', 'desc')
            ->get()
            ->toArray();
    }
}
