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
        Schema::create('roles_generales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60)->nullable();
            $table->integer('id_empresa')->nullable();
            $table->string('ids_permission', 500)->nullable();
            $table->integer('tipo')->comment('0: Estricto, 1: Dinamico');
            $table->integer('estado')->comment('0: Inactivo, 1: Activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles_generales');
    }
};
