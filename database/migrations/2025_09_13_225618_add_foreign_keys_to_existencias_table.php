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
        Schema::table('existencias', function (Blueprint $table) {
            $table->foreign(['bodega_id'], 'fk_exis_bodega')->references(['id'])->on('bodegas')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['producto_id'], 'fk_exis_producto')->references(['id'])->on('productos')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('existencias', function (Blueprint $table) {
            $table->dropForeign('fk_exis_bodega');
            $table->dropForeign('fk_exis_producto');
        });
    }
};
