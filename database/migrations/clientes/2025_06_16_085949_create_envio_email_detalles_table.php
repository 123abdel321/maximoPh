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
        Schema::create('envio_email_detalles', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_email');
            $table->string('email')->nullable()->index();
            $table->string('event')->nullable()->index();
            $table->ipAddress('ip')->nullable();
            $table->text('response')->nullable();
            $table->string('sg_event_id')->nullable();
            $table->string('sg_message_id')->nullable()->index();
            $table->string('smtp_id')->nullable();
            $table->unsignedBigInteger('timestamp')->nullable()->index();
            $table->boolean('tls')->nullable();
            $table->timestamps();

            $table->foreign('id_email')
                ->references('id')
                ->on('envio_emails')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('envio_email_detalles');
    }
};
