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
        Schema::create('componentes_menus', function (Blueprint $table) {
            $table->id();
            $table->integer('id_padre')->nullable();
            $table->integer('id_componente')->nullable();
            $table->boolean('tipo')->comment('0:menu; 1:submenu');
            $table->string('nombre', 100)->default('');
            $table->string('url', 100)->default('');
            $table->string('icon', 100)->default('');
            $table->string('code_name', 100)->default('');
            $table->boolean('estado')->default(true);
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
        Schema::dropIfExists('componentes_menus');
    }
};
