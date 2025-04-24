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
        Schema::create('novedades', function (Blueprint $table) {
            $table->id();
            $table->integer('id_porteria')->nullable();
            $table->integer('area')->nullable()->comment('1: ADMINISTRACIÓN; 2: SEGURIDAD; 3: ASEO; 4: MANTENIMIENTO; 5: ZONAS COMUNES;');
            $table->integer('tipo')->nullable()->comment('1: MULTAS; 2: NOVEDADES;');
            $table->dateTime('fecha')->nullable();
            $table->string('asunto', 200)->nullable();
            $table->longText('mensaje')->nullable();
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
        Schema::dropIfExists('novedades');
    }
};
