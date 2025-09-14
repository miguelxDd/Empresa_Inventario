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
        Schema::table('reglas_contables', function (Blueprint $table) {
            $table->foreign(['categoria_producto_id'], 'fk_regla_categoria')->references(['id'])->on('categorias')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['cuenta_debe_id'], 'fk_regla_cta_debe')->references(['id'])->on('cuentas')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['cuenta_haber_id'], 'fk_regla_cta_haber')->references(['id'])->on('cuentas')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['producto_id'], 'fk_regla_producto')->references(['id'])->on('productos')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reglas_contables', function (Blueprint $table) {
            $table->dropForeign('fk_regla_categoria');
            $table->dropForeign('fk_regla_cta_debe');
            $table->dropForeign('fk_regla_cta_haber');
            $table->dropForeign('fk_regla_producto');
        });
    }
};
