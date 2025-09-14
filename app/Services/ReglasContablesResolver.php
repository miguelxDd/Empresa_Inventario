<?php

namespace App\Services;

use App\Models\ReglasContable;
use App\Models\Producto;
use Illuminate\Support\Facades\Cache;

/**
 * Resolver de reglas contables para movimientos de inventario
 * Maneja la prioridad y selección de cuentas según tipo, categoría y producto
 */
class ReglasContablesResolver
{
    /**
     * Resolver cuentas contables para un producto y tipo de movimiento
     * 
     * @param string $tipoMovimiento entrada|salida|ajuste|transferencia
     * @param Producto $producto
     * @return array|null ['cuenta_debe_id' => int, 'cuenta_haber_id' => int]
     */
    public function resolverCuentas(string $tipoMovimiento, Producto $producto): ?array
    {
        $cacheKey = "reglas_contables_{$tipoMovimiento}_{$producto->id}";
        
        return Cache::remember($cacheKey, 3600, function () use ($tipoMovimiento, $producto) {
            // Buscar reglas en orden de prioridad:
            // 1. Específica por producto
            // 2. Por categoría del producto
            // 3. General por tipo de movimiento
            
            $regla = ReglasContable::where('tipo_movimiento', $tipoMovimiento)
                ->where('activa', true)
                ->where(function ($query) use ($producto) {
                    $query->where('producto_id', $producto->id)
                        ->orWhere('categoria_producto_id', $producto->categoria_id)
                        ->orWhere(function ($q) {
                            $q->whereNull('producto_id')
                              ->whereNull('categoria_producto_id');
                        });
                })
                ->orderBy('prioridad', 'asc') // Menor prioridad = mayor precedencia
                ->orderByRaw('CASE 
                    WHEN producto_id IS NOT NULL THEN 1
                    WHEN categoria_producto_id IS NOT NULL THEN 2
                    ELSE 3
                END')
                ->first();

            if (!$regla) {
                return null;
            }

            return [
                'cuenta_debe_id' => $regla->cuenta_debe_id,
                'cuenta_haber_id' => $regla->cuenta_haber_id,
                'regla_id' => $regla->id
            ];
        });
    }

    /**
     * Obtener todas las reglas activas por tipo de movimiento
     */
    public function obtenerReglasPorTipo(string $tipoMovimiento): \Illuminate\Database\Eloquent\Collection
    {
        return ReglasContable::with(['cuentaDebe', 'cuentaHaber', 'producto', 'categoriaProducto'])
            ->where('tipo_movimiento', $tipoMovimiento)
            ->where('activa', true)
            ->orderBy('prioridad', 'asc')
            ->get();
    }

    /**
     * Validar que existan reglas para un conjunto de productos
     */
    public function validarReglasParaProductos(string $tipoMovimiento, array $productosIds): array
    {
        $productos = Producto::whereIn('id', $productosIds)->get();
        $faltantes = [];

        foreach ($productos as $producto) {
            $cuentas = $this->resolverCuentas($tipoMovimiento, $producto);
            if (!$cuentas) {
                $faltantes[] = [
                    'producto_id' => $producto->id,
                    'producto_nombre' => $producto->nombre,
                    'categoria_id' => $producto->categoria_id
                ];
            }
        }

        return $faltantes;
    }

    /**
     * Limpiar cache de reglas contables
     */
    public function limpiarCache(): void
    {
        $tipos = ['entrada', 'salida', 'ajuste', 'transferencia'];
        $productos = Producto::pluck('id');

        foreach ($tipos as $tipo) {
            foreach ($productos as $productoId) {
                Cache::forget("reglas_contables_{$tipo}_{$productoId}");
            }
        }
    }
}
