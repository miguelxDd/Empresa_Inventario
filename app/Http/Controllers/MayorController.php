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
     * Obtener datos del libro mayor para DataTables
     */
    public function getData(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'cuenta_id' => 'required|exists:cuentas,id',
        ]);

        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;
        $cuentaId = $request->cuenta_id;

        // Obtener información de la cuenta
        $cuenta = Cuenta::find($cuentaId);

        // Obtener saldo inicial (movimientos anteriores a la fecha de inicio)
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

        // Calcular saldos acumulados
        $saldoAcumulado = $saldoInicial;
        $data = collect();

        // Agregar fila de saldo inicial si existe
        if ($saldoInicial != 0) {
            $data->push([
                'fecha' => '',
                'numero' => '',
                'debe' => '',
                'haber' => '',
                'saldo' => number_format($saldoInicial, 2),
                'concepto' => 'SALDO INICIAL',
                'acciones' => ''
            ]);
        }

        // Procesar movimientos
        foreach ($movimientos as $movimiento) {
            $saldoAcumulado += ($movimiento->debe - $movimiento->haber);
            
            $data->push([
                'fecha' => Carbon::parse($movimiento->fecha)->format('d/m/Y'),
                'numero' => $movimiento->numero,
                'debe' => $movimiento->debe > 0 ? number_format($movimiento->debe, 2) : '',
                'haber' => $movimiento->haber > 0 ? number_format($movimiento->haber, 2) : '',
                'saldo' => number_format($saldoAcumulado, 2),
                'concepto' => $movimiento->concepto ?? '',
                'acciones' => '<a href="#" onclick="verAsiento(' . $movimiento->asiento_id . ')" 
                                 class="btn btn-sm btn-outline-primary" title="Ver Asiento">
                                 <i class="fas fa-eye"></i>
                               </a>'
            ]);
        }

        // Calcular totales del período
        $totalDebe = $movimientos->sum('debe');
        $totalHaber = $movimientos->sum('haber');

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $data->count(),
            'recordsFiltered' => $data->count(),
            'data' => $data,
            'cuenta' => [
                'codigo' => $cuenta->codigo,
                'nombre' => $cuenta->nombre,
                'tipo' => $cuenta->tipo
            ],
            'totales' => [
                'debe' => number_format($totalDebe, 2),
                'haber' => number_format($totalHaber, 2),
                'saldo_inicial' => number_format($saldoInicial, 2),
                'saldo_final' => number_format($saldoAcumulado, 2),
            ]
        ]);
    }

    /**
     * Exportar libro mayor a CSV
     */
    public function export(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'cuenta_id' => 'required|exists:cuentas,id',
        ]);

        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;
        $cuentaId = $request->cuenta_id;

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
            fputcsv($file, ['LIBRO MAYOR'], ';');
            fputcsv($file, ['Cuenta: ' . $cuenta->codigo . ' - ' . $cuenta->nombre], ';');
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
