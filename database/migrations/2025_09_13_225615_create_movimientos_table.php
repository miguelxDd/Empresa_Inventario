<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('fecha')->index('idx_movimientos_fecha');
            $table->enum('tipo', ['entrada', 'salida', 'ajuste', 'transferencia']);
            $table->unsignedBigInteger('bodega_origen_id')->nullable();
            $table->unsignedBigInteger('bodega_destino_id')->nullable();
            $table->enum('estado', ['borrador', 'confirmado', 'cancelado'])->default('borrador');
            $table->string('numero_documento', 50)->nullable();
            $table->string('referencia', 100)->nullable();
            $table->text('observaciones')->nullable();
            $table->decimal('valor_total', 15, 4)->default(0);
            $table->unsignedBigInteger('asiento_id')->nullable()->index('idx_movimientos_asiento');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->softDeletes();

            $table->index(['bodega_destino_id', 'fecha'], 'idx_movimientos_bodega_destino');
            $table->index(['bodega_origen_id', 'fecha'], 'idx_movimientos_bodega_origen');
            $table->index(['tipo', 'estado'], 'idx_movimientos_tipo_estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
