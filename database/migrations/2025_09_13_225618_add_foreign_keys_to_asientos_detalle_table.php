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
        Schema::table('asientos_detalle', function (Blueprint $table) {
            $table->foreign(['asiento_id'], 'fk_asidet_asiento')->references(['id'])->on('asientos')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['cuenta_id'], 'fk_asidet_cuenta')->references(['id'])->on('cuentas')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asientos_detalle', function (Blueprint $table) {
            $table->dropForeign('fk_asidet_asiento');
            $table->dropForeign('fk_asidet_cuenta');
        });
    }
};
