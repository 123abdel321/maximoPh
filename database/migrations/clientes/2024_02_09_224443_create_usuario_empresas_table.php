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
        Schema::create('usuario_empresas', function (Blueprint $table) {
            $table->id();
            $table->integer('id_usuario');
            $table->integer('id_empresa');
            $table->integer('id_rol');
            $table->integer('id_nit');
            $table->integer('estado')->comment('0: inactivo, 1: activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_empresas');
    }
};
