<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('email')->constrained('roles')->nullOnDelete();
            $table->string('apellidos', 100)->nullable()->after('name');
            $table->string('telefono', 20)->nullable()->after('apellidos');
            $table->string('dni', 15)->nullable()->after('telefono');
            $table->string('foto')->nullable()->after('dni');
            $table->timestamp('ultima_actividad')->nullable()->after('foto');
            $table->boolean('habilitado')->default(true)->after('ultima_actividad');
            $table->softDeletes();
        });

        Schema::create('user_fuentes', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('fuente_id')->constrained('fuentes')->cascadeOnDelete();
            $table->primary(['user_id', 'fuente_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_fuentes');
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['role_id','apellidos','telefono','dni','foto','ultima_actividad','habilitado']);
        });
    }
};
