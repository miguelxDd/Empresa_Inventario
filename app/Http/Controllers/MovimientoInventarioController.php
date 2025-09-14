<?php

namespace App\Http\Controllers;

use App\Models\Bodega;
use App\Models\Producto;
use App\Models\Movimiento;
// use App\Services\InventoryMovementService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MovimientoInventarioController extends Controller
{
    // protected $inventoryService;

    // public function __construct(InventoryMovementService $inventoryService)
    // {
    //     $this->inventoryService = $inventoryService;
    // }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('movimientos.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $bodegas = Bodega::where('activa', true)->orderBy('nombre')->get();
        $productos = Producto::with(['categoria', 'unidade'])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('movimientos.create', compact('bodegas', 'productos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Debug: registrar los datos recibidos
        Log::info('Datos recibidos en store:', $request->all());
        
        // Procesar y normalizar las líneas si vienen con índices numéricos
        if ($request->has('lineas') && is_array($request->lineas)) {
            $lineasNormalizadas = [];
            foreach ($request->lineas as $key => $linea) {
                if (is_array($linea) && isset($linea['producto_id'])) {
                    $lineasNormalizadas[] = [
                        'producto_id' => $linea['producto_id'],
                        'cantidad' => $linea['cantidad'],
                        'costo_unitario' => $linea['costo_unitario'] ?? null,
                    ];
                }
            }
            $request->merge(['lineas' => $lineasNormalizadas]);
        }
        
        $request->validate([
            'tipo_movimiento' => 'required|in:entrada,salida,ajuste,transferencia',
            'bodega_origen_id' => 'required_if:tipo_movimiento,salida,transferencia|nullable|exists:bodegas,id',
            'bodega_destino_id' => 'required_if:tipo_movimiento,entrada,transferencia|nullable|exists:bodegas,id',
            'observaciones' => 'nullable|string|max:500',
            'lineas' => 'required|array|min:1',
            'lineas.*.producto_id' => 'required|exists:productos,id',
            'lineas.*.cantidad' => 'required|numeric|min:0.01',
            'lineas.*.costo_unitario' => 'required_if:tipo_movimiento,entrada,ajuste|numeric|min:0',
        ], [
            'tipo_movimiento.required' => 'El tipo de movimiento es obligatorio',
            'tipo_movimiento.in' => 'El tipo de movimiento debe ser: entrada, salida, ajuste o transferencia',
            'bodega_origen_id.required_if' => 'La bodega origen es requerida para salidas y transferencias',
            'bodega_destino_id.required_if' => 'La bodega destino es requerida para entradas y transferencias',
            'lineas.required' => 'Debe agregar al menos una línea de productos',
            'lineas.min' => 'Debe agregar al menos una línea de productos',
            'lineas.*.producto_id.required' => 'El producto es obligatorio en cada línea',
            'lineas.*.producto_id.exists' => 'El producto seleccionado no existe',
            'lineas.*.cantidad.required' => 'La cantidad es obligatoria en cada línea',
            'lineas.*.cantidad.min' => 'La cantidad debe ser mayor a 0',
            'lineas.*.costo_unitario.required_if' => 'El costo unitario es obligatorio para entradas y ajustes',
            'lineas.*.costo_unitario.min' => 'El costo unitario debe ser mayor o igual a 0',
        ]);

        DB::beginTransaction();
        try {
            // Preparar datos para el servicio
            $movementData = [
                'tipo_movimiento' => $request->tipo_movimiento,
                'bodega_origen_id' => $request->bodega_origen_id,
                'bodega_destino_id' => $request->bodega_destino_id,
                'observaciones' => $request->observaciones,
                'lineas' => collect($request->lineas)->map(function ($linea) use ($request) {
                    $lineaData = [
                        'producto_id' => $linea['producto_id'],
                        'cantidad' => $linea['cantidad'],
                    ];
                    
                    // Solo incluir costo unitario para entradas y ajustes
                    if (in_array($request->tipo_movimiento, ['entrada', 'ajuste'])) {
                        $lineaData['costo_unitario'] = $linea['costo_unitario'];
                    }
                    
                    return $lineaData;
                })->toArray()
            ];

            // Crear y contabilizar el movimiento
            // Calcular el valor total del movimiento
            $valorTotal = 0;
            foreach ($request->lineas as $linea) {
                if (isset($linea['costo_unitario']) && isset($linea['cantidad'])) {
                    $valorTotal += $linea['cantidad'] * $linea['costo_unitario'];
                }
            }

            // Crear el asiento contable
            $numeroAsiento = 'AS-' . date('Y') . '-' . str_pad(random_int(1, 999), 3, '0', STR_PAD_LEFT);
            $asientoId = DB::table('asientos')->insertGetId([
                'fecha' => now()->format('Y-m-d'),
                'numero' => $numeroAsiento,
                'descripcion' => "Movimiento de inventario - {$request->tipo_movimiento} - {$request->observaciones}",
                'origen_tabla' => 'movimientos',
                'origen_id' => null, // Se actualizará después
                'estado' => 'confirmado',
                'total_debe' => $valorTotal,
                'total_haber' => $valorTotal,
                'created_by' => 1, // Usuario temporal
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Crear el movimiento en la base de datos
            $movimiento = DB::table('movimientos')->insertGetId([
                'fecha' => now()->format('Y-m-d'),
                'tipo' => $request->tipo_movimiento,
                'bodega_origen_id' => $request->bodega_origen_id,
                'bodega_destino_id' => $request->bodega_destino_id,
                'estado' => 'confirmado',
                'referencia' => 'MOV-' . date('Y') . '-' . str_pad(random_int(1, 999), 3, '0', STR_PAD_LEFT),
                'observaciones' => $request->observaciones,
                'valor_total' => $valorTotal,
                'asiento_id' => $asientoId,
                'created_by' => 1, // Usuario temporal
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Actualizar el asiento con la referencia al movimiento
            DB::table('asientos')->where('id', $asientoId)->update([
                'origen_id' => $movimiento,
                'updated_at' => now(),
            ]);

            // Crear las líneas de detalle del movimiento
            foreach ($request->lineas as $linea) {
                $costoUnitario = $linea['costo_unitario'] ?? 0;
                $total = $linea['cantidad'] * $costoUnitario;
                
                DB::table('movimiento_detalles')->insert([
                    'movimiento_id' => $movimiento,
                    'producto_id' => $linea['producto_id'],
                    'cantidad' => $linea['cantidad'],
                    'costo_unitario' => $costoUnitario,
                    'total' => $total,
                    'observaciones' => $linea['observaciones'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Crear objeto movimiento para actualizar existencias
            $movimientoObj = (object) [
                'id' => $movimiento,
                'tipo' => $request->tipo_movimiento,
                'bodega_origen_id' => $request->bodega_origen_id,
                'bodega_destino_id' => $request->bodega_destino_id,
            ];

            // Actualizar existencias según el tipo de movimiento
            $this->actualizarExistencias($movimientoObj, $request->lineas);

            $resultado = [
                'movimiento' => (object) [
                    'id' => $movimiento,
                    'tipo_movimiento' => $request->tipo_movimiento
                ],
                'asiento' => (object) [
                    'id' => $asientoId,
                    'numero' => $numeroAsiento
                ]
            ];

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Movimiento de inventario creado exitosamente (modo desarrollo)',
                    'data' => [
                        'movimiento_id' => $resultado['movimiento']->id,
                        'asiento_numero' => $resultado['asiento']->numero,
                        'asiento_id' => $resultado['asiento']->id,
                        'tipo_movimiento' => $resultado['movimiento']->tipo_movimiento,
                    ]
                ]);
            }

            return redirect()->route('movimientos.index')
                ->with('success', "Movimiento creado exitosamente. Asiento contable Nº {$resultado['asiento']->numero} generado.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear movimiento de inventario: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el movimiento: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()
                ->withErrors(['error' => 'Error al crear el movimiento: ' . $e->getMessage()]);
        }
    }

    /**
     * Get form options for AJAX
     */
    public function getFormOptions(): JsonResponse
    {
        try {
            $bodegas = Bodega::where('activa', true)
                ->orderBy('nombre')
                ->get(['id', 'codigo', 'nombre']);
            
            $productos = Producto::with(['categoria', 'unidade'])
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'sku', 'nombre', 'categoria_id', 'unidad_id', 'precio_compra_promedio', 'precio_venta']);

            return response()->json([
                'success' => true,
                'data' => [
                    'bodegas' => $bodegas,
                    'productos' => $productos->map(function ($producto) {
                        return [
                            'id' => $producto->id,
                            'codigo' => $producto->sku,
                            'nombre' => $producto->nombre,
                            'categoria_nombre' => $producto->categoria->nombre ?? 'Sin categoría',
                            'unidade_nombre' => $producto->unidade->nombre ?? 'Sin unidad',
                            'unidade_simbolo' => $producto->unidade->abreviatura ?? '',
                            'precio_compra' => $producto->precio_compra_promedio,
                            'precio_venta' => $producto->precio_venta,
                            'texto_completo' => "{$producto->sku} - {$producto->nombre}"
                        ];
                    })
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
     * Get product cost for AJAX
     */
    public function getProductCost(Request $request): JsonResponse
    {
        try {
            $producto = Producto::findOrFail($request->producto_id);
            $bodegaId = $request->bodega_id;

            $costoPromedio = 0;
            $stockDisponible = 0;

            if ($bodegaId) {
                $existencia = $producto->existencias()
                    ->where('bodega_id', $bodegaId)
                    ->first();
                
                if ($existencia) {
                    $costoPromedio = $existencia->costo_promedio;
                    $stockDisponible = $existencia->cantidad;
                }
            }

            // Si no hay costo promedio, usar precio de compra del producto
            if (!$costoPromedio && $producto->precio_compra) {
                $costoPromedio = $producto->precio_compra;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'costo_promedio' => $costoPromedio,
                    'stock_disponible' => $stockDisponible,
                    'unidade_simbolo' => $producto->unidade->simbolo ?? '',
                    'precio_compra' => $producto->precio_compra,
                    'precio_venta' => $producto->precio_venta
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el costo del producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get movements history for AJAX DataTable
     */
    public function getMovements(Request $request): JsonResponse
    {
        try {
            $movimientos = DB::table('movimientos as mi')
                ->leftJoin('bodegas as bo', 'mi.bodega_origen_id', '=', 'bo.id')
                ->leftJoin('bodegas as bd', 'mi.bodega_destino_id', '=', 'bd.id')
                ->leftJoin('asientos as ac', 'mi.asiento_id', '=', 'ac.id')
                ->select([
                    'mi.id',
                    'mi.tipo',
                    'mi.fecha',
                    'mi.observaciones',
                    'mi.estado',
                    'mi.referencia',
                    'mi.numero_documento',
                    'bo.nombre as bodega_origen',
                    'bd.nombre as bodega_destino',
                    'ac.numero as asiento_numero',
                    'mi.created_at'
                ])
                ->orderBy('mi.created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $movimientos->map(function ($movimiento) {
                    return [
                        'id' => $movimiento->id,
                        'tipo_movimiento' => ucfirst($movimiento->tipo),
                        'fecha' => date('d/m/Y', strtotime($movimiento->fecha)),
                        'estado' => ucfirst($movimiento->estado),
                        'referencia' => $movimiento->referencia ?? 'N/A',
                        'numero_documento' => $movimiento->numero_documento ?? 'N/A',
                        'bodega_origen' => $movimiento->bodega_origen ?? 'N/A',
                        'bodega_destino' => $movimiento->bodega_destino ?? 'N/A',
                        'asiento_numero' => $movimiento->asiento_numero ?? 'N/A',
                        'observaciones' => $movimiento->observaciones ?? '',
                        'created_at' => date('d/m/Y H:i', strtotime($movimiento->created_at))
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los movimientos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirmar un movimiento de inventario
     * POST /inventarios/movimientos/{id}/confirmar
     */
    public function confirmar(Request $request, $id): JsonResponse
    {
        try {
            $movimiento = Movimiento::with('detalles.producto')->findOrFail($id);

            if ($movimiento->estado !== 'borrador') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden confirmar movimientos en estado borrador'
                ], 400);
            }

            // El observer se encargará de procesar contablemente
            $movimiento->update(['estado' => 'confirmado']);

            return response()->json([
                'success' => true,
                'message' => 'Movimiento confirmado exitosamente',
                'data' => [
                    'movimiento_id' => $movimiento->id,
                    'asiento_id' => $movimiento->asiento_id,
                    'estado' => $movimiento->estado
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al confirmar movimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza las existencias según el tipo de movimiento
     */
    private function actualizarExistencias($movimiento, $detalles)
    {
        foreach ($detalles as $detalle) {
            $producto_id = $detalle['producto_id'];
            $cantidad = $detalle['cantidad'];

            switch ($movimiento->tipo) {
                case 'entrada':
                    // Aumentar stock en bodega destino
                    $this->actualizarStockBodega($producto_id, $movimiento->bodega_destino_id, $cantidad, 'aumentar');
                    break;

                case 'salida':
                    // Disminuir stock en bodega origen
                    $this->actualizarStockBodega($producto_id, $movimiento->bodega_origen_id, $cantidad, 'disminuir');
                    break;

                case 'transferencia':
                    // Disminuir stock en bodega origen
                    $this->actualizarStockBodega($producto_id, $movimiento->bodega_origen_id, $cantidad, 'disminuir');
                    // Aumentar stock en bodega destino
                    $this->actualizarStockBodega($producto_id, $movimiento->bodega_destino_id, $cantidad, 'aumentar');
                    break;

                case 'ajuste':
                    // Para ajustes, la cantidad puede ser positiva o negativa
                    $operacion = $cantidad >= 0 ? 'aumentar' : 'disminuir';
                    $cantidad_abs = abs($cantidad);
                    $bodega_id = $movimiento->bodega_destino_id ?: $movimiento->bodega_origen_id;
                    $this->actualizarStockBodega($producto_id, $bodega_id, $cantidad_abs, $operacion);
                    break;
            }
        }
    }

    /**
     * Actualiza el stock de un producto en una bodega específica
     */
    private function actualizarStockBodega($producto_id, $bodega_id, $cantidad, $operacion)
    {
        // Verificar si existe la existencia
        $existencia = \App\Models\Existencia::where('producto_id', $producto_id)
            ->where('bodega_id', $bodega_id)
            ->first();

        if (!$existencia) {
            // Crear nueva existencia
            $existencia = new \App\Models\Existencia();
            $existencia->producto_id = $producto_id;
            $existencia->bodega_id = $bodega_id;
            $existencia->cantidad = 0;
            $existencia->costo_promedio = 0;
            $existencia->save();
        }

        // Actualizar cantidad
        if ($operacion === 'aumentar') {
            $nuevaCantidad = $existencia->cantidad + $cantidad;
        } else {
            $nuevaCantidad = $existencia->cantidad - $cantidad;
            // Evitar cantidades negativas
            if ($nuevaCantidad < 0) {
                $nuevaCantidad = 0;
            }
        }

        // Actualizar usando consulta directa para evitar problemas con clave primaria compuesta
        \App\Models\Existencia::where('producto_id', $producto_id)
            ->where('bodega_id', $bodega_id)
            ->update(['cantidad' => $nuevaCantidad]);
    }
}
