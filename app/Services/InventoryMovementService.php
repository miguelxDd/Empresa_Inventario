<?php

namespace App\Services;

use App\DTOs\InventoryMovementResult;
use App\Exceptions\InventoryException;
use App\Models\Movimiento;
use App\Models\MovimientoDetalle;
use App\Models\Asiento;
use App\Models\Existencia;
use App\Models\Producto;
use App\Models\Bodega;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use PDOException;

class InventoryMovementService
{
    private const VALID_MOVEMENT_TYPES = ['entrada', 'salida', 'ajuste', 'transferencia'];
    private const MOVEMENT_STATES = ['borrador', 'confirmado', 'anulado'];

    /**
     * Create and post an inventory movement with full transactional flow
     *
     * @param array $payload
     * @return InventoryMovementResult
     * @throws InventoryException
     */
    public function createAndPost(array $payload): InventoryMovementResult
    {
        // Validar payload
        $this->validatePayload($payload);

        try {
            return DB::transaction(function () use ($payload) {
                // 1. Crear movimiento en estado borrador
                $movimiento = $this->createMovement($payload);

                // 2. Insertar detalles del movimiento (triggers se ejecutarán automáticamente)
                $detalles = $this->createMovementDetails($movimiento, $payload['detalles']);

                // 3. Refrescar movimiento para obtener valor_total calculado por trigger
                $movimiento->refresh();

                // 4. Cambiar estado a confirmado
                $this->confirmMovement($movimiento);

                // 5. Generar asiento contable
                $asiento = $this->generateAccountingEntry($movimiento);

                // 6. Obtener existencias afectadas
                $existenciasAfectadas = $this->getAffectedStock($movimiento);

                return new InventoryMovementResult(
                    $movimiento->load(['bodegaOrigen', 'bodegaDestino', 'creadoPor']),
                    $asiento,
                    $existenciasAfectadas,
                    $detalles->load('producto')
                );
            });
        } catch (PDOException $e) {
            // Capturar errores SQL específicos de inventario (SQLSTATE 45000)
            if (str_contains($e->getCode(), '45000') || str_contains($e->getMessage(), 'SQLSTATE[45000]')) {
                throw InventoryException::fromSqlError($e->getMessage());
            }
            
            Log::error('Error en movimiento de inventario', [
                'payload' => $payload,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            
            throw new InventoryException('Error inesperado al procesar el movimiento de inventario: ' . $e->getMessage());
        } catch (InventoryException $e) {
            // Re-lanzar excepciones de inventario
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error general en movimiento de inventario', [
                'payload' => $payload,
                'error' => $e->getMessage()
            ]);
            
            throw new InventoryException('Error inesperado: ' . $e->getMessage());
        }
    }

    /**
     * Validate the movement payload
     *
     * @param array $payload
     * @throws InventoryException
     */
    private function validatePayload(array $payload): void
    {
        $errors = [];

        // Validar fecha obligatoria
        if (empty($payload['fecha'])) {
            $errors[] = 'La fecha es obligatoria';
        } else {
            try {
                Carbon::parse($payload['fecha']);
            } catch (\Exception $e) {
                $errors[] = 'Formato de fecha inválido';
            }
        }

        // Validar tipo de movimiento
        if (empty($payload['tipo'])) {
            $errors[] = 'El tipo de movimiento es obligatorio';
        } elseif (!in_array($payload['tipo'], self::VALID_MOVEMENT_TYPES)) {
            throw InventoryException::invalidMovementType($payload['tipo']);
        }

        // Validar bodegas según tipo de movimiento
        $this->validateWarehouses($payload, $errors);

        // Validar detalles (líneas)
        if (empty($payload['detalles']) || !is_array($payload['detalles']) || count($payload['detalles']) === 0) {
            $errors[] = 'Debe incluir al menos una línea de detalle';
        } else {
            $this->validateDetails($payload['detalles'], $errors);
        }

        // Validar usuario creador
        if (empty($payload['created_by'])) {
            $errors[] = 'El usuario creador es obligatorio';
        }

        if (!empty($errors)) {
            throw InventoryException::validationFailed($errors);
        }
    }

    /**
     * Validate warehouses based on movement type
     *
     * @param array $payload
     * @param array &$errors
     */
    private function validateWarehouses(array $payload, array &$errors): void
    {
        $tipo = $payload['tipo'];

        switch ($tipo) {
            case 'entrada':
            case 'ajuste':
                if (empty($payload['bodega_destino_id'])) {
                    throw InventoryException::missingWarehouse("movimientos de {$tipo}");
                }
                break;

            case 'salida':
                if (empty($payload['bodega_origen_id'])) {
                    throw InventoryException::missingWarehouse("movimientos de {$tipo}");
                }
                break;

            case 'transferencia':
                if (empty($payload['bodega_origen_id'])) {
                    throw InventoryException::missingWarehouse("bodega origen en transferencias");
                }
                if (empty($payload['bodega_destino_id'])) {
                    throw InventoryException::missingWarehouse("bodega destino en transferencias");
                }
                if ($payload['bodega_origen_id'] === $payload['bodega_destino_id']) {
                    $errors[] = 'La bodega origen debe ser diferente a la bodega destino en transferencias';
                }
                break;
        }

        // Verificar que las bodegas existan
        if (!empty($payload['bodega_origen_id']) && !Bodega::find($payload['bodega_origen_id'])) {
            $errors[] = 'La bodega origen no existe';
        }
        if (!empty($payload['bodega_destino_id']) && !Bodega::find($payload['bodega_destino_id'])) {
            $errors[] = 'La bodega destino no existe';
        }
    }

    /**
     * Validate movement details
     *
     * @param array $detalles
     * @param array &$errors
     */
    private function validateDetails(array $detalles, array &$errors): void
    {
        foreach ($detalles as $index => $detalle) {
            $lineNumber = $index + 1;

            if (empty($detalle['producto_id'])) {
                $errors[] = "Línea {$lineNumber}: El producto es obligatorio";
            } else {
                $producto = Producto::find($detalle['producto_id']);
                if (!$producto) {
                    $errors[] = "Línea {$lineNumber}: El producto no existe";
                } elseif (!$producto->activo) {
                    $errors[] = "Línea {$lineNumber}: El producto '{$producto->nombre}' está inactivo";
                }
            }

            if (empty($detalle['cantidad']) || $detalle['cantidad'] <= 0) {
                $errors[] = "Línea {$lineNumber}: La cantidad debe ser mayor a cero";
            }

            // Para movimientos de entrada y ajuste, validar costo unitario
            if (isset($detalle['costo_unitario']) && $detalle['costo_unitario'] < 0) {
                $errors[] = "Línea {$lineNumber}: El costo unitario no puede ser negativo";
            }
        }
    }

    /**
     * Create the movement record
     *
     * @param array $payload
     * @return Movimiento
     */
    private function createMovement(array $payload): Movimiento
    {
        $movimientoData = [
            'fecha' => Carbon::parse($payload['fecha']),
            'tipo' => $payload['tipo'],
            'estado' => 'borrador',
            'referencia' => $payload['referencia'] ?? null,
            'observaciones' => $payload['observaciones'] ?? null,
            'bodega_origen_id' => $payload['bodega_origen_id'] ?? null,
            'bodega_destino_id' => $payload['bodega_destino_id'] ?? null,
            'valor_total' => 0.00, // Se calculará por trigger
            'created_by' => $payload['created_by'],
            'updated_by' => $payload['created_by'],
        ];

        $movimiento = Movimiento::create($movimientoData);

        // Generar número de movimiento
        $numero = $this->generateMovementNumber($movimiento);
        $movimiento->update(['numero' => $numero]);

        return $movimiento;
    }

    /**
     * Generate movement number
     *
     * @param Movimiento $movimiento
     * @return string
     */
    private function generateMovementNumber(Movimiento $movimiento): string
    {
        $tipo = strtoupper($movimiento->tipo);
        $fecha = $movimiento->fecha->format('Ymd');
        $id = str_pad($movimiento->id, 6, '0', STR_PAD_LEFT);
        
        return "{$tipo}-{$fecha}-{$id}";
    }

    /**
     * Create movement details
     *
     * @param Movimiento $movimiento
     * @param array $detalles
     * @return Collection
     */
    private function createMovementDetails(Movimiento $movimiento, array $detalles): Collection
    {
        $createdDetails = collect();

        foreach ($detalles as $detalle) {
            $detalleData = [
                'movimiento_id' => $movimiento->id,
                'producto_id' => $detalle['producto_id'],
                'cantidad' => $detalle['cantidad'],
                'costo_unitario' => $detalle['costo_unitario'] ?? 0.00,
                'total' => ($detalle['cantidad'] ?? 0) * ($detalle['costo_unitario'] ?? 0),
            ];

            $movimientoDetalle = MovimientoDetalle::create($detalleData);
            $createdDetails->push($movimientoDetalle);
        }

        return $createdDetails;
    }

    /**
     * Confirm the movement by changing state
     *
     * @param Movimiento $movimiento
     */
    private function confirmMovement(Movimiento $movimiento): void
    {
        $movimiento->update([
            'estado' => 'confirmado',
            'updated_by' => $movimiento->created_by,
        ]);
    }

    /**
     * Generate accounting entry using stored procedure
     *
     * @param Movimiento $movimiento
     * @return Asiento|null
     */
    private function generateAccountingEntry(Movimiento $movimiento): ?Asiento
    {
        try {
            // Llamar al procedimiento almacenado para generar asiento contable
            DB::statement('CALL generar_asiento_contable(?)', [$movimiento->id]);

            // Recargar el movimiento para obtener el asiento_id
            $movimiento->refresh();

            return $movimiento->asiento_id ? Asiento::find($movimiento->asiento_id) : null;
        } catch (PDOException $e) {
            // Si el procedimiento no existe o falla, log y continuar sin asiento
            Log::warning('No se pudo generar asiento contable automáticamente', [
                'movimiento_id' => $movimiento->id,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Get affected stock records
     *
     * @param Movimiento $movimiento
     * @return Collection
     */
    private function getAffectedStock(Movimiento $movimiento): Collection
    {
        $productosIds = $movimiento->detalles->pluck('producto_id')->unique();
        $bodegasIds = collect([
            $movimiento->bodega_origen_id,
            $movimiento->bodega_destino_id
        ])->filter()->unique();

        return Existencia::with(['producto', 'bodega'])
            ->whereIn('producto_id', $productosIds)
            ->whereIn('bodega_id', $bodegasIds)
            ->get();
    }

    /**
     * Get movement by ID with all relations
     *
     * @param int $movimientoId
     * @return Movimiento|null
     */
    public function getMovementById(int $movimientoId): ?Movimiento
    {
        return Movimiento::with([
            'detalles.producto',
            'bodegaOrigen',
            'bodegaDestino',
            'asiento.detalles.cuenta',
            'creadoPor'
        ])->find($movimientoId);
    }

    /**
     * Cancel a movement
     *
     * @param int $movimientoId
     * @param int $userId
     * @return Movimiento
     * @throws InventoryException
     */
    public function cancelMovement(int $movimientoId, int $userId): Movimiento
    {
        $movimiento = $this->getMovementById($movimientoId);
        
        if (!$movimiento) {
            throw new InventoryException("Movimiento no encontrado");
        }

        if ($movimiento->estado === 'anulado') {
            throw new InventoryException("El movimiento ya está anulado");
        }

        if ($movimiento->estado === 'borrador') {
            // Si está en borrador, simplemente eliminar
            $movimiento->delete();
            throw new InventoryException("Movimiento en borrador eliminado");
        }

        // TODO: Implementar lógica de reversión para movimientos confirmados
        throw new InventoryException("La anulación de movimientos confirmados aún no está implementada");
    }

    /**
     * Get available movement types
     *
     * @return array
     */
    public static function getValidMovementTypes(): array
    {
        return self::VALID_MOVEMENT_TYPES;
    }

    /**
     * Get available movement states
     *
     * @return array
     */
    public static function getValidMovementStates(): array
    {
        return self::MOVEMENT_STATES;
    }
}
