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
        Schema::create('inf_estadistica_detalles', function (Blueprint $table) {
            $table->id();
            $table->integer('id_estadisticas');
            $table->integer('id_nit')->nullable();
            $table->integer('id_cuenta')->nullable();
            $table->decimal('total_area', 15)->nullable();
            $table->decimal('total_coheficiente', 15)->nullable();
            $table->decimal('saldo_anterior', 15)->nullable();
            $table->decimal('total_facturas', 15)->nullable();
            $table->decimal('total_abono', 15)->nullable();
            $table->decimal('saldo', 15)->nullable();
            $table->string('registros', 15)->nullable();
            $table->string('errores', 15)->nullable();
            $table->string('total', 1)->default('0');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inf_estadistica_detalles');
    }
};
