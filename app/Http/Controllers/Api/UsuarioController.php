<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('rol')->orderBy('name');

        if ($request->search) {
            $q = $request->search;
            $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%")
                    ->orWhere('dni', 'like', "%$q%");
            });
        }
        if ($request->rol_id) {
            $query->where('role_id', $request->rol_id);
        }
        if ($request->habilitado !== null) {
            $query->where('habilitado', $request->boolean('habilitado'));
        }

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    public function store(StoreUsuarioRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $fuentes = $data['fuentes'] ?? [];
        unset($data['fuentes']);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('fotos', 'public');
        }

        $usuario = User::create($data);
        if ($fuentes) {
            $usuario->fuentes()->sync($fuentes);
        }
        $usuario->load('rol', 'fuentes');

        return response()->json($this->formatUser($usuario), 201);
    }

    public function show(User $usuario)
    {
        $usuario->load('rol', 'fuentes');

        return response()->json($this->formatUser($usuario));
    }

    public function update(UpdateUsuarioRequest $request, User $usuario)
    {
        $data = $request->validated();
        $fuentes = $data['fuentes'] ?? null;
        unset($data['fuentes']);

        if (isset($data['password']) && $data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('fotos', 'public');
        }

        $usuario->update($data);
        if ($fuentes !== null) {
            $usuario->fuentes()->sync($fuentes);
        }
        $usuario->load('rol', 'fuentes');

        return response()->json($this->formatUser($usuario));
    }

    public function destroy(User $usuario)
    {
        $usuario->delete();

        return response()->json(['mensaje' => 'Usuario eliminado.']);
    }

    public function toggleHabilitado(User $usuario)
    {
        $usuario->update(['habilitado' => ! $usuario->habilitado]);

        return response()->json(['habilitado' => $usuario->habilitado]);
    }

    public function roles()
    {
        return response()->json(Rol::all());
    }

    private function formatUser(User $u): array
    {
        return [
            'id' => $u->id,
            'name' => $u->name,
            'apellidos' => $u->apellidos,
            'email' => $u->email,
            'telefono' => $u->telefono,
            'dni' => $u->dni,
            'habilitado' => $u->habilitado,
            'rol' => $u->rol ? ['id' => $u->rol->id, 'nombre' => $u->rol->nombre] : null,
            'foto_url' => $u->foto ? asset('storage/'.$u->foto) : null,
            'ultima_actividad' => $u->ultima_actividad,
            'fuentes' => $u->relationLoaded('fuentes')
                ? $u->fuentes->map(fn($f) => ['id' => $f->id, 'nombre' => $f->nombre])->values()->toArray()
                : [],
        ];
    }
}
