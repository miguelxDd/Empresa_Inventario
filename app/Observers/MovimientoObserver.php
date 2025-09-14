<?php

namespace App\Observers;

use App\Models\Movimiento;
use App\Services\InventoryAccountingService;
use Illuminate\Support\Facades\Log;

class MovimientoObserver
{
    private InventoryAccountingService $accountingService;

    public function __construct(InventoryAccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    /**
     * Handle the Movimiento "created" event.
     */
    public function created(Movimiento $movimiento): void
    {
        //
    }

    /**
     * Handle the Movimiento "updated" event.
     */
    public function updated(Movimiento $movimiento): void
    {
        // Si el estado cambió de 'borrador' a 'confirmado', procesar contablemente
        if ($movimiento->isDirty('estado') && 
            $movimiento->getOriginal('estado') === 'borrador' && 
            $movimiento->estado === 'confirmado') {
            
            try {
                $asiento = $this->accountingService->postMovement($movimiento);
                
                Log::info("Movimiento procesado contablemente", [
                    'movimiento_id' => $movimiento->id,
                    'asiento_id' => $asiento->id,
                    'tipo' => $movimiento->tipo
                ]);
            } catch (\Exception $e) {
                Log::error("Error al procesar movimiento contablemente", [
                    'movimiento_id' => $movimiento->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Revertir el estado si hay error
                $movimiento->update(['estado' => 'borrador']);
                throw $e;
            }
        }
    }

    /**
     * Handle the Movimiento "deleted" event.
     */
    public function deleted(Movimiento $movimiento): void
    {
        //
    }

    /**
     * Handle the Movimiento "restored" event.
     */
    public function restored(Movimiento $movimiento): void
    {
        //
    }

    /**
     * Handle the Movimiento "force deleted" event.
     */
    public function forceDeleted(Movimiento $movimiento): void
    {
        //
    }
}
