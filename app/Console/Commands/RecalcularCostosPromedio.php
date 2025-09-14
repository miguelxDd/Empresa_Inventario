<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Existencia;
use App\Models\MovimientoDetalle;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;

class RecalcularCostosPromedio extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventario:recalcular-costos {--dry-run : Solo mostrar lo que se haría sin ejecutar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcula los costos promedio de todas las existencias basándose en los movimientos de entrada';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 MODO SIMULACIÓN - Solo mostrando cambios, no se ejecutarán');
        } else {
            $this->info('🔄 Recalculando costos promedio de existencias...');
        }

        $existencias = Existencia::with(['producto:id,nombre'])->get();
        $this->info("📦 Encontradas {$existencias->count()} existencias para procesar");

        $actualizadas = 0;
        $errores = 0;

        foreach ($existencias as $existencia) {
            try {
                // Obtener todos los movimientos de entrada para este producto en esta bodega
                $movimientosEntrada = MovimientoDetalle::join('movimientos', 'movimiento_detalles.movimiento_id', '=', 'movimientos.id')
                    ->where('movimiento_detalles.producto_id', $existencia->producto_id)
                    ->where('movimientos.bodega_destino_id', $existencia->bodega_id)
                    ->where('movimientos.tipo', 'entrada')
                    ->where('movimientos.estado', 'confirmado')
                    ->select('movimiento_detalles.cantidad', 'movimiento_detalles.costo_unitario', 'movimientos.fecha')
                    ->orderBy('movimientos.fecha', 'asc')
                    ->get();

                if ($movimientosEntrada->isEmpty()) {
                    $this->warn("⚠️  No hay movimientos de entrada para {$existencia->producto->nombre} en bodega {$existencia->bodega_id}");
                    continue;
                }

                // Calcular costo promedio ponderado
                $cantidadTotal = 0;
                $valorTotal = 0;

                foreach ($movimientosEntrada as $movimiento) {
                    $cantidadTotal += $movimiento->cantidad;
                    $valorTotal += ($movimiento->cantidad * $movimiento->costo_unitario);
                }

                $nuevoCostoPromedio = $cantidadTotal > 0 ? ($valorTotal / $cantidadTotal) : 0;

                $this->line("📋 {$existencia->producto->nombre}:");
                $this->line("   - Bodega: {$existencia->bodega_id}");
                $this->line("   - Cantidad actual: {$existencia->cantidad}");
                $this->line("   - Costo anterior: $" . number_format($existencia->costo_promedio, 2));
                $this->line("   - Nuevo costo: $" . number_format($nuevoCostoPromedio, 2));
                $this->line("   - Movimientos procesados: {$movimientosEntrada->count()}");

                if (!$isDryRun) {
                    // Actualizar la existencia usando consulta directa
                    \App\Models\Existencia::where('producto_id', $existencia->producto_id)
                        ->where('bodega_id', $existencia->bodega_id)
                        ->update([
                            'costo_promedio' => $nuevoCostoPromedio
                        ]);
                    $this->info("   ✅ Actualizado");
                } else {
                    $this->info("   🔍 Sería actualizado (modo simulación)");
                }

                $actualizadas++;
                
            } catch (\Exception $e) {
                $this->error("❌ Error procesando {$existencia->producto->nombre}: " . $e->getMessage());
                $errores++;
            }
        }

        $this->newLine();
        if ($isDryRun) {
            $this->info("📊 RESUMEN (SIMULACIÓN):");
            $this->info("   - Existencias que serían actualizadas: {$actualizadas}");
            $this->info("   - Errores encontrados: {$errores}");
            $this->info("💡 Para ejecutar realmente, use: php artisan inventario:recalcular-costos");
        } else {
            $this->info("📊 RESUMEN:");
            $this->info("   - Existencias actualizadas: {$actualizadas}");
            $this->info("   - Errores: {$errores}");
            $this->info("✅ Proceso completado exitosamente");
        }

        return Command::SUCCESS;
    }
}
