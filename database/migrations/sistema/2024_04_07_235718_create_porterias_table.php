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
        Schema::create('porterias', function (Blueprint $table) {
            $table->id();
            $table->integer('id_usuario')->nullable();
            $table->integer('tipo_porteria')->nullable()->default(0)->comment('0 - Persona; 1 - Mascota; 2 - Vehiculo;');
            $table->integer('tipo_vehiculo')->nullable()->default(0)->comment('0 - Ninguno; 1 - Carro; 2 - Moto; 3 - Otros;');
            $table->integer('tipo_mascota')->nullable()->default(0)->comment('0 - Perro; 1 - Gato; 2 - Otros;');
            $table->string('nombre', 600)->nullable();
            $table->string('dias', 100)->nullable();
            $table->string('placa', 100)->nullable();
            $table->date('hoy', 100)->nullable();
            $table->string('observacion', 100)->nullable();
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
        Schema::dropIfExists('porterias');
    }
};