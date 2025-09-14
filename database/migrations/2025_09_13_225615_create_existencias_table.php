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
        Schema::create('existencias', function (Blueprint $table) {
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('bodega_id')->index('idx_existencias_bodega');
            $table->decimal('cantidad', 12, 4)->default(0);
            $table->decimal('costo_promedio', 12, 4)->default(0);
            $table->decimal('stock_minimo', 12, 4)->nullable()->default(0);
            $table->decimal('stock_maximo', 12, 4)->nullable();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();

            $table->index(['producto_id', 'cantidad', 'stock_minimo'], 'idx_existencias_stock_bajo');
            $table->primary(['producto_id', 'bodega_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('existencias');
    }
};
