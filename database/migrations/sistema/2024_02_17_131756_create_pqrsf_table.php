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
        Schema::create('pqrsf', function (Blueprint $table) {
            $table->id();
            $table->integer('id_usuario')->nullable();
            $table->integer('id_nit')->nullable();
            $table->integer('id_rol')->nullable();
            $table->integer('id_inmueble')->nullable();
            $table->integer('tipo')->nullable()->default(0)->comment('0 - Pregunta; 1 - Queja; 2 - Reclamo; 3 - Solicitud; 4 - Felicitacion');
            $table->integer('area')->nullable()->default(0)->comment('0 - Ninguna; 1 - Administración; 2 - Seguridad; 3 - Aseo; 4 - Mantenimiento; 5 - Zonas comunes');
            $table->string('dias', 100)->nullable();
            $table->date('hoy', 100)->nullable();
            $table->string('asunto', 200)->nullable();
            $table->longText('descripcion')->nullable();
            $table->integer('estado')->default(0)->comment('0: Activo; 1: En proceso; 2: Cerrado;');
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
        Schema::dropIfExists('pqrsf');
    }
};
