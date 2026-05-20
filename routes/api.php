<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EncuestaController;
use App\Http\Controllers\Api\RespuestaController;
use App\Http\Controllers\Api\ComentarioController;
use App\Http\Controllers\Api\ReporteController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\ConfiguracionController;
use App\Http\Controllers\Api\MiCuentaController;
use App\Http\Controllers\Api\FuenteController;
use App\Http\Controllers\Api\CategoriaController;

// ── Broadcasting auth con Bearer token (Sanctum) ─────
Route::post('/broadcasting/auth', function (\Illuminate\Http\Request $request) {
    return \Illuminate\Support\Facades\Broadcast::auth($request);
})->middleware('auth:sanctum');

// ── Público ──────────────────────────────────────────
Route::middleware('throttle:login')->post('/login', [AuthController::class, 'login']);
Route::get('/reportes/kpi-publico', [ReporteController::class, 'kpiPublico']);

// ── Autenticado ───────────────────────────────────────
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Fuentes y categorías (lookups)
    Route::get('/fuentes', [FuenteController::class, 'index']);
    Route::get('/categorias', [CategoriaController::class, 'index']);

    // Encuestas
    Route::apiResource('encuestas', EncuestaController::class);
    Route::patch('/encuestas/{encuesta}/toggle-pause', [EncuestaController::class, 'togglePause']);
    Route::get('/encuestas-activa-para-fuente', [EncuestaController::class, 'activaParaFuente']);

    // Respuestas
    Route::middleware('throttle:respuestas')->group(function () {
        Route::post('/respuestas', [RespuestaController::class, 'store']);
        Route::post('/respuestas/sync-offline', [RespuestaController::class, 'syncOffline']);
    });

    // Comentarios
    Route::get('/comentarios', [ComentarioController::class, 'index']);
    Route::middleware('throttle:exportar')->get('/comentarios/exportar', [ComentarioController::class, 'exportar']);
    Route::get('/comentarios/{comentario}', [ComentarioController::class, 'show']);
    Route::patch('/comentarios/{comentario}', [ComentarioController::class, 'update']);
    Route::post('/comentarios/{comentario}/seguimientos', [ComentarioController::class, 'agregarSeguimiento']);

    // Reportes
    Route::get('/reportes/dashboard', [ReporteController::class, 'dashboard']);
    Route::get('/reportes/satisfaccion-diaria', [ReporteController::class, 'satisfaccionDiaria']);

    // Usuarios
    Route::get('/roles', [UsuarioController::class, 'roles']);
    Route::put('/configuracion/roles', [ConfiguracionController::class, 'saveRoles']);
    Route::patch('/usuarios/{usuario}/toggle-habilitado', [UsuarioController::class, 'toggleHabilitado']);
    Route::apiResource('usuarios', UsuarioController::class);

    // Configuración
    Route::get('/configuracion/general', [ConfiguracionController::class, 'getGeneral']);
    Route::put('/configuracion/general', [ConfiguracionController::class, 'saveGeneral']);
    Route::get('/configuracion/alertas', [ConfiguracionController::class, 'getAlertas']);
    Route::put('/configuracion/alertas', [ConfiguracionController::class, 'saveAlertas']);
    Route::get('/configuracion/fuentes', [ConfiguracionController::class, 'getFuentes']);
    Route::post('/configuracion/fuentes', [ConfiguracionController::class, 'storeFuente']);
    Route::put('/configuracion/fuentes/{fuente}', [ConfiguracionController::class, 'updateFuente']);
    Route::delete('/configuracion/fuentes/{fuente}', [ConfiguracionController::class, 'destroyFuente']);
    Route::get('/configuracion/categorias', [ConfiguracionController::class, 'getCategorias']);
    Route::post('/configuracion/categorias', [ConfiguracionController::class, 'storeCategoria']);
    Route::put('/configuracion/categorias/{categoria}', [ConfiguracionController::class, 'updateCategoria']);
    Route::delete('/configuracion/categorias/{categoria}', [ConfiguracionController::class, 'destroyCategoria']);

    // Mi cuenta
    Route::get('/mi-cuenta', [MiCuentaController::class, 'show']);
    Route::post('/mi-cuenta', [MiCuentaController::class, 'update']);
    Route::put('/mi-cuenta/cambiar-password', [MiCuentaController::class, 'cambiarPassword']);
});
