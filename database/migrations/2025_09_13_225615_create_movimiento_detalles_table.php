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
        Schema::create('movimiento_detalles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('movimiento_id')->index('idx_mov_detalles_movimiento');
            $table->unsignedBigInteger('producto_id')->index('idx_mov_detalles_producto');
            $table->decimal('cantidad', 12, 4);
            $table->decimal('costo_unitario', 12, 4);
            $table->decimal('total', 15, 4);
            $table->text('observaciones')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimiento_detalles');
    }
};
