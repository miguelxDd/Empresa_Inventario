<?php

namespace App\Http\Controllers;

use App\Models\Bodega;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BodegaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $bodegas = Bodega::with(['responsable:id,name'])
                ->select(['id', 'codigo', 'nombre', 'ubicacion', 'activa', 'responsable_id', 'created_at', 'updated_at'])
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $bodegas
            ]);
        }

        return view('bodegas.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $usuarios = User::orderBy('name')->get(['id', 'name']);
        return view('bodegas.create', compact('usuarios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:50|unique:bodegas,codigo',
            'nombre' => 'required|string|max:255',
            'ubicacion' => 'nullable|string|max:500',
            'responsable_id' => 'nullable|exists:users,id',
            'activa' => 'required|in:true,false'
        ]);

        try {
            $bodega = Bodega::create([
                'codigo' => $request->codigo,
                'nombre' => $request->nombre,
                'ubicacion' => $request->ubicacion,
                'responsable_id' => $request->responsable_id,
                'activa' => filter_var($request->activa, FILTER_VALIDATE_BOOLEAN),
                'created_by' => $request->created_by ?? Auth::id() ?? 1
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bodega creada exitosamente',
                    'data' => $bodega->load('responsable:id,name')
                ]);
            }

            return redirect()->route('bodegas.index')->with('success', 'Bodega creada exitosamente');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear la bodega: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->withErrors(['error' => 'Error al crear la bodega: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Bodega $bodega)
    {
        $bodega->load(['responsable:id,name', 'existencias.producto:id,sku,nombre']);
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $bodega
            ]);
        }
        
        return view('bodegas.show', compact('bodega'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bodega $bodega)
    {
        $usuarios = User::orderBy('name')->get(['id', 'name']);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'bodega' => $bodega,
                    'usuarios' => $usuarios
                ]
            ]);
        }

        return view('bodegas.edit', compact('bodega', 'usuarios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bodega $bodega)
    {
        $request->validate([
            'codigo' => 'required|string|max:50|unique:bodegas,codigo,' . $bodega->id,
            'nombre' => 'required|string|max:255',
            'ubicacion' => 'nullable|string|max:500',
            'responsable_id' => 'nullable|exists:users,id',
            'activa' => 'required|in:true,false'
        ]);

        try {
            $bodega->update([
                'codigo' => $request->codigo,
                'nombre' => $request->nombre,
                'ubicacion' => $request->ubicacion,
                'responsable_id' => $request->responsable_id,
                'activa' => filter_var($request->activa, FILTER_VALIDATE_BOOLEAN),
                'updated_by' => Auth::id() ?? 1
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bodega actualizada exitosamente',
                    'data' => $bodega->load('responsable:id,name')
                ]);
            }

            return redirect()->route('bodegas.index')->with('success', 'Bodega actualizada exitosamente');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar la bodega: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->withErrors(['error' => 'Error al actualizar la bodega: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bodega $bodega)
    {
        try {
            // Verificar si tiene existencias antes de eliminar
            if ($bodega->existencias()->where('cantidad', '>', 0)->exists()) {
                $message = 'No se puede eliminar la bodega porque tiene existencias de productos';
                
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], 422);
                }

                return back()->withErrors(['error' => $message]);
            }

            $bodega->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bodega eliminada exitosamente'
                ]);
            }

            return redirect()->route('bodegas.index')->with('success', 'Bodega eliminada exitosamente');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar la bodega: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Error al eliminar la bodega: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle bodega active status
     */
    public function toggle(Bodega $bodega): JsonResponse
    {
        try {
            $bodega->activa = !$bodega->activa;
            $bodega->updated_by = Auth::id();
            $bodega->save();

            return response()->json([
                'success' => true,
                'message' => 'Estado de la bodega actualizado exitosamente',
                'activa' => $bodega->activa
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get warehouse inventory
     */
    public function inventario(Bodega $bodega): JsonResponse
    {
        try {
            $existencias = $bodega->existencias()
                ->with('producto:id,sku,nombre')
                ->where('cantidad', '>', 0)
                ->get();

            $totalProductos = $existencias->count();
            $valorTotalInventario = $existencias->sum(function ($existencia) {
                return $existencia->cantidad * $existencia->costo_promedio;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'bodega' => [
                        'id' => $bodega->id,
                        'codigo' => $bodega->codigo,
                        'nombre' => $bodega->nombre
                    ],
                    'existencias' => $existencias->map(function ($existencia) {
                        return [
                            'producto_id' => $existencia->producto_id,
                            'producto_codigo' => $existencia->producto->sku,
                            'producto_nombre' => $existencia->producto->nombre,
                            'cantidad' => $existencia->cantidad,
                            'costo_promedio' => $existencia->costo_promedio,
                            'valor_total' => $existencia->cantidad * $existencia->costo_promedio,
                        ];
                    }),
                    'resumen' => [
                        'total_productos' => $totalProductos,
                        'valor_total_inventario' => $valorTotalInventario
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el inventario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get options for dropdowns
     */
    public function getOptions(): JsonResponse
    {
        try {
            $usuarios = User::orderBy('name')->get(['id', 'name']);

            return response()->json([
                'success' => true,
                'data' => [
                    'usuarios' => $usuarios
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
     * Get data for DataTables
     */
    public function data(): JsonResponse
    {
        try {
            $bodegas = Bodega::with(['responsable:id,name'])
                ->select(['id', 'codigo', 'nombre', 'ubicacion', 'activa', 'responsable_id'])
                ->get();
            
            $data = $bodegas->map(function ($bodega) {
                $toggleClass = $bodega->activa ? 'btn-warning' : 'btn-success';
                $toggleIcon = $bodega->activa ? 'fas fa-pause' : 'fas fa-play';
                $toggleTitle = $bodega->activa ? 'Desactivar' : 'Activar';
                
                return [
                    'id' => $bodega->id,
                    'codigo' => $bodega->codigo,
                    'nombre' => $bodega->nombre,
                    'ubicacion' => $bodega->ubicacion ?? 'Sin ubicación',
                    'responsable' => $bodega->responsable->name ?? 'Sin responsable',
                    'estado' => $bodega->activa ? 
                        '<span class="badge bg-success">Activa</span>' : 
                        '<span class="badge bg-secondary">Inactiva</span>',
                    'activa' => $bodega->activa,
                    'acciones' => "
                        <div class=\"btn-group\" role=\"group\">
                            <button class=\"btn btn-sm btn-info\" onclick=\"verInventario({$bodega->id})\" title=\"Ver Inventario\">
                                <i class=\"fas fa-boxes\"></i>
                            </button>
                            <button class=\"btn btn-sm btn-primary\" onclick=\"editarBodega({$bodega->id})\" title=\"Editar\">
                                <i class=\"fas fa-edit\"></i>
                            </button>
                            <button class=\"btn btn-sm {$toggleClass}\" onclick=\"toggleEstado({$bodega->id})\" title=\"{$toggleTitle}\">
                                <i class=\"{$toggleIcon}\"></i>
                            </button>
                            <button class=\"btn btn-sm btn-danger\" onclick=\"eliminarBodega({$bodega->id})\" title=\"Eliminar\">
                                <i class=\"fas fa-trash\"></i>
                            </button>
                        </div>
                    "
                ];
            });

            return response()->json([
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics for dashboard
     */
    public function statistics(): JsonResponse
    {
        try {
            $total = Bodega::count();
            $activas = Bodega::where('activa', true)->count();
            $inactivas = $total - $activas;
            
            // Para inventario, podríamos contar las bodegas que tienen existencias
            $conInventario = Bodega::whereHas('existencias')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'activas' => $activas,
                    'inactivas' => $inactivas,
                    'con_inventario' => $conInventario
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users list for select options
     */
    public function getUsersList(): JsonResponse
    {
        try {
            $users = User::orderBy('name')->get(['id', 'name']);

            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la lista de usuarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics for bodegas
     */
    public function stats(): JsonResponse
    {
        try {
            $total = Bodega::count();
            $activas = Bodega::where('activa', true)->count();
            $inactivas = Bodega::where('activa', false)->count();
            
            // Bodegas con inventario (que tienen existencias)
            $conInventario = Bodega::whereHas('existencias')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'activas' => $activas,
                    'inactivas' => $inactivas,
                    'con_inventario' => $conInventario
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }
}
