<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CambiarPasswordRequest;
use App\Http\Requests\UpdateMiCuentaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MiCuentaController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load('rol', 'fuentes');

        return response()->json($this->format($user));
    }

    public function update(UpdateMiCuentaRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('fotos', 'public');
        }

        unset($data['foto_preview']); // por si viene del form
        $user->update($data);
        $user->load('rol');

        return response()->json($this->format($user));
    }

    public function cambiarPassword(CambiarPasswordRequest $request)
    {
        $user = $request->user();

        if (! Hash::check($request->password_actual, $user->password)) {
            throw ValidationException::withMessages([
                'password_actual' => 'La contraseña actual no es correcta.',
            ]);
        }

        $user->update(['password' => Hash::make($request->password_nuevo)]);

        return response()->json(['mensaje' => 'Contraseña actualizada.']);
    }

    private function format($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'apellidos' => $user->apellidos,
            'email' => $user->email,
            'telefono' => $user->telefono,
            'dni' => $user->dni,
            'foto_url' => $user->foto ? asset('storage/'.$user->foto) : null,
            'rol' => $user->rol ? ['id' => $user->rol->id, 'nombre' => $user->rol->nombre] : null,
        ];
    }
}
