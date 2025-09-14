<?php

namespace App\Http\Controllers;

use App\Models\Asiento;
use App\Models\AsientosDetalle;
use App\Models\Cuenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BalanzaController extends Controller
{
    /**
     * Mostrar la Balanza de Comprobación
     */
    public function index(Request $request)
    {
        return view('contabilidad.balanza.index');
    }

    /**
     * Obtener datos de la balanza de comprobación para DataTables
     */
    public function getData(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        // Consulta principal para obtener saldos por cuenta
        $balanza = DB::table('cuentas as c')
            ->leftJoin('asientos_detalle as ad', 'c.id', '=', 'ad.cuenta_id')
            ->leftJoin('asientos as a', function($join) use ($fechaInicio, $fechaFin) {
                $join->on('ad.asiento_id', '=', 'a.id')
                     ->whereBetween('a.fecha', [$fechaInicio, $fechaFin])
                     ->where('a.estado', 'confirmado');
            })
            ->where('c.activa', true)
            ->groupBy('c.id', 'c.codigo', 'c.nombre', 'c.tipo')
            ->selectRaw('
                c.id,
                c.codigo,
                c.nombre,
                c.tipo,
                COALESCE(SUM(ad.debe), 0) as total_debe,
                COALESCE(SUM(ad.haber), 0) as total_haber,
                COALESCE(SUM(ad.debe - ad.haber), 0) as saldo
            ')
            ->having('total_debe', '>', 0)
            ->orHaving('total_haber', '>', 0)
            ->orderBy('c.codigo')
            ->get();

        // Formatear datos para DataTables
        $data = $balanza->map(function($cuenta) {
            $saldo = floatval($cuenta->saldo);
            $saldoDeudor = $saldo > 0 ? $saldo : 0;
            $saldoAcreedor = $saldo < 0 ? abs($saldo) : 0;

            return [
                'codigo' => $cuenta->codigo,
                'cuenta' => $cuenta->codigo . ' - ' . $cuenta->nombre,
                'tipo' => ucfirst($cuenta->tipo),
                'debe' => number_format($cuenta->total_debe, 2),
                'haber' => number_format($cuenta->total_haber, 2),
                'saldo_deudor' => $saldoDeudor > 0 ? number_format($saldoDeudor, 2) : '',
                'saldo_acreedor' => $saldoAcreedor > 0 ? number_format($saldoAcreedor, 2) : '',
            ];
        });

        // Calcular totales
        $totales = [
            'debe' => $balanza->sum('total_debe'),
            'haber' => $balanza->sum('total_haber'),
            'saldo_deudor' => $balanza->where('saldo', '>', 0)->sum('saldo'),
            'saldo_acreedor' => abs($balanza->where('saldo', '<', 0)->sum('saldo')),
        ];

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $data->count(),
            'recordsFiltered' => $data->count(),
            'data' => $data,
            'totales' => [
                'debe' => number_format($totales['debe'], 2),
                'haber' => number_format($totales['haber'], 2),
                'saldo_deudor' => number_format($totales['saldo_deudor'], 2),
                'saldo_acreedor' => number_format($totales['saldo_acreedor'], 2),
            ],
            'resumen_por_tipo' => $this->obtenerResumenPorTipo($balanza)
        ]);
    }

    /**
     * Exportar balanza de comprobación a CSV
     */
    public function export(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        // Obtener datos de la balanza
        $balanza = DB::table('cuentas as c')
            ->leftJoin('asientos_detalle as ad', 'c.id', '=', 'ad.cuenta_id')
            ->leftJoin('asientos as a', function($join) use ($fechaInicio, $fechaFin) {
                $join->on('ad.asiento_id', '=', 'a.id')
                     ->whereBetween('a.fecha', [$fechaInicio, $fechaFin])
                     ->where('a.estado', 'confirmado');
            })
            ->where('c.activa', true)
            ->groupBy('c.id', 'c.codigo', 'c.nombre', 'c.tipo')
            ->selectRaw('
                c.codigo,
                c.nombre,
                c.tipo,
                COALESCE(SUM(ad.debe), 0) as total_debe,
                COALESCE(SUM(ad.haber), 0) as total_haber,
                COALESCE(SUM(ad.debe - ad.haber), 0) as saldo
            ')
            ->having('total_debe', '>', 0)
            ->orHaving('total_haber', '>', 0)
            ->orderBy('c.codigo')
            ->get();

        $filename = 'balanza_comprobacion_' . 
                   str_replace('-', '', $fechaInicio) . '_' . 
                   str_replace('-', '', $fechaFin) . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($balanza, $fechaInicio, $fechaFin) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Información del reporte
            fputcsv($file, ['BALANZA DE COMPROBACIÓN'], ';');
            fputcsv($file, ['Período: ' . Carbon::parse($fechaInicio)->format('d/m/Y') . ' al ' . Carbon::parse($fechaFin)->format('d/m/Y')], ';');
            fputcsv($file, [], ';'); // Línea vacía
            
            // Encabezados
            fputcsv($file, [
                'Código',
                'Cuenta',
                'Tipo',
                'Debe',
                'Haber',
                'Saldo Deudor',
                'Saldo Acreedor'
            ], ';');

            // Datos
            $totales = [
                'debe' => 0,
                'haber' => 0,
                'saldo_deudor' => 0,
                'saldo_acreedor' => 0,
            ];

            foreach ($balanza as $cuenta) {
                $saldo = floatval($cuenta->saldo);
                $saldoDeudor = $saldo > 0 ? $saldo : 0;
                $saldoAcreedor = $saldo < 0 ? abs($saldo) : 0;

                $totales['debe'] += $cuenta->total_debe;
                $totales['haber'] += $cuenta->total_haber;
                $totales['saldo_deudor'] += $saldoDeudor;
                $totales['saldo_acreedor'] += $saldoAcreedor;

                fputcsv($file, [
                    $cuenta->codigo,
                    $cuenta->codigo . ' - ' . $cuenta->nombre,
                    ucfirst($cuenta->tipo),
                    number_format($cuenta->total_debe, 2, ',', '.'),
                    number_format($cuenta->total_haber, 2, ',', '.'),
                    $saldoDeudor > 0 ? number_format($saldoDeudor, 2, ',', '.') : '',
                    $saldoAcreedor > 0 ? number_format($saldoAcreedor, 2, ',', '.') : ''
                ], ';');
            }

            // Totales
            fputcsv($file, [], ';'); // Línea vacía
            fputcsv($file, [
                '',
                'TOTALES',
                '',
                number_format($totales['debe'], 2, ',', '.'),
                number_format($totales['haber'], 2, ',', '.'),
                number_format($totales['saldo_deudor'], 2, ',', '.'),
                number_format($totales['saldo_acreedor'], 2, ',', '.')
            ], ';');

            // Resumen por tipo
            fputcsv($file, [], ';'); // Línea vacía
            fputcsv($file, ['RESUMEN POR TIPO DE CUENTA'], ';');
            
            $resumenPorTipo = $this->calcularResumenPorTipo($balanza);
            foreach ($resumenPorTipo as $tipo => $datos) {
                fputcsv($file, [
                    ucfirst($tipo),
                    '',
                    '',
                    number_format($datos['debe'], 2, ',', '.'),
                    number_format($datos['haber'], 2, ',', '.'),
                    number_format($datos['saldo_deudor'], 2, ',', '.'),
                    number_format($datos['saldo_acreedor'], 2, ',', '.')
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Obtener resumen por tipo de cuenta para mostrar en pantalla
     */
    private function obtenerResumenPorTipo($balanza)
    {
        $resumen = [];
        $tipos = ['activo', 'pasivo', 'patrimonio', 'ingreso', 'gasto'];

        foreach ($tipos as $tipo) {
            $cuentasTipo = $balanza->where('tipo', $tipo);
            
            if ($cuentasTipo->isNotEmpty()) {
                $saldoDeudor = $cuentasTipo->where('saldo', '>', 0)->sum('saldo');
                $saldoAcreedor = abs($cuentasTipo->where('saldo', '<', 0)->sum('saldo'));
                
                $resumen[$tipo] = [
                    'tipo' => ucfirst($tipo),
                    'debe' => number_format($cuentasTipo->sum('total_debe'), 2),
                    'haber' => number_format($cuentasTipo->sum('total_haber'), 2),
                    'saldo_deudor' => number_format($saldoDeudor, 2),
                    'saldo_acreedor' => number_format($saldoAcreedor, 2),
                ];
            }
        }

        return $resumen;
    }

    /**
     * Calcular resumen por tipo para exportación
     */
    private function calcularResumenPorTipo($balanza)
    {
        $resumen = [];
        $tipos = ['activo', 'pasivo', 'patrimonio', 'ingreso', 'gasto'];

        foreach ($tipos as $tipo) {
            $cuentasTipo = $balanza->where('tipo', $tipo);
            
            if ($cuentasTipo->isNotEmpty()) {
                $saldoDeudor = $cuentasTipo->where('saldo', '>', 0)->sum('saldo');
                $saldoAcreedor = abs($cuentasTipo->where('saldo', '<', 0)->sum('saldo'));
                
                $resumen[$tipo] = [
                    'debe' => $cuentasTipo->sum('total_debe'),
                    'haber' => $cuentasTipo->sum('total_haber'),
                    'saldo_deudor' => $saldoDeudor,
                    'saldo_acreedor' => $saldoAcreedor,
                ];
            }
        }

        return $resumen;
    }
}
