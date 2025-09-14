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
        Schema::create('reglas_contables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('tipo_movimiento', ['entrada', 'salida', 'ajuste', 'transferencia']);
            $table->unsignedBigInteger('categoria_producto_id')->nullable()->index('idx_reglas_categoria');
            $table->unsignedBigInteger('producto_id')->nullable()->index('idx_reglas_producto');
            $table->unsignedBigInteger('cuenta_debe_id')->index('fk_regla_cta_debe');
            $table->unsignedBigInteger('cuenta_haber_id')->index('fk_regla_cta_haber');
            $table->unsignedTinyInteger('prioridad')->default(1);
            $table->boolean('activa')->default(true);
            $table->text('descripcion')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();

            $table->index(['tipo_movimiento', 'prioridad'], 'idx_reglas_prioridad');
            $table->index(['tipo_movimiento', 'activa'], 'idx_reglas_tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reglas_contables');
    }
};
