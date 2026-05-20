<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('alertas_config', function (Blueprint $table) {
            $table->id();
            $table->enum('emocion', ['positiva', 'neutral', 'negativa']);
            $table->boolean('activa')->default(true);
            $table->json('destinatarios_ids')->nullable();
            $table->timestamps();
            $table->unique('emocion');
        });

        Schema::create('configuracion_general', function (Blueprint $table) {
            $table->id();
            $table->string('organizacion', 120)->nullable();
            $table->string('pais', 80)->nullable();
            $table->string('distrito', 80)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email_responsable')->nullable();
            $table->integer('cierre_sesion_minutos')->default(120);
            $table->time('hora_limite_login')->nullable();
            $table->integer('umbral_tiempo_atencion')->default(30);
            $table->integer('umbral_satisfaccion_bajo')->default(60);
            $table->timestamps();
        });

        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('titulo', 160);
            $table->text('mensaje');
            $table->boolean('leida')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'leida']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
        Schema::dropIfExists('configuracion_general');
        Schema::dropIfExists('alertas_config');
    }
};
