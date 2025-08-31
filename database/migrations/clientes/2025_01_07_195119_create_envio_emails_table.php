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
        Schema::create('envio_emails', function (Blueprint $table) {
            $table->id();
            $table->integer('id_empresa')->nullable();
            $table->enum('type', ['email', 'whatsapp'])->nullable()->default('email');
            $table->integer('id_nit')->nullable();
            $table->string('message_id')->nullable();
            $table->string('sg_message_id')->nullable();
            $table->string('email')->nullable();
            $table->string('contexto')->nullable();
            $table->enum('status', ['en_cola', 'enviado', 'abierto', 'rechazado'])->nullable()->comment('Estado del correo: en_cola, enviado, abierto, rechazado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('envio_emails');
    }
};
