<?php

namespace App\DTOs;

use App\Models\Movimiento;
use App\Models\Asiento;
use Illuminate\Support\Collection;

class InventoryMovementResult
{
    /**
     * Create a new inventory movement result DTO.
     *
     * @param Movimiento $movimiento
     * @param Asiento|null $asiento
     * @param Collection $existenciasAfectadas
     * @param Collection $detalles
     * @param bool $success
     */
    public function __construct(
        public readonly Movimiento $movimiento,
        public readonly ?Asiento $asiento,
        public readonly Collection $existenciasAfectadas,
        public readonly Collection $detalles,
        public readonly bool $success = true
    ) {}

    /**
     * Convert to array for API responses
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'movimiento' => [
                'id' => $this->movimiento->id,
                'numero' => $this->movimiento->numero,
                'tipo_movimiento' => $this->movimiento->tipo_movimiento,
                'fecha_movimiento' => $this->movimiento->fecha_movimiento,
                'estado' => $this->movimiento->estado,
                'valor_total' => $this->movimiento->valor_total,
                'observaciones' => $this->movimiento->observaciones,
                'usuario' => $this->movimiento->usuario?->toArray(),
                'bodega_origen' => $this->movimiento->bodegaOrigen?->toArray(),
                'bodega_destino' => $this->movimiento->bodegaDestino?->toArray(),
            ],
            'asiento' => $this->asiento ? [
                'id' => $this->asiento->id,
                'numero' => $this->asiento->numero,
                'fecha' => $this->asiento->fecha,
                'estado' => $this->asiento->estado,
                'total_debe' => $this->asiento->total_debe,
                'total_haber' => $this->asiento->total_haber,
                'descripcion' => $this->asiento->descripcion,
            ] : null,
            'detalles' => $this->detalles->map(function ($detalle) {
                return [
                    'id' => $detalle->id,
                    'producto_id' => $detalle->producto_id,
                    'producto_nombre' => $detalle->producto->nombre,
                    'cantidad' => $detalle->cantidad,
                    'costo_unitario' => $detalle->costo_unitario,
                    'total' => $detalle->total,
                ];
            })->toArray(),
            'existencias_afectadas' => $this->existenciasAfectadas->map(function ($existencia) {
                return [
                    'producto_id' => $existencia->producto_id,
                    'producto_nombre' => $existencia->producto->nombre,
                    'bodega_id' => $existencia->bodega_id,
                    'bodega_nombre' => $existencia->bodega->nombre,
                    'cantidad' => $existencia->cantidad,
                    'costo_promedio' => $existencia->costo_promedio,
                ];
            })->toArray(),
            'resumen' => [
                'productos_afectados' => $this->detalles->count(),
                'bodegas_afectadas' => $this->existenciasAfectadas->pluck('bodega_id')->unique()->count(),
                'valor_total_movimiento' => $this->movimiento->valor_total,
                'tiene_asiento_contable' => !is_null($this->asiento),
            ]
        ];
    }

    /**
     * Get summary information
     *
     * @return array
     */
    public function getSummary(): array
    {
        return [
            'movimiento_id' => $this->movimiento->id,
            'movimiento_numero' => $this->movimiento->numero,
            'tipo' => $this->movimiento->tipo,
            'estado' => $this->movimiento->estado,
            'valor_total' => $this->movimiento->valor_total,
            'productos_procesados' => $this->detalles->count(),
            'asiento_generado' => !is_null($this->asiento),
            'asiento_numero' => $this->asiento?->numero,
        ];
    }
}
