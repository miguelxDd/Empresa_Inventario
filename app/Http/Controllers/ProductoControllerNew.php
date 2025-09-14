<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Unidade;
use App\Models\Cuenta;
use App\Models\Existencia;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $productos = Producto::with(['categoria', 'unidade'])
                ->select(['id', 'sku', 'nombre', 'categoria_id', 'unidad_id', 'activo', 'precio_venta'])
                ->get();
            
            $response = [
                'data' => $productos->map(function ($producto) {
                    return [
                        'id' => $producto->id,
                        'codigo' => $producto->sku,
                        'nombre' => $producto->nombre,
                        'categoria_nombre' => $producto->categoria->nombre ?? 'Sin categoría',
                        'unidade_nombre' => $producto->unidade->nombre ?? 'Sin unidad',
                        'precio_formateado' => $producto->precio_venta ? '$' . number_format($producto->precio_venta, 2) : 'N/A',
                        'estado' => $producto->activo ? 'Activo' : 'Inactivo',
                        'activo' => $producto->activo
                    ];
                })
            ];
            
            return response()->json($response);
        }

        return view('productos.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = Categoria::where('activa', true)->orderBy('nombre')->get();
        $unidades = Unidade::where('activa', true)->orderBy('nombre')->get();
        $cuentas = Cuenta::where('activa', true)->orderBy('codigo')->get();

        return view('productos.create', compact('categorias', 'unidades', 'cuentas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductoRequest $request)
    {
        try {
            $producto = Producto::create($request->validated());

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Producto creado exitosamente',
                    'data' => $producto->load(['categoria', 'unidade'])
                ]);
            }

            return redirect()->route('productos.index')->with('success', 'Producto creado exitosamente');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el producto: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->withErrors(['error' => 'Error al crear el producto: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Producto $producto)
    {
        $producto->load(['categoria', 'unidade']);
        return view('productos.show', compact('producto'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $producto)
    {
        $categorias = Categoria::where('activa', true)->orderBy('nombre')->get();
        $unidades = Unidade::where('activa', true)->orderBy('nombre')->get();
        $cuentas = Cuenta::where('activa', true)->orderBy('codigo')->get();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'producto' => $producto,
                    'categorias' => $categorias,
                    'unidades' => $unidades,
                    'cuentas' => $cuentas
                ]
            ]);
        }

        return view('productos.edit', compact('producto', 'categorias', 'unidades', 'cuentas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductoRequest $request, Producto $producto)
    {
        try {
            $producto->update($request->validated());

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Producto actualizado exitosamente',
                    'data' => $producto->load(['categoria', 'unidade'])
                ]);
            }

            return redirect()->route('productos.index')->with('success', 'Producto actualizado exitosamente');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar el producto: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->withErrors(['error' => 'Error al actualizar el producto: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto)
    {
        try {
            $producto->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Producto eliminado exitosamente'
                ]);
            }

            return redirect()->route('productos.index')->with('success', 'Producto eliminado exitosamente');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el producto: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Error al eliminar el producto: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle product active status
     */
    public function toggle(Producto $producto): JsonResponse
    {
        try {
            $producto->activo = !$producto->activo;
            $producto->save();

            return response()->json([
                'success' => true,
                'message' => 'Estado del producto actualizado exitosamente',
                'activo' => $producto->activo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product stock by warehouses
     */
    public function existencias(Producto $producto): JsonResponse
    {
        try {
            $existencias = Existencia::with('bodega')
                ->where('producto_id', $producto->id)
                ->where('cantidad', '>', 0)
                ->get();

            $totalExistencias = $existencias->sum('cantidad');
            $valorTotalInventario = $existencias->sum(function ($existencia) {
                return $existencia->cantidad * $existencia->costo_promedio;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'producto' => $producto->only(['id', 'codigo', 'nombre']),
                    'existencias' => $existencias->map(function ($existencia) {
                        return [
                            'bodega_id' => $existencia->bodega_id,
                            'bodega_nombre' => $existencia->bodega->nombre,
                            'bodega_codigo' => $existencia->bodega->codigo,
                            'cantidad' => $existencia->cantidad,
                            'costo_promedio' => $existencia->costo_promedio,
                            'valor_total' => $existencia->cantidad * $existencia->costo_promedio,
                        ];
                    }),
                    'resumen' => [
                        'total_existencias' => $totalExistencias,
                        'valor_total_inventario' => $valorTotalInventario,
                        'bodegas_con_stock' => $existencias->count(),
                        'costo_promedio_general' => $totalExistencias > 0 ? $valorTotalInventario / $totalExistencias : 0
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las existencias: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get form options for AJAX
     */
    public function getFormOptions(): JsonResponse
    {
        try {
            $categorias = Categoria::where('activa', true)->orderBy('nombre')->get(['id', 'nombre']);
            $unidades = Unidade::where('activa', true)->orderBy('nombre')->get(['id', 'nombre', 'simbolo']);
            $cuentas = Cuenta::where('activa', true)->orderBy('codigo')->get(['id', 'codigo', 'nombre']);

            return response()->json([
                'success' => true,
                'data' => [
                    'categorias' => $categorias,
                    'unidades' => $unidades,
                    'cuentas' => $cuentas
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las opciones: ' . $e->getMessage()
            ], 500);
        }
    }

    // Métodos adicionales para mantener compatibilidad con la API existente
    public function porCategoria($categoriaId)
    {
        $productos = Producto::with(['categoria', 'unidade'])
            ->where('categoria_id', $categoriaId)
            ->where('activo', true)
            ->get();

        return response()->json(['productos' => $productos]);
    }

    public function estadisticas()
    {
        $stats = [
            'total_productos' => Producto::count(),
            'productos_activos' => Producto::where('activo', true)->count(),
            'sin_stock' => Producto::whereDoesntHave('existencias', function($query) {
                $query->where('cantidad', '>', 0);
            })->count(),
            'categorias_utilizadas' => Producto::distinct('categoria_id')->count('categoria_id')
        ];

        return response()->json(['estadisticas' => $stats]);
    }
}
