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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('id_empresa')->nullable();
            $table->string('avatar', 255)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('ids_bodegas_responsable', 255)->nullable();
            $table->string('ids_resolucion_responsable', 255)->nullable();
            $table->string('facturacion_rapida', 255)->nullable();
            $table->string('fondo_sistema', 255)->nullable();
            $table->integer('created_by');
            $table->integer('updated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
