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
        Schema::create('asientos_detalle', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('asiento_id')->index('idx_asientos_detalle_asiento');
            $table->unsignedBigInteger('cuenta_id')->index('idx_asientos_detalle_cuenta');
            $table->decimal('debe', 15, 4)->default(0);
            $table->decimal('haber', 15, 4)->default(0);
            $table->unsignedBigInteger('centro_costo_id')->nullable();
            $table->text('concepto')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asientos_detalle');
    }
};
