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
        Schema::create('con_recibos_imports', function (Blueprint $table) {
            $table->id();
            $table->integer('id_inmueble')->nullable();
            $table->integer('id_nit')->nullable();
            $table->string('codigo', 60)->nullable();
            $table->string('numero_documento', 60)->nullable();
            $table->string('nombre_inmueble', 100)->nullable();
            $table->string('nombre_zona', 100)->nullable();
            $table->string('email', 100)->nullable();
            $table->integer('id_concepto_facturacion')->nullable();
            $table->string('numero_concepto_facturacion', 100)->nullable();
            $table->string('nombre_nit', 100)->nullable();
            $table->date('fecha_manual')->nullable();
            $table->decimal('pago', 15)->default(0);
            $table->decimal('descuento', 15)->default(0);
            $table->decimal('faltante_descuento', 15)->default(0);
            $table->decimal('saldo', 15)->default(0);
            $table->decimal('saldo_nuevo', 15)->default(0);
            $table->decimal('anticipos', 15)->default(0);
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
        Schema::dropIfExists('con_recibos_imports');
    }
};
