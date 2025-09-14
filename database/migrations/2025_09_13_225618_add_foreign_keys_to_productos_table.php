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
            $table->foreign(['categoria_id'], 'fk_prod_categoria')->references(['id'])->on('categorias')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['cuenta_contraparte_id'], 'fk_prod_cta_contra')->references(['id'])->on('cuentas')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['cuenta_costo_id'], 'fk_prod_cta_costo')->references(['id'])->on('cuentas')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['cuenta_inventario_id'], 'fk_prod_cta_inv')->references(['id'])->on('cuentas')->onUpdate('restrict')->onDelete('set null');
            $table->foreign(['unidad_id'], 'fk_prod_unidad')->references(['id'])->on('unidades')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign('fk_prod_categoria');
            $table->dropForeign('fk_prod_cta_contra');
            $table->dropForeign('fk_prod_cta_costo');
            $table->dropForeign('fk_prod_cta_inv');
            $table->dropForeign('fk_prod_unidad');
        });
    }
};
