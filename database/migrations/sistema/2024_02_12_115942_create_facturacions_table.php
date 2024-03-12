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
        Schema::create('facturacions', function (Blueprint $table) {
            $table->id();
            $table->integer('id_comprobante');
            $table->integer('id_nit')->nullable();
            $table->date('fecha_manual')->nullable();
            $table->string('token_factura', 255)->nullable();
            $table->decimal('valor', 15);
            $table->decimal('valor_admon', 15)->nullable();
            $table->decimal('valor_intereses', 15)->nullable();
            $table->decimal('valor_anticipos', 15)->nullable();
            $table->decimal('valor_cuotas_multas', 15)->nullable();
            $table->string('mensajes', 500)->nullable();
            $table->boolean('anulado')->default(false);
            $table->boolean('errores')->default(true);
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
        Schema::dropIfExists('facturacions');
    }
};
