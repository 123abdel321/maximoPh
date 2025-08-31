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
        Schema::create('inmueble_nits', function (Blueprint $table) {
            $table->id();
            $table->integer('id_nit');
            $table->integer('id_inmueble');
            $table->decimal('porcentaje_administracion', 15)->nullable();
            $table->decimal('valor_total', 15)->nullable();
            $table->boolean('tipo')->nullable()->default(0)->comment('0 - PROPIETARIO; 1 - INQUILINO;');
            $table->boolean('paga_administracion')->nullable()->default(1)->comment('0 - NO; 1 - SI;');
            $table->boolean('enviar_notificaciones_mail')->nullable()->default(0)->comment('0 - NO; 1 - SI;');
            $table->boolean('enviar_notificaciones_fisica')->nullable()->default(0)->comment('0 - NO; 1 - SI;');
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
        Schema::dropIfExists('inmueble_nits');
    }
};
