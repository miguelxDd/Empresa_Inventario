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
        Schema::create('bodegas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codigo', 20)->unique('codigo');
            $table->string('nombre', 100);
            $table->text('ubicacion')->nullable();
            $table->boolean('activa')->default(true)->index('idx_bodegas_activa');
            $table->unsignedBigInteger('responsable_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            $table->softDeletes()->index('idx_bodegas_deleted');

            $table->index(['codigo'], 'idx_bodegas_codigo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bodegas');
    }
};
