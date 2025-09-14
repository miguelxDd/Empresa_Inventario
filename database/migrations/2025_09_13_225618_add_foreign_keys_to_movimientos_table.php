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
        Schema::table('movimientos', function (Blueprint $table) {
            $table->foreign(['asiento_id'], 'fk_mov_asiento')->references(['id'])->on('asientos')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['bodega_destino_id'], 'fk_mov_bod_destino')->references(['id'])->on('bodegas')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['bodega_origen_id'], 'fk_mov_bod_origen')->references(['id'])->on('bodegas')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->dropForeign('fk_mov_asiento');
            $table->dropForeign('fk_mov_bod_destino');
            $table->dropForeign('fk_mov_bod_origen');
        });
    }
};
