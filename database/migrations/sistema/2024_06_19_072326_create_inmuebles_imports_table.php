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
        Schema::create('inmuebles_imports', function (Blueprint $table) {
            $table->id();
            $table->integer('id_inmueble')->nullable();
            $table->integer('id_zona')->nullable();
            $table->integer('id_nit')->nullable();
            $table->integer('id_concepto_facturacion')->nullable();
            $table->string('nombre_concepto_facturacion', 200)->nullable();
            $table->string('nombre_inmueble', 200)->nullable();
            $table->string('nombre_zona', 200)->nullable();
            $table->string('area', 200)->nullable();
            $table->string('coheficiente', 200)->nullable();
            $table->decimal('porcentaje_aumento', 15)->nullable();
            $table->decimal('valor_aumento', 15)->nullable();
            $table->string('nombre_nit', 200)->nullable();
            $table->string('numero_documento', 200)->nullable();
            $table->integer('tipo')->nullable();
            $table->decimal('porcentaje_administracion', 15)->nullable();
            $table->decimal('valor_administracion', 15)->nullable();
            $table->mediumText('observacion')->nullable();
            $table->integer('estado')->default(0)->comment('0: Con errores; 1: Recibo nuevo;');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inmuebles_imports');
    }
};
