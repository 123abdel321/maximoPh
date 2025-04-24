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
        Schema::create('archivos_caches', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_archivo', 20);
            $table->string('relative_path', 255)->nullable();
            $table->string('url_archivo', 255)->nullable();
            $table->string('name_file', 100)->nullable();
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
        Schema::dropIfExists('archivos_caches');
    }
};
