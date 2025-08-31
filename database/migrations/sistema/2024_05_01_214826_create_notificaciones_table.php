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
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->integer('tipo')->default(0)->comment('0: Success; 1: Info; 2: Warning; 3: Danger');
            $table->integer('id_usuario')->nullable();
            $table->integer('id_rol')->nullable();
            $table->string('mensaje', 200)->nullable();
            $table->string('menu', 50)->nullable();
            $table->string('function', 50)->nullable();
            $table->longText('data')->nullable();
            $table->integer('estado')->default(0)->comment('0: Sin leer; 1: Mensaje leido; 2: Eliminado');
            $table->integer('notificacion_id');
            $table->integer('notificacion_type');
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
        Schema::dropIfExists('notificaciones');
    }
};
