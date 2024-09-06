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
        Schema::create('cuotas_multas_imports', function (Blueprint $table) {
            $table->id();
            $table->integer('id_nit')->nullable();
            $table->integer('id_inmueble')->nullable();
            $table->integer('id_concepto_facturacion')->nullable();
            $table->string('numero_documento', 200)->nullable();
            $table->string('nombre_inmueble', 200)->nullable();
            $table->string('nombre_nit', 100)->nullable();
            $table->integer('codigo_concepto')->nullable();
            $table->string('fecha_inicio', 10)->nullable();
            $table->string('fecha_fin', 10)->nullable();
            $table->decimal('valor_total', 15)->nullable();
            $table->mediumText('observacion')->nullable();
            $table->integer('estado')->default(0)->comment('0: Con errores; 1: Cuotas extra nuevo;');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuotas_multas_imports');
    }
};
