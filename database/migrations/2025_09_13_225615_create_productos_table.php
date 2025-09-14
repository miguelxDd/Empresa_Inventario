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
        Schema::create('productos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sku', 50)->index('idx_productos_sku');
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('unidad_id')->index('fk_prod_unidad');
            $table->unsignedBigInteger('categoria_id')->index('idx_productos_categoria');
            $table->decimal('precio_compra_promedio', 12, 4)->nullable()->default(0);
            $table->decimal('precio_venta', 12, 4)->nullable();
            $table->boolean('activo')->default(true)->index('idx_productos_activo');
            $table->boolean('permite_negativo')->default(false);
            $table->unsignedBigInteger('cuenta_inventario_id')->nullable()->index('fk_prod_cta_inv');
            $table->unsignedBigInteger('cuenta_costo_id')->nullable()->index('fk_prod_cta_costo');
            $table->unsignedBigInteger('cuenta_contraparte_id')->nullable()->index('fk_prod_cta_contra');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->softDeletes()->index('idx_productos_deleted');

            $table->unique(['sku'], 'sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
