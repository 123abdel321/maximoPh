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
        Schema::create('facturacion_detalles', function (Blueprint $table) {
            $table->id();
            $table->integer('id_factura')->nullable();
            $table->integer('id_nit')->nullable();
            $table->integer('id_concepto_facturacion')->nullable();
            $table->integer('id_cuenta_por_cobrar')->nullable();
            $table->integer('id_cuenta_ingreso')->nullable();
            $table->integer('id_comprobante')->nullable();
            $table->integer('id_centro_costos')->nullable();
            $table->date('fecha_manual')->nullable();
            $table->string('documento_referencia', 20)->nullable();
            $table->string('documento_referencia_anticipo', 20)->nullable();
            $table->decimal('saldo', 15)->default(0)->nullable();
            $table->decimal('valor', 15)->default(0)->nullable();
            $table->string('concepto', 600)->nullable();
            $table->boolean('naturaleza_opuesta')->default(false)->nullable();
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
        Schema::dropIfExists('facturacion_detalles');
    }
};
