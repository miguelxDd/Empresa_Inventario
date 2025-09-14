<?php

namespace App\Services;

use App\Models\Movimiento;
use App\Models\Asiento;
use App\Models\AsientosDetalle;
use App\Models\Existencia;
use App\Models\Producto;
use App\Models\Cuenta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servicio para manejo contable de inventarios
 * Implementa doble partida y actualización de existencias
 */
class InventoryAccountingService
{
    private ReglasContablesResolver $reglasResolver;

    public function __construct(ReglasContablesResolver $reglasResolver)
    {
        $this->reglasResolver = $reglasResolver;
    }

    /**
     * Procesa contablemente un movimiento de inventario
     * 
     * @param Movimiento $movimiento
     * @return Asiento
     * @throws Exception
     */
    public function postMovement(Movimiento $movimiento): Asiento
    {
        return DB::transaction(function () use ($movimiento) {
            // Validar que el movimiento esté en estado borrador
            if ($movimiento->estado !== 'borrador') {
                throw new Exception("Solo se pueden confirmar movimientos en estado borrador");
            }

            // Validar existencias para salidas
            if (in_array($movimiento->tipo, ['salida', 'transferencia'])) {
                $this->validarExistenciasParaSalida($movimiento);
            }

            // Crear asiento contable
            $asiento = $this->crearAsientoContable($movimiento);

            // Actualizar existencias
            $this->actualizarExistencias($movimiento);

            // Actualizar estado del movimiento
            $movimiento->update([
                'estado' => 'confirmado',
                'asiento_id' => $asiento->id
            ]);

            Log::info("Movimiento confirmado", [
                'movimiento_id' => $movimiento->id,
                'asiento_id' => $asiento->id,
                'tipo' => $movimiento->tipo
            ]);

            return $asiento;
        });
    }

    /**
     * Crear asiento contable según el tipo de movimiento
     */
    private function crearAsientoContable(Movimiento $movimiento): Asiento
    {
        $numeroAsiento = $this->generarNumeroAsiento($movimiento->fecha);
        
        $asiento = Asiento::create([
            'fecha' => $movimiento->fecha,
            'numero' => $numeroAsiento,
            'descripcion' => $this->generarDescripcionAsiento($movimiento),
            'origen_tabla' => 'movimientos',
            'origen_id' => $movimiento->id,
            'estado' => 'confirmado',
            'total_debe' => 0,
            'total_haber' => 0,
            'created_by' => $movimiento->created_by ?? 1
        ]);

        $detalles = [];

        switch ($movimiento->tipo) {
            case 'entrada':
                $detalles = $this->procesarEntrada($movimiento);
                break;
            case 'salida':
                $detalles = $this->procesarSalida($movimiento);
                break;
            case 'ajuste':
                $detalles = $this->procesarAjuste($movimiento);
                break;
            case 'transferencia':
                // Transferencias no impactan GL por política
                $detalles = [];
                break;
            default:
                throw new Exception("Tipo de movimiento no válido: {$movimiento->tipo}");
        }

        $totalDebe = 0;
        $totalHaber = 0;

        foreach ($detalles as $detalle) {
            AsientosDetalle::create([
                'asiento_id' => $asiento->id,
                'cuenta_id' => $detalle['cuenta_id'],
                'debe' => $detalle['debe'],
                'haber' => $detalle['haber'],
                'concepto' => $detalle['concepto']
            ]);

            $totalDebe += $detalle['debe'];
            $totalHaber += $detalle['haber'];
        }

        // Validar doble partida
        if (abs($totalDebe - $totalHaber) > 0.01) {
            throw new Exception("Asiento descuadrado: Debe={$totalDebe}, Haber={$totalHaber}");
        }

        // Actualizar totales del asiento
        $asiento->update([
            'total_debe' => $totalDebe,
            'total_haber' => $totalHaber
        ]);

        return $asiento;
    }

    /**
     * Procesar entrada de inventario
     */
    private function procesarEntrada(Movimiento $movimiento): array
    {
        $detalles = [];

        foreach ($movimiento->detalles as $detalle) {
            $producto = $detalle->producto;
            $cuentas = $this->reglasResolver->resolverCuentas('entrada', $producto);

            if (!$cuentas) {
                throw new Exception("No se encontraron reglas contables para entrada del producto {$producto->nombre}");
            }

            $valor = $detalle->cantidad * $detalle->costo_unitario;

            // Debe: Inventario
            $detalles[] = [
                'cuenta_id' => $cuentas['cuenta_debe_id'],
                'debe' => $valor,
                'haber' => 0,
                'concepto' => "Entrada {$producto->nombre} - {$detalle->cantidad} " . ($producto->unidade->nombre ?? 'UND')
            ];

            // Haber: Contraparte (Proveedores, Caja, etc.)
            $detalles[] = [
                'cuenta_id' => $cuentas['cuenta_haber_id'],
                'debe' => 0,
                'haber' => $valor,
                'concepto' => "Contrapartida entrada {$producto->nombre}"
            ];
        }

        return $detalles;
    }

    /**
     * Procesar salida de inventario
     */
    private function procesarSalida(Movimiento $movimiento): array
    {
        $detalles = [];

        foreach ($movimiento->detalles as $detalle) {
            $producto = $detalle->producto;
            $cuentas = $this->reglasResolver->resolverCuentas('salida', $producto);

            if (!$cuentas) {
                throw new Exception("No se encontraron reglas contables para salida del producto {$producto->nombre}");
            }

            // Obtener costo promedio actual
            $existencia = Existencia::where('producto_id', $producto->id)
                ->where('bodega_id', $movimiento->bodega_origen_id)
                ->first();

            $costoPromedio = $existencia ? $existencia->costo_promedio : 0;
            $valor = $detalle->cantidad * $costoPromedio;

            // Debe: Costo de Ventas/Consumo
            $detalles[] = [
                'cuenta_id' => $cuentas['cuenta_debe_id'],
                'debe' => $valor,
                'haber' => 0,
                'concepto' => "Costo salida {$producto->nombre} - {$detalle->cantidad} " . ($producto->unidade->nombre ?? 'UND')
            ];

            // Haber: Inventario
            $detalles[] = [
                'cuenta_id' => $cuentas['cuenta_haber_id'],
                'debe' => 0,
                'haber' => $valor,
                'concepto' => "Salida inventario {$producto->nombre}"
            ];
        }

        return $detalles;
    }

    /**
     * Procesar ajuste de inventario
     */
    private function procesarAjuste(Movimiento $movimiento): array
    {
        $detalles = [];

        foreach ($movimiento->detalles as $detalle) {
            $producto = $detalle->producto;
            $cuentas = $this->reglasResolver->resolverCuentas('ajuste', $producto);

            if (!$cuentas) {
                throw new Exception("No se encontraron reglas contables para ajuste del producto {$producto->nombre}");
            }

            $valor = abs($detalle->cantidad * $detalle->costo_unitario);
            $esPositivo = $detalle->cantidad > 0;

            if ($esPositivo) {
                // Ajuste positivo: Debe Inventario, Haber Ajustes
                $detalles[] = [
                    'cuenta_id' => $cuentas['cuenta_debe_id'], // Inventario
                    'debe' => $valor,
                    'haber' => 0,
                    'concepto' => "Ajuste + {$producto->nombre} - {$detalle->cantidad} " . ($producto->unidade->nombre ?? 'UND')
                ];

                $detalles[] = [
                    'cuenta_id' => $cuentas['cuenta_haber_id'], // Ajustes/Resultados
                    'debe' => 0,
                    'haber' => $valor,
                    'concepto' => "Contrapartida ajuste + {$producto->nombre}"
                ];
            } else {
                // Ajuste negativo: Debe Ajustes, Haber Inventario
                $detalles[] = [
                    'cuenta_id' => $cuentas['cuenta_haber_id'], // Ajustes/Resultados
                    'debe' => $valor,
                    'haber' => 0,
                    'concepto' => "Ajuste - {$producto->nombre} - {$detalle->cantidad} " . ($producto->unidade->nombre ?? 'UND')
                ];

                $detalles[] = [
                    'cuenta_id' => $cuentas['cuenta_debe_id'], // Inventario
                    'debe' => 0,
                    'haber' => $valor,
                    'concepto' => "Contrapartida ajuste - {$producto->nombre}"
                ];
            }
        }

        return $detalles;
    }

    /**
     * Actualizar existencias según el tipo de movimiento
     */
    private function actualizarExistencias(Movimiento $movimiento): void
    {
        foreach ($movimiento->detalles as $detalle) {
            switch ($movimiento->tipo) {
                case 'entrada':
                    $this->actualizarExistenciaEntrada($detalle, $movimiento->bodega_destino_id);
                    break;
                case 'salida':
                    $this->actualizarExistenciaSalida($detalle, $movimiento->bodega_origen_id);
                    break;
                case 'ajuste':
                    $this->actualizarExistenciaAjuste($detalle, $movimiento->bodega_destino_id ?? $movimiento->bodega_origen_id);
                    break;
                case 'transferencia':
                    $this->actualizarExistenciaTransferencia($detalle, $movimiento->bodega_origen_id, $movimiento->bodega_destino_id);
                    break;
            }
        }
    }

    /**
     * Actualizar existencia para entrada (método promedio ponderado)
     */
    private function actualizarExistenciaEntrada($detalle, $bodegaId): void
    {
        $existencia = Existencia::firstOrCreate(
            [
                'producto_id' => $detalle->producto_id,
                'bodega_id' => $bodegaId
            ],
            [
                'cantidad' => 0,
                'costo_promedio' => 0,
                'stock_minimo' => 0,
                'stock_maximo' => 0
            ]
        );

        $cantidadAnterior = $existencia->cantidad;
        $costoAnterior = $existencia->costo_promedio;
        $cantidadNueva = $cantidadAnterior + $detalle->cantidad;

        // Cálculo promedio ponderado
        if ($cantidadNueva > 0) {
            $valorAnterior = $cantidadAnterior * $costoAnterior;
            $valorNuevo = $detalle->cantidad * $detalle->costo_unitario;
            $costoPromedio = ($valorAnterior + $valorNuevo) / $cantidadNueva;
        } else {
            $costoPromedio = 0;
        }

        $existencia->update([
            'cantidad' => $cantidadNueva,
            'costo_promedio' => $costoPromedio
        ]);
    }

    /**
     * Actualizar existencia para salida
     */
    private function actualizarExistenciaSalida($detalle, $bodegaId): void
    {
        $existencia = Existencia::where('producto_id', $detalle->producto_id)
            ->where('bodega_id', $bodegaId)
            ->first();

        if (!$existencia) {
            throw new Exception("No existe inventario del producto {$detalle->producto->nombre} en la bodega");
        }

        $cantidadNueva = $existencia->cantidad - $detalle->cantidad;

        if ($cantidadNueva < 0) {
            throw new Exception("Stock insuficiente para {$detalle->producto->nombre}. Disponible: {$existencia->cantidad}, Solicitado: {$detalle->cantidad}");
        }

        $existencia->update([
            'cantidad' => $cantidadNueva
        ]);
    }

    /**
     * Actualizar existencia para ajuste
     */
    private function actualizarExistenciaAjuste($detalle, $bodegaId): void
    {
        $existencia = Existencia::firstOrCreate(
            [
                'producto_id' => $detalle->producto_id,
                'bodega_id' => $bodegaId
            ],
            [
                'cantidad' => 0,
                'costo_promedio' => 0,
                'stock_minimo' => 0,
                'stock_maximo' => 0
            ]
        );

        $cantidadNueva = $existencia->cantidad + $detalle->cantidad; // cantidad puede ser negativa

        if ($cantidadNueva < 0) {
            throw new Exception("El ajuste resultaría en stock negativo para {$detalle->producto->nombre}");
        }

        // Recalcular costo promedio si es ajuste positivo con costo
        if ($detalle->cantidad > 0 && $detalle->costo_unitario > 0) {
            $cantidadAnterior = $existencia->cantidad;
            $costoAnterior = $existencia->costo_promedio;
            
            if ($cantidadNueva > 0) {
                $valorAnterior = $cantidadAnterior * $costoAnterior;
                $valorNuevo = $detalle->cantidad * $detalle->costo_unitario;
                $costoPromedio = ($valorAnterior + $valorNuevo) / $cantidadNueva;
            } else {
                $costoPromedio = 0;
            }

            $existencia->update([
                'cantidad' => $cantidadNueva,
                'costo_promedio' => $costoPromedio
            ]);
        } else {
            $existencia->update([
                'cantidad' => $cantidadNueva
            ]);
        }
    }

    /**
     * Actualizar existencias para transferencia
     */
    private function actualizarExistenciaTransferencia($detalle, $bodegaOrigenId, $bodegaDestinoId): void
    {
        // Salida de bodega origen
        $this->actualizarExistenciaSalida($detalle, $bodegaOrigenId);

        // Entrada a bodega destino (conservando costo promedio de origen)
        $existenciaOrigen = Existencia::where('producto_id', $detalle->producto_id)
            ->where('bodega_id', $bodegaOrigenId)
            ->first();

        $costoTransferencia = $existenciaOrigen ? $existenciaOrigen->costo_promedio : $detalle->costo_unitario;

        // Crear detalle temporal para entrada
        $detalleEntrada = (object) [
            'producto_id' => $detalle->producto_id,
            'cantidad' => $detalle->cantidad,
            'costo_unitario' => $costoTransferencia,
            'producto' => $detalle->producto
        ];

        $this->actualizarExistenciaEntrada($detalleEntrada, $bodegaDestinoId);
    }

    /**
     * Validar existencias suficientes para salidas
     */
    private function validarExistenciasParaSalida(Movimiento $movimiento): void
    {
        $bodegaId = $movimiento->bodega_origen_id;

        foreach ($movimiento->detalles as $detalle) {
            $existencia = Existencia::where('producto_id', $detalle->producto_id)
                ->where('bodega_id', $bodegaId)
                ->first();

            $disponible = $existencia ? $existencia->cantidad : 0;

            if ($disponible < $detalle->cantidad) {
                throw new Exception("Stock insuficiente para {$detalle->producto->nombre}. Disponible: {$disponible}, Solicitado: {$detalle->cantidad}");
            }
        }
    }

    /**
     * Generar número único de asiento
     */
    private function generarNumeroAsiento($fecha): string
    {
        $year = date('Y', strtotime($fecha));
        $prefijo = "AS-{$year}-";
        
        $ultimoNumero = Asiento::where('numero', 'like', $prefijo . '%')
            ->orderBy('numero', 'desc')
            ->value('numero');

        if ($ultimoNumero) {
            $numero = intval(str_replace($prefijo, '', $ultimoNumero)) + 1;
        } else {
            $numero = 1;
        }

        return $prefijo . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generar descripción del asiento
     */
    private function generarDescripcionAsiento(Movimiento $movimiento): string
    {
        $productos = $movimiento->detalles->pluck('producto.nombre')->take(3)->implode(', ');
        $total = $movimiento->detalles->count();
        
        if ($total > 3) {
            $productos .= " y " . ($total - 3) . " más";
        }

        return "Movimiento de inventario - {$movimiento->tipo} - {$productos} - Ref: {$movimiento->referencia}";
    }
}
