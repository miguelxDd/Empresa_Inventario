<?php

namespace App\Http\Controllers;

use App\Models\Asiento;
use App\Models\AsientosDetalle;
use App\Models\Cuenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MayorController extends Controller
{
    /**
     * Mostrar el Libro Mayor
     */
    public function index(Request $request)
    {
        return view('contabilidad.mayor.index');
    }

    /**
     * Obtener datos del libro mayor
     */
    public function getData(Request $request)
    {
        // Validación base
        $rules = [
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'tipo_reporte' => 'required|in:general,cuenta',
        ];

        // Solo validar cuenta_id si el tipo es 'cuenta'
        if ($request->tipo_reporte === 'cuenta') {
            $rules['cuenta_id'] = 'required|exists:cuentas,id';
        }

        $request->validate($rules);

        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;
        $tipoReporte = $request->tipo_reporte;
        $cuentaId = $request->cuenta_id;

        try {
            if ($tipoReporte === 'general') {
                return $this->getMayorGeneral($fechaInicio, $fechaFin);
            } else {
                return $this->getMayorCuenta($fechaInicio, $fechaFin, $cuentaId);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener Mayor General (todas las cuentas)
     */
    private function getMayorGeneral($fechaInicio, $fechaFin)
    {
        // Obtener todas las cuentas que tienen movimientos en el período o saldo inicial
        $cuentasConMovimientos = DB::table('asientos as a')
            ->join('asientos_detalle as ad', 'a.id', '=', 'ad.asiento_id')
            ->join('cuentas as c', 'ad.cuenta_id', '=', 'c.id')
            ->where('a.estado', 'confirmado')
            ->where(function($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('a.fecha', [$fechaInicio, $fechaFin])
                      ->orWhere('a.fecha', '<', $fechaInicio);
            })
            ->select('c.id', 'c.codigo', 'c.nombre', 'c.tipo')
            ->distinct()
            ->orderBy('c.codigo')
            ->get();

        $cuentasData = [];
        $totalGeneralDebe = 0;
        $totalGeneralHaber = 0;

        foreach ($cuentasConMovimientos as $cuenta) {
            $dataCuenta = $this->procesarCuenta($cuenta->id, $fechaInicio, $fechaFin);
            
            if ($dataCuenta['tiene_movimientos'] || $dataCuenta['saldo_inicial'] != 0) {
                $dataCuenta['codigo'] = $cuenta->codigo;
                $dataCuenta['nombre'] = $cuenta->nombre;
                $dataCuenta['tipo'] = $cuenta->tipo;
                
                $cuentasData[] = $dataCuenta;
                $totalGeneralDebe += $dataCuenta['total_debe'];
                $totalGeneralHaber += $dataCuenta['total_haber'];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'cuentas' => $cuentasData,
                'resumen_general' => [
                    'total_debe' => $totalGeneralDebe,
                    'total_haber' => $totalGeneralHaber,
                    'diferencia' => $totalGeneralDebe - $totalGeneralHaber
                ]
            ]
        ]);
    }

    /**
     * Obtener Mayor por Cuenta específica
     */
    private function getMayorCuenta($fechaInicio, $fechaFin, $cuentaId)
    {
        $cuenta = Cuenta::find($cuentaId);
        $dataCuenta = $this->procesarCuenta($cuentaId, $fechaInicio, $fechaFin);
        
        $dataCuenta['codigo'] = $cuenta->codigo;
        $dataCuenta['nombre'] = $cuenta->nombre;
        $dataCuenta['tipo'] = $cuenta->tipo;

        return response()->json([
            'success' => true,
            'data' => [
                'cuenta' => $dataCuenta,
                'cuenta_info' => [
                    'codigo' => $cuenta->codigo,
                    'nombre' => $cuenta->nombre,
                    'tipo' => $cuenta->tipo
                ]
            ]
        ]);
    }

    /**
     * Procesar datos de una cuenta específica
     */
    private function procesarCuenta($cuentaId, $fechaInicio, $fechaFin)
    {
        // Obtener saldo inicial
        $saldoInicial = DB::table('asientos as a')
            ->join('asientos_detalle as ad', 'a.id', '=', 'ad.asiento_id')
            ->where('ad.cuenta_id', $cuentaId)
            ->where('a.fecha', '<', $fechaInicio)
            ->where('a.estado', 'confirmado')
            ->selectRaw('SUM(ad.debe - ad.haber) as saldo')
            ->value('saldo') ?? 0;

        // Obtener movimientos en el rango de fechas
        $movimientos = DB::table('asientos as a')
            ->join('asientos_detalle as ad', 'a.id', '=', 'ad.asiento_id')
            ->where('ad.cuenta_id', $cuentaId)
            ->whereBetween('a.fecha', [$fechaInicio, $fechaFin])
            ->where('a.estado', 'confirmado')
            ->select([
                'a.id as asiento_id',
                'a.fecha',
                'a.numero',
                'ad.debe',
                'ad.haber',
                'ad.concepto'
            ])
            ->orderBy('a.fecha')
            ->orderBy('a.numero')
            ->get();

        // Procesar movimientos y calcular saldos acumulados
        $saldoAcumulado = $saldoInicial;
        $movimientosProcesados = [];
        $totalDebe = 0;
        $totalHaber = 0;

        foreach ($movimientos as $movimiento) {
            $debe = floatval($movimiento->debe);
            $haber = floatval($movimiento->haber);
            
            $saldoAcumulado += ($debe - $haber);
            $totalDebe += $debe;
            $totalHaber += $haber;
            
            $movimientosProcesados[] = [
                'asiento_id' => $movimiento->asiento_id,
                'fecha' => Carbon::parse($movimiento->fecha)->format('d/m/Y'),
                'numero' => $movimiento->numero,
                'debe' => $debe,
                'haber' => $haber,
                'saldo_acumulado' => $saldoAcumulado,
                'concepto' => $movimiento->concepto ?? ''
            ];
        }

        return [
            'saldo_inicial' => $saldoInicial,
            'movimientos' => $movimientosProcesados,
            'total_debe' => $totalDebe,
            'total_haber' => $totalHaber,
            'saldo_final' => $saldoAcumulado,
            'tiene_movimientos' => count($movimientosProcesados) > 0
        ];
    }

    /**
     * Exportar libro mayor a CSV
     */
    public function export(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'tipo_reporte' => 'required|in:general,cuenta',
            'cuenta_id' => 'required_if:tipo_reporte,cuenta|exists:cuentas,id',
        ]);

        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;
        $tipoReporte = $request->tipo_reporte;
        $cuentaId = $request->cuenta_id;

        if ($tipoReporte === 'general') {
            return $this->exportMayorGeneral($fechaInicio, $fechaFin);
        } else {
            return $this->exportMayorCuenta($fechaInicio, $fechaFin, $cuentaId);
        }
    }

    /**
     * Exportar Mayor General a CSV
     */
    private function exportMayorGeneral($fechaInicio, $fechaFin)
    {
        // Obtener datos del mayor general
        $response = $this->getMayorGeneral($fechaInicio, $fechaFin);
        $data = json_decode($response->getContent(), true);
        $cuentas = $data['data']['cuentas'];

        $filename = 'libro_mayor_general_' . 
                   str_replace('-', '', $fechaInicio) . '_' . 
                   str_replace('-', '', $fechaFin) . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($cuentas, $fechaInicio, $fechaFin, $data) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Información del reporte
            fputcsv($file, ['LIBRO MAYOR GENERAL'], ';');
            fputcsv($file, ['Período: ' . Carbon::parse($fechaInicio)->format('d/m/Y') . ' al ' . Carbon::parse($fechaFin)->format('d/m/Y')], ';');
            fputcsv($file, ['Total Debe: ' . number_format($data['data']['resumen_general']['total_debe'], 2, ',', '.')], ';');
            fputcsv($file, ['Total Haber: ' . number_format($data['data']['resumen_general']['total_haber'], 2, ',', '.')], ';');
            fputcsv($file, [], ';'); // Línea vacía

            foreach ($cuentas as $cuenta) {
                // Información de la cuenta
                fputcsv($file, [], ';'); // Línea vacía
                fputcsv($file, ['CUENTA: ' . $cuenta['codigo'] . ' - ' . $cuenta['nombre']], ';');
                fputcsv($file, ['TIPO: ' . $cuenta['tipo']], ';');
                
                // Encabezados de movimientos
                fputcsv($file, [
                    'Fecha',
                    'Número',
                    'Debe',
                    'Haber',
                    'Saldo',
                    'Concepto'
                ], ';');

                // Saldo inicial
                if ($cuenta['saldo_inicial'] != 0) {
                    fputcsv($file, [
                        '',
                        '',
                        '',
                        '',
                        number_format($cuenta['saldo_inicial'], 2, ',', '.'),
                        'SALDO INICIAL'
                    ], ';');
                }

                // Movimientos
                foreach ($cuenta['movimientos'] as $movimiento) {
                    fputcsv($file, [
                        $movimiento['fecha'],
                        $movimiento['numero'],
                        $movimiento['debe'] > 0 ? number_format($movimiento['debe'], 2, ',', '.') : '',
                        $movimiento['haber'] > 0 ? number_format($movimiento['haber'], 2, ',', '.') : '',
                        number_format($movimiento['saldo_acumulado'], 2, ',', '.'),
                        $movimiento['concepto']
                    ], ';');
                }

                // Totales de la cuenta
                fputcsv($file, [
                    'TOTALES',
                    '',
                    number_format($cuenta['total_debe'], 2, ',', '.'),
                    number_format($cuenta['total_haber'], 2, ',', '.'),
                    number_format($cuenta['saldo_final'], 2, ',', '.'),
                    ''
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exportar Mayor por Cuenta a CSV
     */
    private function exportMayorCuenta($fechaInicio, $fechaFin, $cuentaId)
    {
        $cuenta = Cuenta::find($cuentaId);

        // Obtener saldo inicial
        $saldoInicial = DB::table('asientos as a')
            ->join('asientos_detalle as ad', 'a.id', '=', 'ad.asiento_id')
            ->where('ad.cuenta_id', $cuentaId)
            ->where('a.fecha', '<', $fechaInicio)
            ->where('a.estado', 'confirmado')
            ->selectRaw('SUM(ad.debe - ad.haber) as saldo')
            ->value('saldo') ?? 0;

        // Obtener movimientos
        $movimientos = DB::table('asientos as a')
            ->join('asientos_detalle as ad', 'a.id', '=', 'ad.asiento_id')
            ->where('ad.cuenta_id', $cuentaId)
            ->whereBetween('a.fecha', [$fechaInicio, $fechaFin])
            ->where('a.estado', 'confirmado')
            ->select([
                'a.fecha',
                'a.numero',
                'ad.debe',
                'ad.haber',
                'ad.concepto'
            ])
            ->orderBy('a.fecha')
            ->orderBy('a.numero')
            ->get();

        $filename = 'libro_mayor_' . $cuenta->codigo . '_' . 
                   str_replace('-', '', $fechaInicio) . '_' . 
                   str_replace('-', '', $fechaFin) . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($movimientos, $cuenta, $saldoInicial, $fechaInicio, $fechaFin) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Información de la cuenta
            fputcsv($file, ['LIBRO MAYOR POR CUENTA'], ';');
            fputcsv($file, ['Cuenta: ' . $cuenta->codigo . ' - ' . $cuenta->nombre], ';');
            fputcsv($file, ['Tipo: ' . $cuenta->tipo], ';');
            fputcsv($file, ['Período: ' . Carbon::parse($fechaInicio)->format('d/m/Y') . ' al ' . Carbon::parse($fechaFin)->format('d/m/Y')], ';');
            fputcsv($file, [], ';'); // Línea vacía
            
            // Encabezados
            fputcsv($file, [
                'Fecha',
                'Número',
                'Debe',
                'Haber',
                'Saldo',
                'Concepto'
            ], ';');

            // Saldo inicial
            $saldoAcumulado = $saldoInicial;
            if ($saldoInicial != 0) {
                fputcsv($file, [
                    '',
                    '',
                    '',
                    '',
                    number_format($saldoInicial, 2, ',', '.'),
                    'SALDO INICIAL'
                ], ';');
            }

            // Movimientos
            foreach ($movimientos as $movimiento) {
                $saldoAcumulado += ($movimiento->debe - $movimiento->haber);
                
                fputcsv($file, [
                    Carbon::parse($movimiento->fecha)->format('d/m/Y'),
                    $movimiento->numero,
                    $movimiento->debe > 0 ? number_format($movimiento->debe, 2, ',', '.') : '',
                    $movimiento->haber > 0 ? number_format($movimiento->haber, 2, ',', '.') : '',
                    number_format($saldoAcumulado, 2, ',', '.'),
                    $movimiento->concepto ?? ''
                ], ';');
            }

            // Totales
            $totalDebe = $movimientos->sum('debe');
            $totalHaber = $movimientos->sum('haber');
            
            fputcsv($file, [], ';'); // Línea vacía
            fputcsv($file, [
                'TOTALES PERÍODO',
                '',
                number_format($totalDebe, 2, ',', '.'),
                number_format($totalHaber, 2, ',', '.'),
                number_format($saldoAcumulado, 2, ',', '.'),
                ''
            ], ';');

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
