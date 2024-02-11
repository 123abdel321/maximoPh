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
            $table->string('nombre_concepto', 200);
            $table->integer('id_cuenta_ingreso')->nullable();
            $table->integer('id_cuenta_interes')->nullable();
            $table->integer('id_cuenta_cobrar')->nullable();
            $table->integer('id_cuenta_iva')->nullable();
            $table->boolean('intereses')->nullable()->default(0)->comment('0 - No; 1 - Si;');
            $table->decimal('valor', 15)->nullable()->default(0);
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
