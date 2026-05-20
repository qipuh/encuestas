<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('encuestas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 160);
            $table->foreignId('fuente_id')->constrained('fuentes')->cascadeOnDelete();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('pregunta_principal');
            $table->text('pregunta_positiva')->nullable();
            $table->text('pregunta_neutral')->nullable();
            $table->text('pregunta_negativa')->nullable();
            $table->enum('estado', ['activa', 'pausada', 'eliminada'])->default('activa');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['fuente_id', 'estado']);
        });

        Schema::create('respuestas_encuesta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encuesta_id')->constrained('encuestas')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('emocion', ['positiva', 'neutral', 'negativa']);
            $table->text('comentario')->nullable();
            $table->timestamp('fecha_hora')->useCurrent();
            $table->index(['encuesta_id', 'fecha_hora']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('respuestas_encuesta');
        Schema::dropIfExists('encuestas');
    }
};
