<?php

namespace Tests\Feature;

use App\Models\Comentario;
use App\Models\Fuente;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComentarioTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $rol = Rol::factory()->administrador()->create();
        $this->admin = User::factory()->create(['role_id' => $rol->id]);
    }

    public function test_listar_comentarios_paginado(): void
    {
        Comentario::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->getJson('/api/comentarios')
            ->assertOk()
            ->assertJsonStructure(['data', 'total', 'per_page']);
    }

    public function test_mostrar_comentario(): void
    {
        $comentario = Comentario::factory()->create();

        $this->actingAs($this->admin)
            ->getJson("/api/comentarios/{$comentario->id}")
            ->assertOk()
            ->assertJsonPath('id', $comentario->id);
    }

    public function test_actualizar_estado_comentario(): void
    {
        $comentario = Comentario::factory()->create(['estado' => 'pendiente']);

        $this->actingAs($this->admin)
            ->patchJson("/api/comentarios/{$comentario->id}", [
                'estado' => 'en_proceso',
            ])
            ->assertOk()
            ->assertJsonPath('estado', 'en_proceso');
    }

    public function test_actualizar_estado_invalido(): void
    {
        $comentario = Comentario::factory()->create();

        $this->actingAs($this->admin)
            ->patchJson("/api/comentarios/{$comentario->id}", [
                'estado' => 'estado_invalido',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['estado']);
    }

    public function test_actualizar_datos_cliente(): void
    {
        $comentario = Comentario::factory()->create();

        $this->actingAs($this->admin)
            ->patchJson("/api/comentarios/{$comentario->id}", [
                'cliente_nombre' => 'Juan Pérez',
                'cliente_telefono' => '999888777',
            ])
            ->assertOk()
            ->assertJsonPath('cliente_nombre', 'Juan Pérez');
    }

    public function test_agregar_seguimiento(): void
    {
        $comentario = Comentario::factory()->create(['estado' => 'pendiente']);

        $this->actingAs($this->admin)
            ->postJson("/api/comentarios/{$comentario->id}/seguimientos", [
                'nota' => 'Se contactó al cliente.',
                'accion' => 'en_proceso',
            ])
            ->assertStatus(201)
            ->assertJsonPath('nota', 'Se contactó al cliente.');

        $this->assertDatabaseHas('comentarios', [
            'id' => $comentario->id,
            'estado' => 'en_proceso',
        ]);
    }

    public function test_seguimiento_sin_nota_falla(): void
    {
        $comentario = Comentario::factory()->create();

        $this->actingAs($this->admin)
            ->postJson("/api/comentarios/{$comentario->id}/seguimientos", [
                'accion' => 'resuelto',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nota']);
    }

    public function test_filtrar_comentarios_por_emocion(): void
    {
        Comentario::factory()->positiva()->count(2)->create();
        Comentario::factory()->negativa()->count(1)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/comentarios?emocion=positiva')
            ->assertOk();

        $this->assertCount(2, $response->json('data'));
    }

    public function test_filtrar_comentarios_por_fuente(): void
    {
        $fuente1 = Fuente::factory()->create();
        $fuente2 = Fuente::factory()->create();

        Comentario::factory()->create(['fuente_id' => $fuente1->id]);
        Comentario::factory()->create(['fuente_id' => $fuente2->id]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/comentarios?fuente_id={$fuente1->id}")
            ->assertOk();

        $this->assertCount(1, $response->json('data'));
    }

    public function test_comentarios_no_accesibles_sin_autenticacion(): void
    {
        $this->getJson('/api/comentarios')->assertUnauthorized();
    }
}
