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
use Illuminate\Support\Facades\Log;

class ReporteController extends Controller
{
    public function index()
    {
        return view('reportes.index');
    }

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

            $saldoInicial = $this->calcularSaldoInicial(
                $request->producto_id, 
                $request->bodega_id, 
                $request->fecha_inicio
            );

            $movimientos = $this->obtenerMovimientosKardex(
                $request->producto_id,
                $request->bodega_id,
                $request->fecha_inicio,
                $request->fecha_fin
            );

            $kardexData = $this->calcularKardex($movimientos, $saldoInicial);

            return response()->json([
                'success' => true,
                'data' => [
                    'producto' => [
                        'id' => $producto->id,
                        'sku' => $producto->sku,
                        'nombre' => $producto->nombre,
                        'descripcion' => $producto->descripcion,
                        'categoria' => $producto->categoria?->nombre,
                        'unidad' => $producto->unidade?->nombre,
                    ],
                    'bodega' => $bodega ? [
                        'id' => $bodega->id,
                        'codigo' => $bodega->codigo,
                        'nombre' => $bodega->nombre,
                    ] : null,
                    'periodo' => [
                        'fecha_inicio' => $request->fecha_inicio,
                        'fecha_fin' => $request->fecha_fin,
                    ],
                    'saldo_inicial' => $saldoInicial,
                    'movimientos' => $kardexData['movimientos'],
                    'resumen' => $kardexData['resumen'],
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating kardex: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el kardex: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadKardexPDF(Request $request)
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

            $saldoInicial = $this->calcularSaldoInicial(
                $request->producto_id, 
                $request->bodega_id, 
                $request->fecha_inicio
            );

            $movimientos = $this->obtenerMovimientosKardex(
                $request->producto_id,
                $request->bodega_id,
                $request->fecha_inicio,
                $request->fecha_fin
            );

            $kardexData = $this->calcularKardex($movimientos, $saldoInicial);

            $data = [
                'producto' => [
                    'id' => $producto->id,
                    'sku' => $producto->sku,
                    'nombre' => $producto->nombre,
                    'descripcion' => $producto->descripcion,
                    'categoria' => $producto->categoria?->nombre,
                    'unidad' => $producto->unidade?->nombre,
                ],
                'bodega' => $bodega ? [
                    'id' => $bodega->id,
                    'codigo' => $bodega->codigo,
                    'nombre' => $bodega->nombre,
                ] : null,
                'periodo' => [
                    'fecha_inicio' => $request->fecha_inicio,
                    'fecha_fin' => $request->fecha_fin,
                ],
                'saldo_inicial' => $saldoInicial,
                'movimientos' => $kardexData['movimientos'],
                'resumen' => $kardexData['resumen'],
            ];

            return view('reportes.kardex-pdf', compact('data'));

        } catch (\Exception $e) {
            Log::error('Error generating kardex PDF: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportKardexCSV(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'bodega_id' => 'nullable|exists:bodegas,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        try {
            $producto = Producto::findOrFail($request->producto_id);
            $bodega = $request->bodega_id ? Bodega::findOrFail($request->bodega_id) : null;

            $saldoInicial = $this->calcularSaldoInicial(
                $request->producto_id, 
                $request->bodega_id, 
                $request->fecha_inicio
            );

            $movimientos = $this->obtenerMovimientosKardex(
                $request->producto_id,
                $request->bodega_id,
                $request->fecha_inicio,
                $request->fecha_fin
            );

            $kardexData = $this->calcularKardex($movimientos, $saldoInicial);

            $filename = 'kardex_' . $producto->sku . '_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($producto, $bodega, $request, $saldoInicial, $kardexData) {
                $file = fopen('php://output', 'w');
                
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                fputcsv($file, ['KARDEX DE PRODUCTO'], ';');
                fputcsv($file, ['Producto:', $producto->sku . ' - ' . $producto->nombre], ';');
                fputcsv($file, ['Generado:', now()->format('d/m/Y H:i:s')], ';');
                fputcsv($file, [], ';');
                
                fputcsv($file, [
                    'Fecha',
                    'Tipo',
                    'Observaciones',
                    'Entrada',
                    'Salida',
                    'Saldo'
                ], ';');
                
                fputcsv($file, [
                    '',
                    'SALDO INICIAL',
                    '',
                    '',
                    '',
                    number_format($saldoInicial, 2)
                ], ';');
                
                foreach ($kardexData['movimientos'] as $mov) {
                    $entrada = $mov['entrada'] > 0 ? number_format($mov['entrada'], 2) : '';
                    $salida = $mov['salida'] > 0 ? number_format($mov['salida'], 2) : '';
                    
                    fputcsv($file, [
                        $mov['fecha'],
                        $mov['tipo_movimiento'],
                        $mov['observaciones'] ?: '',
                        $entrada,
                        $salida,
                        number_format($mov['saldo'], 2)
                    ], ';');
                }
                
                fclose($file);
            };

            return Response::stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Error exporting kardex CSV: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar CSV: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getOptions(): JsonResponse
    {
        try {
            $totalProductos = Producto::where('activo', true)->count();
            $totalBodegas = Bodega::where('activa', true)->count();
            
            $valorInventario = DB::table('existencias as e')
                ->join('productos as p', 'e.producto_id', '=', 'p.id')
                ->where('p.activo', true)
                ->where('e.cantidad', '>', 0)
                ->sum(DB::raw('e.cantidad * e.costo_promedio'));
            
            $movimientosUltimoMes = DB::table('movimientos')
                ->whereNull('deleted_at')
                ->where('fecha', '>=', now()->subMonth())
                ->count();

            $productos = Producto::where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'sku as codigo', 'nombre']);
            
            $bodegas = Bodega::where('activa', true)
                ->orderBy('nombre')
                ->get(['id', 'codigo', 'nombre']);
            
            $categorias = Categoria::where('activa', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre']);

            return response()->json([
                'success' => true,
                'data' => [
                    'productos' => $productos,
                    'bodegas' => $bodegas,
                    'categorias' => $categorias,
                    'estadisticas' => [
                        'total_productos' => $totalProductos,
                        'total_bodegas' => $totalBodegas,
                        'valor_inventario' => $valorInventario,
                        'movimientos_ultimo_mes' => $movimientosUltimoMes,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting options: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar opciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show existencias report form
     */
    public function existencias()
    {
        $productos = Producto::where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'sku as codigo', 'nombre']);
        
        $bodegas = Bodega::where('activa', true)
            ->orderBy('nombre')
            ->get(['id', 'codigo', 'nombre']);

        $categorias = Categoria::where('activa', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return view('reportes.existencias', compact('productos', 'bodegas', 'categorias'));
    }

    /**
     * Get existencias data
     */
    public function getExistencias(Request $request): JsonResponse
    {
        try {
            $query = DB::table('existencias as e')
                ->join('productos as p', 'e.producto_id', '=', 'p.id')
                ->join('bodegas as b', 'e.bodega_id', '=', 'b.id')
                ->leftJoin('categorias as c', 'p.categoria_id', '=', 'c.id')
                ->leftJoin('unidades as u', 'p.unidad_id', '=', 'u.id')
                ->where('p.activo', true)
                ->where('b.activa', true)
                ->select([
                    'p.id as producto_id',
                    'p.sku',
                    'p.nombre as producto_nombre',
                    'p.descripcion',
                    'c.nombre as categoria',
                    'u.nombre as unidad',
                    'b.id as bodega_id',
                    'b.codigo as bodega_codigo',
                    'b.nombre as bodega_nombre',
                    'e.cantidad',
                    'e.costo_promedio',
                    'e.stock_minimo',
                    'e.stock_maximo',
                    DB::raw('(e.cantidad * e.costo_promedio) as valor_total'),
                    'e.updated_at'
                ]);

            // Filtros
            if ($request->filled('bodega_id')) {
                $query->where('e.bodega_id', $request->bodega_id);
            }

            if ($request->filled('categoria_id')) {
                $query->where('p.categoria_id', $request->categoria_id);
            }

            if ($request->filled('producto_id')) {
                $query->where('p.id', $request->producto_id);
            }

            if ($request->filled('solo_con_stock')) {
                $query->where('e.cantidad', '>', 0);
            }

            $existencias = $query->orderBy('p.nombre')
                ->orderBy('b.nombre')
                ->get();

            // Calcular totales
            $totalValor = $existencias->sum('valor_total');
            $totalCantidad = $existencias->sum('cantidad');
            $totalProductos = $existencias->groupBy('producto_id')->count();
            $totalBodegas = $existencias->groupBy('bodega_id')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'existencias' => $existencias,
                    'resumen' => [
                        'total_productos' => $totalProductos,
                        'total_bodegas' => $totalBodegas,
                        'total_cantidad' => $totalCantidad,
                        'valor_total_inventario' => $totalValor,
                        'total_registros' => $existencias->count()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting existencias: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar existencias: ' . $e->getMessage()
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
                ->where('p.activo', true)
                ->where('b.activa', true)
                ->select([
                    'p.sku',
                    'p.nombre as producto_nombre',
                    'p.descripcion',
                    'c.nombre as categoria',
                    'u.nombre as unidad',
                    'b.codigo as bodega_codigo',
                    'b.nombre as bodega_nombre',
                    'e.cantidad',
                    'e.costo_promedio',
                    'e.stock_minimo',
                    'e.stock_maximo',
                    DB::raw('(e.cantidad * e.costo_promedio) as valor_total')
                ]);

            // Aplicar filtros
            if ($request->filled('bodega_id')) {
                $query->where('e.bodega_id', $request->bodega_id);
            }

            if ($request->filled('categoria_id')) {
                $query->where('p.categoria_id', $request->categoria_id);
            }

            if ($request->filled('producto_id')) {
                $query->where('p.id', $request->producto_id);
            }

            if ($request->filled('solo_con_stock')) {
                $query->where('e.cantidad_disponible', '>', 0);
            }

            $existencias = $query->orderBy('p.nombre')
                ->orderBy('b.nombre')
                ->get();

            $filename = 'existencias_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($existencias) {
                $file = fopen('php://output', 'w');
                
                // BOM para UTF-8
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // Información del reporte
                fputcsv($file, ['REPORTE DE EXISTENCIAS'], ';');
                fputcsv($file, ['Fecha:', now()->format('d/m/Y H:i:s')], ';');
                fputcsv($file, [], ';'); // Línea vacía
                
                // Encabezados
                fputcsv($file, [
                    'SKU',
                    'Producto',
                    'Descripción',
                    'Categoría',
                    'Unidad',
                    'Código Bodega',
                    'Bodega',
                    'Cantidad',
                    'Costo Promedio',
                    'Stock Mínimo',
                    'Stock Máximo',
                    'Valor Total'
                ], ';');
                
                // Datos
                foreach ($existencias as $existencia) {
                    fputcsv($file, [
                        $existencia->sku,
                        $existencia->producto_nombre,
                        $existencia->descripcion ?: '',
                        $existencia->categoria ?: '',
                        $existencia->unidad ?: '',
                        $existencia->bodega_codigo,
                        $existencia->bodega_nombre,
                        number_format($existencia->cantidad, 2),
                        number_format($existencia->costo_promedio, 2),
                        number_format($existencia->stock_minimo, 2),
                        number_format($existencia->stock_maximo ?: 0, 2),
                        number_format($existencia->valor_total, 2)
                    ], ';');
                }
                
                // Totales
                $totalValor = $existencias->sum('valor_total');
                fputcsv($file, [], ';'); // Línea vacía
                fputcsv($file, ['TOTAL VALOR INVENTARIO:', number_format($totalValor, 2)], ';');
                
                fclose($file);
            };

            return Response::stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Error exporting existencias CSV: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar CSV: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show asientos report form
     */
    public function asientos()
    {
        return view('reportes.asientos');
    }

    /**
     * Get asientos data
     */
    public function getAsientos(Request $request): JsonResponse
    {
        try {
            $query = DB::table('asientos as a')
                ->leftJoin('movimientos as m', 'a.id', '=', 'm.asiento_id')
                ->select([
                    'a.id',
                    'a.fecha',
                    'a.numero',
                    'a.descripcion',
                    'a.total_debe',
                    'a.total_haber',
                    'a.estado',
                    'a.origen_tabla',
                    'a.origen_id',
                    DB::raw('COUNT(m.id) as movimientos_count')
                ])
                ->groupBy([
                    'a.id', 'a.fecha', 'a.numero', 'a.descripcion', 
                    'a.total_debe', 'a.total_haber', 'a.estado',
                    'a.origen_tabla', 'a.origen_id'
                ]);

            // Filtros
            if ($request->filled('fecha_inicio')) {
                $query->where('a.fecha', '>=', $request->fecha_inicio);
            }

            if ($request->filled('fecha_fin')) {
                $query->where('a.fecha', '<=', $request->fecha_fin);
            }

            if ($request->filled('estado')) {
                $query->where('a.estado', $request->estado);
            }

            $asientos = $query->orderBy('a.fecha', 'desc')
                ->orderBy('a.numero', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $asientos
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting asientos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar asientos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get asiento detail with movement information and accounting accounts
     */
    public function getAsientoDetalle(Request $request, $id): JsonResponse
    {
        try {
            // Obtener el asiento principal con información del movimiento
            $asiento = DB::table('asientos as a')
                ->leftJoin('movimientos as m', 'a.id', '=', 'm.asiento_id')
                ->leftJoin('bodegas as bo', 'm.bodega_origen_id', '=', 'bo.id')
                ->leftJoin('bodegas as bd', 'm.bodega_destino_id', '=', 'bd.id')
                ->where('a.id', $id)
                ->select([
                    'a.id',
                    'a.numero',
                    'a.fecha',
                    'a.descripcion',
                    'a.total_debe',
                    'a.total_haber',
                    'a.estado',
                    'a.origen_tabla',
                    'a.origen_id',
                    'm.id as movimiento_id',
                    'm.tipo as tipo_movimiento',
                    'm.bodega_origen_id',
                    'm.bodega_destino_id',
                    'bo.nombre as bodega_origen_nombre',
                    'bd.nombre as bodega_destino_nombre'
                ])
                ->first();

            if (!$asiento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asiento no encontrado'
                ], 404);
            }

            // Obtener los detalles contables (cuentas) del asiento
            $cuentas = DB::table('asientos_detalle as ad')
                ->join('cuentas as c', 'ad.cuenta_id', '=', 'c.id')
                ->where('ad.asiento_id', $id)
                ->select([
                    'ad.id',
                    'ad.debe',
                    'ad.haber',
                    'ad.concepto',
                    'c.id as cuenta_id',
                    'c.codigo as cuenta_codigo',
                    'c.nombre as cuenta_nombre',
                    'c.tipo as cuenta_tipo'
                ])
                ->orderBy('ad.id')
                ->get();

            // Obtener los detalles del movimiento de inventario si existe
            $productos = [];
            if ($asiento->movimiento_id) {
                $productos = DB::table('movimiento_detalles as md')
                    ->join('productos as p', 'md.producto_id', '=', 'p.id')
                    ->where('md.movimiento_id', $asiento->movimiento_id)
                    ->select([
                        'p.sku',
                        'p.nombre as producto_nombre',
                        'md.cantidad',
                        'md.costo_unitario',
                        DB::raw('(md.cantidad * md.costo_unitario) as total')
                    ])
                    ->get();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'asiento' => $asiento,
                    'cuentas' => $cuentas,
                    'productos' => $productos
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting asiento detail: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar detalle del asiento: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calcularSaldoInicial(int $productoId, ?int $bodegaId, string $fechaInicio): float
    {
        $query = DB::table('movimiento_detalles as md')
            ->join('movimientos as m', 'md.movimiento_id', '=', 'm.id')
            ->where('md.producto_id', $productoId)
            ->where('m.fecha', '<', $fechaInicio)
            ->whereNull('m.deleted_at');

        if ($bodegaId) {
            $query->where(function($q) use ($bodegaId) {
                $q->where('m.bodega_origen_id', $bodegaId)
                  ->orWhere('m.bodega_destino_id', $bodegaId);
            });
        }

        $movimientos = $query->get();
        $saldo = 0.0;

        foreach ($movimientos as $mov) {
            if ($mov->bodega_destino_id == $bodegaId || ($bodegaId === null && $mov->bodega_destino_id)) {
                $saldo += (float)$mov->cantidad;
            }
            
            if ($mov->bodega_origen_id == $bodegaId || ($bodegaId === null && $mov->bodega_origen_id)) {
                $saldo -= (float)$mov->cantidad;
            }
        }

        return $saldo;
    }

    private function obtenerMovimientosKardex(int $productoId, ?int $bodegaId, string $fechaInicio, string $fechaFin)
    {
        $query = DB::table('movimiento_detalles as md')
            ->join('movimientos as m', 'md.movimiento_id', '=', 'm.id')
            ->leftJoin('bodegas as bo', 'm.bodega_origen_id', '=', 'bo.id')
            ->leftJoin('bodegas as bd', 'm.bodega_destino_id', '=', 'bd.id')
            ->where('md.producto_id', $productoId)
            ->whereBetween('m.fecha', [$fechaInicio, $fechaFin])
            ->whereNull('m.deleted_at')
            ->select([
                'm.id as movimiento_id',
                'm.fecha',
                'm.tipo',
                'm.observaciones',
                'm.bodega_origen_id',
                'm.bodega_destino_id',
                'bo.nombre as bodega_origen_nombre',
                'bd.nombre as bodega_destino_nombre',
                'md.cantidad',
                'md.costo_unitario'
            ])
            ->orderBy('m.fecha')
            ->orderBy('m.id');

        if ($bodegaId) {
            $query->where(function($q) use ($bodegaId) {
                $q->where('m.bodega_origen_id', $bodegaId)
                  ->orWhere('m.bodega_destino_id', $bodegaId);
            });
        }

        return $query->get();
    }

    private function calcularKardex($movimientos, float $saldoInicial): array
    {
        $kardex = [];
        $saldoActual = $saldoInicial;
        $totalEntradas = 0.0;
        $totalSalidas = 0.0;
        $valorTotalEntradas = 0.0;
        $valorTotalSalidas = 0.0;

        foreach ($movimientos as $mov) {
            $cantidad = (float)$mov->cantidad;
            $costoUnitario = (float)$mov->costo_unitario;
            
            $entrada = 0.0;
            $salida = 0.0;
            $valorEntrada = 0.0;
            $valorSalida = 0.0;

            // Determinar si es entrada o salida según el tipo de movimiento
            if (in_array(strtolower($mov->tipo), ['entrada', 'ajuste_positivo', 'transferencia_entrada'])) {
                $entrada = $cantidad;
                $saldoActual += $cantidad;
                $totalEntradas += $cantidad;
                $valorEntrada = $cantidad * $costoUnitario;
                $valorTotalEntradas += $valorEntrada;
            } else {
                $salida = $cantidad;
                $saldoActual -= $cantidad;
                $totalSalidas += $cantidad;
                $valorSalida = $cantidad * $costoUnitario;
                $valorTotalSalidas += $valorSalida;
            }

            $kardex[] = [
                'fecha' => $mov->fecha,
                'tipo_movimiento' => ucfirst($mov->tipo),
                'observaciones' => $mov->observaciones,
                'bodega_origen' => $mov->bodega_origen_nombre,
                'bodega_destino' => $mov->bodega_destino_nombre,
                'entrada' => $entrada,
                'salida' => $salida,
                'saldo' => $saldoActual,
                'costo_unitario' => $costoUnitario,
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
