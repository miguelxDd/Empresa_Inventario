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
        Schema::table('movimiento_detalles', function (Blueprint $table) {
            $table->foreign(['movimiento_id'], 'fk_movdet_mov')->references(['id'])->on('movimientos')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['producto_id'], 'fk_movdet_prod')->references(['id'])->on('productos')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimiento_detalles', function (Blueprint $table) {
            $table->dropForeign('fk_movdet_mov');
            $table->dropForeign('fk_movdet_prod');
        });
    }
};
