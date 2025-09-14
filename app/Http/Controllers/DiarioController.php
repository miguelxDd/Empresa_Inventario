<?php

namespace App\Http\Controllers;

use App\Models\Asiento;
use App\Models\AsientosDetalle;
use App\Models\Cuenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DiarioController extends Controller
{
    /**
     * Mostrar el Libro Diario
     */
    public function index(Request $request)
    {
        return view('contabilidad.diario.index');
    }

    /**
     * Obtener datos del libro diario para DataTables
     */
    public function getData(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        $query = DB::table('asientos as a')
            ->join('asientos_detalle as ad', 'a.id', '=', 'ad.asiento_id')
            ->join('cuentas as c', 'ad.cuenta_id', '=', 'c.id')
            ->whereBetween('a.fecha', [$fechaInicio, $fechaFin])
            ->where('a.estado', 'confirmado')
            ->select([
                'a.id as asiento_id',
                'a.fecha',
                'a.numero',
                'a.descripcion',
                'a.origen_tabla',
                'a.origen_id',
                'c.codigo as cuenta_codigo',
                'c.nombre as cuenta_nombre',
                'ad.debe',
                'ad.haber',
                'ad.concepto'
            ])
            ->orderBy('a.fecha')
            ->orderBy('a.numero')
            ->orderBy('ad.id');

        $registros = $query->get();

        // Calcular totales
        $totalDebe = $registros->sum('debe');
        $totalHaber = $registros->sum('haber');

        // Formatear datos para DataTables
        $data = $registros->map(function($registro) {
            return [
                'fecha' => Carbon::parse($registro->fecha)->format('d/m/Y'),
                'numero' => $registro->numero,
                'descripcion' => $registro->descripcion,
                'cuenta' => $registro->cuenta_codigo . ' - ' . $registro->cuenta_nombre,
                'debe' => $registro->debe > 0 ? number_format($registro->debe, 2) : '',
                'haber' => $registro->haber > 0 ? number_format($registro->haber, 2) : '',
                'concepto' => $registro->concepto ?? '',
                'acciones' => $this->generarAcciones($registro),
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $data->count(),
            'recordsFiltered' => $data->count(),
            'data' => $data,
            'totales' => [
                'debe' => number_format($totalDebe, 2),
                'haber' => number_format($totalHaber, 2),
            ]
        ]);
    }

    /**
     * Exportar libro diario a CSV
     */
    public function export(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        $registros = DB::table('asientos as a')
            ->join('asientos_detalle as ad', 'a.id', '=', 'ad.asiento_id')
            ->join('cuentas as c', 'ad.cuenta_id', '=', 'c.id')
            ->whereBetween('a.fecha', [$fechaInicio, $fechaFin])
            ->where('a.estado', 'confirmado')
            ->select([
                'a.fecha',
                'a.numero',
                'a.descripcion',
                'c.codigo as cuenta_codigo',
                'c.nombre as cuenta_nombre',
                'ad.debe',
                'ad.haber',
                'ad.concepto'
            ])
            ->orderBy('a.fecha')
            ->orderBy('a.numero')
            ->orderBy('ad.id')
            ->get();

        $filename = 'libro_diario_' . str_replace('-', '', $fechaInicio) . '_' . str_replace('-', '', $fechaFin) . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($registros) {
            $file = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados
            fputcsv($file, [
                'Fecha',
                'Número',
                'Descripción',
                'Código Cuenta',
                'Nombre Cuenta',
                'Debe',
                'Haber',
                'Concepto'
            ], ';');

            // Datos
            foreach ($registros as $registro) {
                fputcsv($file, [
                    Carbon::parse($registro->fecha)->format('d/m/Y'),
                    $registro->numero,
                    $registro->descripcion,
                    $registro->cuenta_codigo,
                    $registro->cuenta_nombre,
                    $registro->debe > 0 ? number_format($registro->debe, 2, ',', '.') : '',
                    $registro->haber > 0 ? number_format($registro->haber, 2, ',', '.') : '',
                    $registro->concepto ?? ''
                ], ';');
            }

            // Totales
            $totalDebe = $registros->sum('debe');
            $totalHaber = $registros->sum('haber');
            
            fputcsv($file, [], ';'); // Línea vacía
            fputcsv($file, [
                'TOTALES',
                '',
                '',
                '',
                '',
                number_format($totalDebe, 2, ',', '.'),
                number_format($totalHaber, 2, ',', '.'),
                ''
            ], ';');

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generar botones de acciones para cada registro
     */
    private function generarAcciones($registro)
    {
        $acciones = [];
        
        // Link al asiento
        $acciones[] = '<a href="#" onclick="verAsiento(' . $registro->asiento_id . ')" 
                         class="btn btn-sm btn-outline-primary" title="Ver Asiento">
                         <i class="fas fa-eye"></i>
                       </a>';
        
        // Link al movimiento si existe
        if ($registro->origen_tabla === 'movimientos' && $registro->origen_id) {
            $acciones[] = '<a href="#" onclick="verMovimiento(' . $registro->origen_id . ')" 
                             class="btn btn-sm btn-outline-info" title="Ver Movimiento">
                             <i class="fas fa-truck"></i>
                           </a>';
        }
        
        return implode(' ', $acciones);
    }
}
