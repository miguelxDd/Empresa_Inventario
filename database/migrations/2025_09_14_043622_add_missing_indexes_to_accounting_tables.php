<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Helper para verificar si un índice existe
        $indexExists = function($table, $indexName) {
            $indexes = collect(DB::select("SHOW INDEX FROM {$table}"))
                ->pluck('Key_name')
                ->unique();
            return $indexes->contains($indexName);
        };

        // Índices para movimientos
        if (!$indexExists('movimientos', 'idx_movimientos_fecha_tipo_estado')) {
            Schema::table('movimientos', function (Blueprint $table) {
                $table->index(['fecha', 'tipo', 'estado'], 'idx_movimientos_fecha_tipo_estado');
            });
        }
        
        if (!$indexExists('movimientos', 'idx_movimientos_estado_created')) {
            Schema::table('movimientos', function (Blueprint $table) {
                $table->index(['estado', 'created_at'], 'idx_movimientos_estado_created');
            });
        }

        // Índices para asientos_detalle
        if (!$indexExists('asientos_detalle', 'idx_asientos_detalle_asiento_cuenta')) {
            Schema::table('asientos_detalle', function (Blueprint $table) {
                $table->index(['asiento_id', 'cuenta_id'], 'idx_asientos_detalle_asiento_cuenta');
            });
        }
        
        if (!$indexExists('asientos_detalle', 'idx_asientos_detalle_cuenta')) {
            Schema::table('asientos_detalle', function (Blueprint $table) {
                $table->index(['cuenta_id'], 'idx_asientos_detalle_cuenta');
            });
        }

        // Índices para cuentas
        if (!$indexExists('cuentas', 'idx_cuentas_tipo_activa')) {
            Schema::table('cuentas', function (Blueprint $table) {
                $table->index(['tipo', 'activa'], 'idx_cuentas_tipo_activa');
            });
        }
        
        if (!$indexExists('cuentas', 'idx_cuentas_codigo')) {
            Schema::table('cuentas', function (Blueprint $table) {
                $table->index(['codigo'], 'idx_cuentas_codigo');
            });
        }

        // Índices para existencias
        if (!$indexExists('existencias', 'idx_existencias_producto_bodega')) {
            Schema::table('existencias', function (Blueprint $table) {
                $table->index(['producto_id', 'bodega_id'], 'idx_existencias_producto_bodega');
            });
        }
        
        if (!$indexExists('existencias', 'idx_existencias_updated')) {
            Schema::table('existencias', function (Blueprint $table) {
                $table->index(['updated_at'], 'idx_existencias_updated');
            });
        }

        // Índices para reglas_contables (si la tabla existe)
        $tablesResult = DB::select("SHOW TABLES LIKE 'reglas_contables'");
        if (!empty($tablesResult)) {
            if (!$indexExists('reglas_contables', 'idx_reglas_tipo_activa_prioridad')) {
                Schema::table('reglas_contables', function (Blueprint $table) {
                    $table->index(['tipo_movimiento', 'activa', 'prioridad'], 'idx_reglas_tipo_activa_prioridad');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Helper para verificar si un índice existe antes de eliminarlo
        $indexExists = function($table, $indexName) {
            try {
                $indexes = collect(DB::select("SHOW INDEX FROM {$table}"))
                    ->pluck('Key_name')
                    ->unique();
                return $indexes->contains($indexName);
            } catch (\Exception $e) {
                return false;
            }
        };

        // Eliminar índices si existen
        $indicesToDrop = [
            'movimientos' => ['idx_movimientos_fecha_tipo_estado', 'idx_movimientos_estado_created'],
            'asientos_detalle' => ['idx_asientos_detalle_asiento_cuenta', 'idx_asientos_detalle_cuenta'],
            'cuentas' => ['idx_cuentas_tipo_activa', 'idx_cuentas_codigo'],
            'existencias' => ['idx_existencias_producto_bodega', 'idx_existencias_updated'],
            'reglas_contables' => ['idx_reglas_tipo_activa_prioridad']
        ];

        foreach ($indicesToDrop as $table => $indexes) {
            foreach ($indexes as $indexName) {
                if ($indexExists($table, $indexName)) {
                    Schema::table($table, function (Blueprint $table) use ($indexName) {
                        $table->dropIndex($indexName);
                    });
                }
            }
        }
    }
};
