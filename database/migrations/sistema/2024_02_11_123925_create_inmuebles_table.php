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
        Schema::create('inmuebles', function (Blueprint $table) {
            $table->id();
            $table->integer('id_zona');
            $table->integer('id_concepto_facturacion');
            $table->string('area', 200)->nullable();
            $table->string('nombre', 200)->nullable();
            $table->string('coeficiente', 200)->nullable();
            $table->string('observaciones', 200)->nullable();
            $table->decimal('valor_total_administracion', 15)->nullable();
            $table->date('fecha_entrega')->nullable();
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
        Schema::dropIfExists('inmuebles');
    }
};
