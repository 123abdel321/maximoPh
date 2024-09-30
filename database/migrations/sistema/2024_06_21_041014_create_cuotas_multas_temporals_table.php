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
        Schema::create('cuotas_multas_temporals', function (Blueprint $table) {
            $table->id();
            $table->integer('id_nit');
            $table->integer('id_inmueble');
            $table->integer('id_cuotas_multas');
            $table->integer('id_concepto_facturacion');
            $table->integer('tipo_concepto')->default(0)->nullable();
            $table->string('fecha_inicio', 10)->nullable();
            $table->string('fecha_fin', 10)->nullable();
            $table->decimal('valor_total', 15)->nullable();
            $table->decimal('valor_coeficiente', 15)->nullable();
            $table->string('observacion', 255)->nullable();
            $table->integer('totales')->default(0)->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuotas_multas_temporals');
    }
};
