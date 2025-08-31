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
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();
            $table->integer('id_usuario')->nullable();
            $table->integer('id_nit')->nullable();
            $table->integer('id_proyecto')->nullable();
            $table->integer('tipo')->nullable()->default(0)->comment('0 - Turnos; 1 - Tareas;');
            $table->dateTime('fecha_inicio', precision: 0)->nullable();
            $table->dateTime('fecha_fin', precision: 0)->nullable();
            $table->string('asunto', 200)->nullable();
            $table->longText('descripcion')->nullable();
            $table->integer('estado')->default(0);
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
        Schema::dropIfExists('turnos');
    }
};
