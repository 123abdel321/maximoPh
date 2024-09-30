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
        Schema::create('archivos_generales', function (Blueprint $table) {
            $table->id();
            $table->integer('relation_id');
            $table->integer('relation_type');
            $table->string('tipo_archivo', 20);
            $table->string('url_archivo', 600)->nullable();
            $table->boolean('estado')->default(true)->comment('0:Inactivo, 1:Activo');
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
        Schema::dropIfExists('archivos_generales');
    }
};
