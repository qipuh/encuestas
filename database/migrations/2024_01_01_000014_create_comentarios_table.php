<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comentarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fuente_id')->constrained('fuentes')->cascadeOnDelete();
            $table->foreignId('encuesta_id')->nullable()->constrained('encuestas')->nullOnDelete();
            $table->foreignId('respuesta_id')->nullable()->constrained('respuestas_encuesta')->nullOnDelete();
            $table->text('comentario');
            $table->enum('emocion', ['positiva', 'neutral', 'negativa']);
            $table->enum('estado', ['pendiente', 'en_proceso', 'resuelto'])->default('pendiente');
            $table->date('fecha');
            $table->time('hora');
            $table->string('cliente_nombre', 120)->nullable();
            $table->string('cliente_dni', 15)->nullable();
            $table->string('cliente_telefono', 20)->nullable();
            $table->timestamps();
            $table->index(['fuente_id', 'estado', 'fecha']);
            $table->index(['emocion', 'fecha']);
        });

        Schema::create('seguimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comentario_id')->constrained('comentarios')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('nota');
            $table->enum('accion', ['pendiente', 'en_proceso', 'resuelto'])->nullable();
            $table->timestamps();
        });

        Schema::create('archivos_seguimiento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seguimiento_id')->constrained('seguimientos')->cascadeOnDelete();
            $table->string('ruta_archivo');
            $table->string('nombre_original', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archivos_seguimiento');
        Schema::dropIfExists('seguimientos');
        Schema::dropIfExists('comentarios');
    }
};
