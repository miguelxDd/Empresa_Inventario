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
        Schema::table('productos', function (Blueprint $table) {
            // Campos básicos que están en el formulario
            $table->decimal('precio_compra', 10, 2)->nullable()->after('precio_compra_promedio');
            $table->integer('stock_minimo')->default(0)->after('precio_venta');
            $table->integer('stock_maximo')->default(0)->after('stock_minimo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['precio_compra', 'stock_minimo', 'stock_maximo']);
        });
    }
};
