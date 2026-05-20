<?php

namespace Tests\Feature;

use App\Models\Encuesta;
use App\Models\Fuente;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EncuestaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $rol = Rol::factory()->administrador()->create();
        $this->admin = User::factory()->create(['role_id' => $rol->id]);
    }

    public function test_listar_encuestas_paginado(): void
    {
        Encuesta::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->getJson('/api/encuestas')
            ->assertOk()
            ->assertJsonStructure(['data', 'total', 'per_page', 'current_page']);
    }

    public function test_crear_encuesta_valida(): void
    {
        $fuente = Fuente::factory()->create();

        $this->actingAs($this->admin)
            ->postJson('/api/encuestas', [
                'nombre' => 'Encuesta de prueba',
                'fuente_id' => $fuente->id,
                'pregunta_principal' => '¿Cómo calificarías el servicio?',
            ])
            ->assertStatus(201)
            ->assertJsonPath('nombre', 'Encuesta de prueba')
            ->assertJsonPath('estado', 'activa');
    }

    public function test_crear_encuesta_falla_sin_nombre(): void
    {
        $fuente = Fuente::factory()->create();

        $this->actingAs($this->admin)
            ->postJson('/api/encuestas', [
                'fuente_id' => $fuente->id,
                'pregunta_principal' => '¿Cómo te fue?',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    public function test_crear_encuesta_falla_fuente_inexistente(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/encuestas', [
                'nombre' => 'Test',
                'fuente_id' => 9999,
                'pregunta_principal' => '¿Cómo te fue?',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['fuente_id']);
    }

    public function test_mostrar_encuesta(): void
    {
        $encuesta = Encuesta::factory()->create();

        $this->actingAs($this->admin)
            ->getJson("/api/encuestas/{$encuesta->id}")
            ->assertOk()
            ->assertJsonPath('id', $encuesta->id);
    }

    public function test_actualizar_encuesta(): void
    {
        $encuesta = Encuesta::factory()->create(['nombre' => 'Nombre original']);

        $this->actingAs($this->admin)
            ->putJson("/api/encuestas/{$encuesta->id}", [
                'nombre' => 'Nombre actualizado',
            ])
            ->assertOk()
            ->assertJsonPath('nombre', 'Nombre actualizado');
    }

    public function test_toggle_pausa(): void
    {
        $encuesta = Encuesta::factory()->create(['estado' => 'activa']);

        $this->actingAs($this->admin)
            ->patchJson("/api/encuestas/{$encuesta->id}/toggle-pause")
            ->assertOk()
            ->assertJsonPath('estado', 'pausada');

        $this->actingAs($this->admin)
            ->patchJson("/api/encuestas/{$encuesta->id}/toggle-pause")
            ->assertOk()
            ->assertJsonPath('estado', 'activa');
    }

    public function test_eliminar_encuesta_marca_estado_eliminada(): void
    {
        $encuesta = Encuesta::factory()->create();

        $this->actingAs($this->admin)
            ->deleteJson("/api/encuestas/{$encuesta->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Encuesta eliminada');

        $this->assertDatabaseHas('encuestas', [
            'id' => $encuesta->id,
            'estado' => 'eliminada',
        ]);
    }

    public function test_encuestas_no_accesibles_sin_autenticacion(): void
    {
        $this->getJson('/api/encuestas')->assertUnauthorized();
    }

    public function test_listar_encuestas_con_filtro_fuente(): void
    {
        $fuente1 = Fuente::factory()->create();
        $fuente2 = Fuente::factory()->create();
        Encuesta::factory()->create(['fuente_id' => $fuente1->id]);
        Encuesta::factory()->create(['fuente_id' => $fuente2->id]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/encuestas?fuente_id={$fuente1->id}")
            ->assertOk();

        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($fuente1->id, $response->json('data.0.fuente.id'));
    }
}
