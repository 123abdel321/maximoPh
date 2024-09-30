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
        Schema::create('visitantes', function (Blueprint $table) {
            $table->id();
            $table->integer('id_usuario')->nullable();
            $table->string('ip', 200)->nullable();
            $table->string('device', 200)->nullable();
            $table->string('browser', 200)->nullable();
            $table->string('loc', 200)->nullable();
            $table->string('city', 200)->nullable();
            $table->string('region', 200)->nullable();
            $table->string('country', 200)->nullable();
            $table->string('hostname', 200)->nullable();
            $table->string('org', 200)->nullable();
            $table->string('timezone', 200)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitantes');
    }
};
