<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('configuracion_general', function (Blueprint $table) {
            $table->string('cierre_sesion', 5)->nullable()->after('email_responsable');
            $table->integer('umbral_timeon_bajo')->nullable()->after('hora_limite_login');
            $table->integer('umbral_timeon_medio')->nullable()->after('umbral_timeon_bajo');
            $table->integer('umbral_timeon_alto')->nullable()->after('umbral_timeon_medio');
            $table->string('umbral_satisfaccion', 5)->nullable()->after('umbral_timeon_alto');
            $table->string('logo_path')->nullable()->after('umbral_satisfaccion');
        });
    }

    public function down(): void
    {
        Schema::table('configuracion_general', function (Blueprint $table) {
            $table->dropColumn(['cierre_sesion', 'umbral_timeon_bajo', 'umbral_timeon_medio', 'umbral_timeon_alto', 'umbral_satisfaccion', 'logo_path']);
        });
    }
};
