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
        Schema::create('asientos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('fecha')->index('idx_asientos_fecha');
            $table->string('numero', 50)->index('idx_asientos_numero');
            $table->text('descripcion');
            $table->string('origen_tabla', 50)->nullable();
            $table->unsignedBigInteger('origen_id')->nullable();
            $table->enum('estado', ['borrador', 'confirmado', 'anulado'])->default('borrador')->index('idx_asientos_estado');
            $table->decimal('total_debe', 15, 4)->default(0);
            $table->decimal('total_haber', 15, 4)->default(0);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();

            $table->index(['origen_tabla', 'origen_id'], 'idx_asientos_origen');
            $table->unique(['numero'], 'numero');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asientos');
    }
};
