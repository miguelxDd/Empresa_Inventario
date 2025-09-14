<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Bodega;
use App\Models\Existencia;
use App\Models\Movimiento;
use App\Models\MovimientoDetalle;
use App\Models\Asiento;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ReporteController extends Controller
{
    /**
     * Display the reports main page.
     */
    public function index()
    {
        return view('reportes.index');
    }

    /**
     * Show kardex report form
     */
    public function kardex()
    {
        $productos = Producto::where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'sku as codigo', 'nombre']);
        
        $bodegas = Bodega::where('activa', true)
            ->orderBy('nombre')
            ->get(['id', 'codigo', 'nombre']);

        return view('reportes.kardex', compact('productos', 'bodegas'));
    }

    /**
     * Generate kardex report data
     */
    public function generateKardex(Request $request): JsonResponse
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'bodega_id' => 'nullable|exists:bodegas,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        try {
            $producto = Producto::with(['categoria', 'unidade'])->findOrFail($request->producto_id);
            $bodega = $request->bodega_id ? Bodega::findOrFail($request->bodega_id) : null;

            // Obtener saldo inicial
            $saldoInicial = $this->getSaldoInicial(
                $request->producto_id, 
                $request->bodega_id, 
                $request->fecha_inicio
            );

            // Obtener movimientos del período
            $movimientos = $this->getMovimientosKardex(
                $request->producto_id,
                $request->bodega_id,
                $request->fecha_inicio,
                $request->fecha_fin
            );

            // Calcular kardex
            $kardex = $this->calcularKardex($movimientos, $saldoInicial);

            return response()->json([
                'success' => true,
                'data' => [
                    'producto' => $producto,
                    'bodega' => $bodega,
                    'periodo' => [
                        'fecha_inicio' => $request->fecha_inicio,
                        'fecha_fin' => $request->fecha_fin
                    ],
                    'saldo_inicial' => $saldoInicial,
                    'movimientos' => $kardex['movimientos'],
                    'resumen' => $kardex['resumen']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el kardex: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show existencias report
     */
    public function existencias()
    {
        return view('reportes.existencias');
    }

    /**
     * Get existencias data for DataTable
     */
    public function getExistencias(Request $request): JsonResponse
    {
        try {
            $query = DB::table('existencias as e')
                ->join('productos as p', 'e.producto_id', '=', 'p.id')
                ->join('bodegas as b', 'e.bodega_id', '=', 'b.id')
                ->leftJoin('categorias as c', 'p.categoria_id', '=', 'c.id')
                ->leftJoin('unidades as u', 'p.unidad_id', '=', 'u.id')
                ->select([
                    'e.*',
                    'p.sku as producto_codigo',
                    'p.nombre as producto_nombre',
                    'c.nombre as categoria_nombre',
                    'u.abreviatura as unidad_display',
                    'b.codigo as bodega_codigo',
                    'b.nombre as bodega_nombre'
                ]);

            // Filtros
            if ($request->filled('producto_id')) {
                $query->where('e.producto_id', $request->producto_id);
            }

            if ($request->filled('bodega_id')) {
                $query->where('e.bodega_id', $request->bodega_id);
            }

            if ($request->filled('categoria_id')) {
                $query->where('p.categoria_id', $request->categoria_id);
            }

            if ($request->filled('stock_minimo')) {
                $query->where('e.cantidad', '<', 10);
            }

            $existencias = $query->where('e.cantidad', '>', 0)
                ->orderBy('p.nombre')
                ->get();

            // Calcular resumen
            $resumen = [
                'total_registros' => $existencias->count(),
                'total_cantidad' => $existencias->sum('cantidad'),
                'total_valor' => $existencias->sum(function($existencia) {
                    return $existencia->cantidad * $existencia->costo_promedio;
                }),
                'productos_unicos' => $existencias->unique('producto_codigo')->count(),
                'bodegas_con_stock' => $existencias->unique('bodega_codigo')->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $existencias->map(function ($existencia) {
                    return [
                        'id' => $existencia->id,
                        'producto_codigo' => $existencia->producto_codigo,
                        'producto_nombre' => $existencia->producto_nombre,
                        'categoria_nombre' => $existencia->categoria_nombre ?: 'Sin categoría',
                        'unidad_display' => $existencia->unidad_display ?: 'UND',
                        'bodega_codigo' => $existencia->bodega_codigo,
                        'bodega_nombre' => $existencia->bodega_nombre,
                        'cantidad' => number_format($existencia->cantidad, 2),
                        'costo_promedio' => number_format($existencia->costo_promedio, 2),
                        'valor_total' => number_format($existencia->cantidad * $existencia->costo_promedio, 2),
                        'updated_at' => date('d/m/Y H:i', strtotime($existencia->updated_at))
                    ];
                }),
                'resumen' => $resumen
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las existencias: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export existencias to CSV
     */
    public function exportExistenciasCSV(Request $request)
    {
        try {
            $query = DB::table('existencias as e')
                ->join('productos as p', 'e.producto_id', '=', 'p.id')
                ->join('bodegas as b', 'e.bodega_id', '=', 'b.id')
                ->leftJoin('categorias as c', 'p.categoria_id', '=', 'c.id')
                ->leftJoin('unidades as u', 'p.unidad_id', '=', 'u.id')
                ->select([
                    'e.*',
                    'p.sku as producto_codigo',
                    'p.nombre as producto_nombre',
                    'c.nombre as categoria_nombre',
                    'u.abreviatura as unidad_display',
                    'b.codigo as bodega_codigo',
                    'b.nombre as bodega_nombre'
                ]);

            // Aplicar filtros
            if ($request->filled('producto_id')) {
                $query->where('e.producto_id', $request->producto_id);
            }

            if ($request->filled('bodega_id')) {
                $query->where('e.bodega_id', $request->bodega_id);
            }

            if ($request->filled('categoria_id')) {
                $query->where('p.categoria_id', $request->categoria_id);
            }

            if ($request->filled('stock_minimo')) {
                $query->where('e.cantidad', '<', 10);
            }

            $existencias = $query->where('e.cantidad', '>', 0)
                ->orderBy('p.nombre')
                ->get();

            $filename = 'existencias_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                "Content-type" => "text/csv; charset=UTF-8",
                "Content-Disposition" => "attachment; filename=$filename",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ];

            $callback = function() use ($existencias) {
                $file = fopen('php://output', 'w');
                
                // BOM UTF-8
                fwrite($file, "\xEF\xBB\xBF");
                
                // Headers CSV
                fputcsv($file, [
                    'Código Producto',
                    'Nombre Producto', 
                    'Categoría',
                    'Unidad',
                    'Código Bodega',
                    'Nombre Bodega',
                    'Cantidad',
                    'Costo Promedio',
                    'Valor Total',
                    'Última Actualización'
                ], ';');

                foreach ($existencias as $existencia) {
                    fputcsv($file, [
                        $existencia->producto_codigo,
                        $existencia->producto_nombre,
                        $existencia->categoria_nombre ?: 'Sin categoría',
                        $existencia->unidad_display ?: 'UND',
                        $existencia->bodega_codigo,
                        $existencia->bodega_nombre,
                        str_replace('.', ',', number_format($existencia->cantidad, 2)),
                        str_replace('.', ',', number_format($existencia->costo_promedio, 2)),
                        str_replace('.', ',', number_format($existencia->cantidad * $existencia->costo_promedio, 2)),
                        date('d/m/Y H:i', strtotime($existencia->updated_at))
                    ], ';');
                }

                fclose($file);
            };

            return Response::stream($callback, 200, $headers);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al exportar: ' . $e->getMessage()]);
        }
    }

    /**
     * Show asientos contables report
     */
    public function asientos()
    {
        return view('reportes.asientos');
    }

    /**
     * Get asientos data for DataTable
     */
    public function getAsientos(Request $request): JsonResponse
    {
        try {
            $query = DB::table('asientos as ac')
                ->leftJoin('movimientos as mi', function($join) {
                    $join->on('ac.origen_id', '=', 'mi.id')
                         ->where('ac.origen_tabla', '=', 'movimientos');
                })
                ->select([
                    'ac.id',
                    'ac.numero',
                    'ac.fecha',
                    'ac.descripcion as concepto',
                    'ac.total_debe as total_debito',
                    'ac.total_haber as total_credito',
                    'ac.origen_tabla',
                    'ac.origen_id',
                    'ac.created_at',
                    'mi.tipo as tipo_movimiento',
                    'mi.observaciones as movimiento_observaciones'
                ]);

            // Filtros
            if ($request->filled('fecha_inicio')) {
                $query->whereDate('ac.fecha', '>=', $request->fecha_inicio);
            }

            if ($request->filled('fecha_fin')) {
                $query->whereDate('ac.fecha', '<=', $request->fecha_fin);
            }

            if ($request->filled('origen_tabla')) {
                $query->where('ac.origen_tabla', $request->origen_tabla);
            }

            if ($request->filled('numero')) {
                $query->where('ac.numero', 'like', '%' . $request->numero . '%');
            }

            $asientos = $query->orderBy('ac.created_at', 'desc')
                ->orderBy('ac.numero', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $asientos->map(function ($asiento) {
                    return [
                        'id' => $asiento->id,
                        'numero' => $asiento->numero,
                        'fecha' => date('d/m/Y', strtotime($asiento->fecha)),
                        'concepto' => $asiento->concepto,
                        'total_debito' => number_format($asiento->total_debito, 2),
                        'total_credito' => number_format($asiento->total_credito, 2),
                        'origen_tabla' => $asiento->origen_tabla,
                        'origen_id' => $asiento->origen_id,
                        'tipo_movimiento' => $asiento->tipo_movimiento ? ucfirst($asiento->tipo_movimiento) : null,
                        'movimiento_observaciones' => $asiento->movimiento_observaciones,
                        'created_at' => date('d/m/Y H:i', strtotime($asiento->created_at)),
                        'tiene_movimiento' => $asiento->origen_tabla === 'movimientos' && $asiento->origen_id
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los asientos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get form options for reports
     */
    public function getOptions(): JsonResponse
    {
        try {
            $productos = Producto::where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'sku as codigo', 'nombre']);
            
            $bodegas = Bodega::where('activa', true)
                ->orderBy('nombre')
                ->get(['id', 'codigo', 'nombre']);

            $categorias = DB::table('categorias')
                ->where('activa', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre']);

            return response()->json([
                'success' => true,
                'data' => [
                    'productos' => $productos,
                    'bodegas' => $bodegas,
                    'categorias' => $categorias
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las opciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Private method to get saldo inicial
     */
    private function getSaldoInicial($productoId, $bodegaId, $fechaInicio)
    {
        // Obtener saldo inicial desde las existencias actuales
        // Por simplicidad, retornamos 0 si no hay datos históricos detallados
        return 0;
    }

    /**
     * Private method to get movimientos for kardex
     */
    private function getMovimientosKardex($productoId, $bodegaId, $fechaInicio, $fechaFin)
    {
        $query = DB::table('movimiento_detalles as md')
            ->join('movimientos as m', 'md.movimiento_id', '=', 'm.id')
            ->leftJoin('bodegas as bo', 'm.bodega_origen_id', '=', 'bo.id')
            ->leftJoin('bodegas as bd', 'm.bodega_destino_id', '=', 'bd.id')
            ->where('md.producto_id', $productoId)
            ->whereBetween('m.fecha', [$fechaInicio, $fechaFin]);

        if ($bodegaId) {
            $query->where(function($q) use ($bodegaId) {
                $q->where('m.bodega_origen_id', $bodegaId)
                  ->orWhere('m.bodega_destino_id', $bodegaId);
            });
        }

        return $query->select([
            'm.id as movimiento_id',
            'm.fecha',
            'm.tipo as tipo_movimiento',
            'm.bodega_origen_id',
            'm.bodega_destino_id',
            'bo.nombre as bodega_origen_nombre',
            'bd.nombre as bodega_destino_nombre',
            'md.cantidad',
            'md.costo_unitario',
            'm.observaciones'
        ])
        ->orderBy('m.fecha')
        ->orderBy('m.id')
        ->get();
    }

    /**
     * Private method to calculate kardex
     */
    private function calcularKardex($movimientos, $saldoInicial)
    {
        $kardex = [];
        $saldoActual = $saldoInicial;
        $totalEntradas = 0;
        $totalSalidas = 0;
        $valorTotalEntradas = 0;
        $valorTotalSalidas = 0;

        foreach ($movimientos as $mov) {
            $entrada = 0;
            $salida = 0;
            $valorEntrada = 0;
            $valorSalida = 0;

            switch ($mov->tipo_movimiento) {
                case 'entrada':
                    $entrada = $mov->cantidad;
                    $valorEntrada = $mov->cantidad * $mov->costo_unitario;
                    $saldoActual += $entrada;
                    $totalEntradas += $entrada;
                    $valorTotalEntradas += $valorEntrada;
                    break;
                case 'salida':
                    $salida = $mov->cantidad;
                    $valorSalida = $mov->cantidad * $mov->costo_unitario;
                    $saldoActual -= $salida;
                    $totalSalidas += $salida;
                    $valorTotalSalidas += $valorSalida;
                    break;
                case 'transferencia':
                    // Lógica más compleja para transferencias si se especifica bodega
                    break;
                case 'ajuste':
                    if ($mov->cantidad > 0) {
                        $entrada = $mov->cantidad;
                        $valorEntrada = $mov->cantidad * $mov->costo_unitario;
                        $totalEntradas += $entrada;
                        $valorTotalEntradas += $valorEntrada;
                    } else {
                        $salida = abs($mov->cantidad);
                        $valorSalida = abs($mov->cantidad) * $mov->costo_unitario;
                        $totalSalidas += $salida;
                        $valorTotalSalidas += $valorSalida;
                    }
                    $saldoActual += $mov->cantidad;
                    break;
            }

            $kardex[] = [
                'fecha' => $mov->fecha,
                'movimiento_id' => $mov->movimiento_id,
                'tipo_movimiento' => ucfirst($mov->tipo_movimiento),
                'observaciones' => $mov->observaciones,
                'bodega_origen' => $mov->bodega_origen_nombre,
                'bodega_destino' => $mov->bodega_destino_nombre,
                'entrada' => $entrada,
                'salida' => $salida,
                'saldo' => $saldoActual,
                'costo_unitario' => $mov->costo_unitario,
                'valor_entrada' => $valorEntrada,
                'valor_salida' => $valorSalida
            ];
        }

        return [
            'movimientos' => $kardex,
            'resumen' => [
                'saldo_inicial' => $saldoInicial,
                'total_entradas' => $totalEntradas,
                'total_salidas' => $totalSalidas,
                'saldo_final' => $saldoActual,
                'valor_total_entradas' => $valorTotalEntradas,
                'valor_total_salidas' => $valorTotalSalidas,
                'total_movimientos' => count($kardex)
            ]
        ];
    }
}
