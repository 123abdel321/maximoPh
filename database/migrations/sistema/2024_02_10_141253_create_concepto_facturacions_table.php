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
        Schema::create('concepto_facturacions', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50);
            $table->string('nombre_concepto', 200);
            $table->integer('id_cuenta_ingreso')->nullable();
            $table->integer('id_cuenta_interes')->nullable();
            $table->integer('id_cuenta_cobrar')->nullable();
            $table->integer('id_cuenta_iva')->nullable();
            $table->integer('id_nit_cuenta_ingreso')->nullable();
            $table->boolean('intereses')->nullable()->default(0)->comment('0 - No; 1 - Si;');
            $table->boolean('pronto_pago')->nullable()->default(0)->comment('0 - No; 1 - Si;');
            $table->boolean('tipo_concepto')->nullable()->default(0)->comment('0 - Facturación; 1 - Cuotas extras & multas;');
            $table->decimal('valor', 15)->nullable()->default(0);
            $table->integer('id_cuenta_gasto')->nullable();
            $table->integer('id_cuenta_anticipo')->nullable();
            $table->integer('dias_pronto_pago')->nullable()->default(0);
            $table->integer('porcentaje_pronto_pago')->nullable()->default(0);
            $table->integer('orden')->nullable()->default(0);
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
        Schema::dropIfExists('concepto_facturacions');
    }
};
