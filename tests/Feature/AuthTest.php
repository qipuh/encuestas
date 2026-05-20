<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function crearAdmin(): User
    {
        $rol = Rol::factory()->administrador()->create();

        return User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('secret123'),
            'role_id' => $rol->id,
            'habilitado' => true,
        ]);
    }

    public function test_login_exitoso(): void
    {
        $this->crearAdmin();

        $response = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'email', 'rol']]);
    }

    public function test_login_credenciales_incorrectas(): void
    {
        $this->crearAdmin();

        $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'mal_password',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_usuario_deshabilitado(): void
    {
        $rol = Rol::factory()->administrador()->create();
        User::factory()->create([
            'email' => 'inactivo@test.com',
            'password' => bcrypt('secret123'),
            'role_id' => $rol->id,
            'habilitado' => false,
        ]);

        $this->postJson('/api/login', [
            'email' => 'inactivo@test.com',
            'password' => 'secret123',
        ])->assertStatus(422);
    }

    public function test_logout_revoca_token(): void
    {
        $user = $this->crearAdmin();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk();

        // Verificar que el token fue eliminado de la BD
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_endpoint_user_devuelve_datos_autenticado(): void
    {
        $user = $this->crearAdmin();

        $this->actingAs($user)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('email', 'admin@test.com');
    }

    public function test_endpoint_protegido_sin_token(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }
}
