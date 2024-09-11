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
        Schema::create('inf_estadisticas', function (Blueprint $table) {
            $table->id();
            $table->integer('id_empresa');
            $table->integer('id_zona')->nullable();
            $table->integer('id_concepto_facturacion')->nullable();
            $table->integer('id_nit')->nullable();
            $table->date('fecha_desde')->nullable();
            $table->date('fecha_hasta')->nullable();
            $table->string('detalle', 10)->nullable();
            $table->string('agrupar', 10)->nullable();
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inf_estadisticas');
    }
};
