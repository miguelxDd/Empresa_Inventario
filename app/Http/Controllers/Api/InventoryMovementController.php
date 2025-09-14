<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InventoryMovementService;
use App\Exceptions\InventoryException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class InventoryMovementController extends Controller
{
    private InventoryMovementService $inventoryService;

    public function __construct(InventoryMovementService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Create and post a new inventory movement
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createAndPost(Request $request): JsonResponse
    {
        try {
            // Validación básica de Laravel
            $validated = $request->validate([
                'fecha' => 'required|date',
                'tipo' => 'required|string|in:entrada,salida,ajuste,transferencia',
                'referencia' => 'nullable|string|max:100',
                'observaciones' => 'nullable|string|max:500',
                'bodega_origen_id' => [
                    'nullable',
                    'integer',
                    'exists:bodegas,id',
                    function ($attribute, $value, $fail) {
                        if ($value && !\App\Models\Bodega::where('id', $value)->where('activa', true)->exists()) {
                            $fail('La bodega origen debe estar activa.');
                        }
                    }
                ],
                'bodega_destino_id' => [
                    'nullable',
                    'integer', 
                    'exists:bodegas,id',
                    function ($attribute, $value, $fail) {
                        if ($value && !\App\Models\Bodega::where('id', $value)->where('activa', true)->exists()) {
                            $fail('La bodega destino debe estar activa.');
                        }
                    }
                ],
                'detalles' => 'required|array|min:1',
                'detalles.*.producto_id' => 'required|integer|exists:productos,id',
                'detalles.*.cantidad' => 'required|numeric|min:0.001',
                'detalles.*.costo_unitario' => 'nullable|numeric|min:0',
            ]);

            // Validaciones lógicas adicionales
            $tipo = $validated['tipo'];
            $bodegaOrigen = $validated['bodega_origen_id'] ?? null;
            $bodegaDestino = $validated['bodega_destino_id'] ?? null;

            // Validar requisitos según tipo de movimiento
            switch ($tipo) {
                case 'entrada':
                    if (!$bodegaDestino) {
                        throw new \Illuminate\Validation\ValidationException(validator([], []), [
                            'bodega_destino_id' => ['La bodega destino es obligatoria para movimientos de entrada.']
                        ]);
                    }
                    break;
                
                case 'salida':
                    if (!$bodegaOrigen) {
                        throw new \Illuminate\Validation\ValidationException(validator([], []), [
                            'bodega_origen_id' => ['La bodega origen es obligatoria para movimientos de salida.']
                        ]);
                    }
                    break;
                
                case 'transferencia':
                    if (!$bodegaOrigen || !$bodegaDestino) {
                        $errors = [];
                        if (!$bodegaOrigen) $errors['bodega_origen_id'] = ['La bodega origen es obligatoria para transferencias.'];
                        if (!$bodegaDestino) $errors['bodega_destino_id'] = ['La bodega destino es obligatoria para transferencias.'];
                        throw new \Illuminate\Validation\ValidationException(validator([], []), $errors);
                    }
                    if ($bodegaOrigen === $bodegaDestino) {
                        throw new \Illuminate\Validation\ValidationException(validator([], []), [
                            'bodega_destino_id' => ['La bodega destino debe ser diferente a la bodega origen.']
                        ]);
                    }
                    break;
                
                case 'ajuste':
                    if (!$bodegaDestino) {
                        throw new \Illuminate\Validation\ValidationException(validator([], []), [
                            'bodega_destino_id' => ['La bodega destino es obligatoria para ajustes de inventario.']
                        ]);
                    }
                    break;
            }

            // Agregar usuario autenticado
            $validated['created_by'] = Auth::id() ?? 1; // Fallback para testing

            // Crear y procesar movimiento
            $result = $this->inventoryService->createAndPost($validated);

            return response()->json([
                'success' => true,
                'message' => 'Movimiento de inventario procesado exitosamente',
                'data' => $result->toArray()
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (InventoryException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Get movement by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $movimiento = $this->inventoryService->getMovementById($id);

            if (!$movimiento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Movimiento no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $movimiento->toArray()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el movimiento',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Cancel a movement
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        try {
            $userId = Auth::id() ?? 1; // Fallback para testing
            
            $movimiento = $this->inventoryService->cancelMovement($id, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Movimiento cancelado exitosamente',
                'data' => $movimiento->toArray()
            ]);

        } catch (InventoryException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar el movimiento',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Get available movement types and states
     *
     * @return JsonResponse
     */
    public function getOptions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'tipos_movimiento' => InventoryMovementService::getValidMovementTypes(),
                'estados' => InventoryMovementService::getValidMovementStates(),
            ]
        ]);
    }

    /**
     * Example payloads for testing
     *
     * @return JsonResponse
     */
    public function getExamples(): JsonResponse
    {
        $examples = [
            'entrada' => [
                'fecha' => now()->format('Y-m-d'),
                'tipo' => 'entrada',
                'referencia' => 'COMPRA-001',
                'observaciones' => 'Compra de mercadería',
                'bodega_destino_id' => 1,
                'detalles' => [
                    [
                        'producto_id' => 1,
                        'cantidad' => 100,
                        'costo_unitario' => 1500.00
                    ],
                    [
                        'producto_id' => 2,
                        'cantidad' => 50,
                        'costo_unitario' => 800.00
                    ]
                ]
            ],
            'salida' => [
                'fecha' => now()->format('Y-m-d'),
                'tipo' => 'salida',
                'referencia' => 'VENTA-001',
                'observaciones' => 'Venta a cliente',
                'bodega_origen_id' => 1,
                'detalles' => [
                    [
                        'producto_id' => 1,
                        'cantidad' => 10
                    ]
                ]
            ],
            'transferencia' => [
                'fecha' => now()->format('Y-m-d'),
                'tipo' => 'transferencia',
                'referencia' => 'TRANS-001',
                'observaciones' => 'Transferencia entre bodegas',
                'bodega_origen_id' => 1,
                'bodega_destino_id' => 2,
                'detalles' => [
                    [
                        'producto_id' => 1,
                        'cantidad' => 25
                    ]
                ]
            ],
            'ajuste' => [
                'fecha' => now()->format('Y-m-d'),
                'tipo' => 'ajuste',
                'referencia' => 'INV-001',
                'observaciones' => 'Ajuste por inventario físico',
                'bodega_destino_id' => 1,
                'detalles' => [
                    [
                        'producto_id' => 1,
                        'cantidad' => 5,
                        'costo_unitario' => 1500.00
                    ]
                ]
            ]
        ];

        return response()->json([
            'success' => true,
            'message' => 'Ejemplos de payloads para movimientos de inventario',
            'data' => $examples
        ]);
    }
}
