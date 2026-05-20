<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AlertaConfig;
use App\Models\Categoria;
use App\Models\ConfiguracionGeneral;
use App\Models\Fuente;
use App\Models\Rol;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function getGeneral()
    {
        $config = ConfiguracionGeneral::first() ?? new ConfiguracionGeneral;

        return response()->json([
            'organizacion'         => $config->organizacion,
            'pais'                 => $config->pais,
            'distrito'             => $config->distrito,
            'telefono'             => $config->telefono,
            'email_responsable'    => $config->email_responsable,
            'logo_url'             => $config->logo_path ? asset('storage/'.$config->logo_path) : null,
            'cierre_sesion'        => $config->cierre_sesion
                                        ? substr($config->cierre_sesion, 0, 5)
                                        : '22:00',
            'hora_limite_login'    => $config->hora_limite_login
                                        ? substr((string) $config->hora_limite_login, 0, 5)
                                        : '08:00',
            'umbral_timeon_bajo'   => $config->umbral_timeon_bajo ?? 5,
            'umbral_timeon_medio'  => $config->umbral_timeon_medio ?? 8,
            'umbral_timeon_alto'   => $config->umbral_timeon_alto ?? 8,
            'umbral_satisfaccion'  => $config->umbral_satisfaccion ?? '60%',
        ]);
    }

    public function saveGeneral(Request $request)
    {
        $data = $request->validate([
            'organizacion'        => 'nullable|string|max:120',
            'pais'                => 'nullable|string|max:80',
            'distrito'            => 'nullable|string|max:80',
            'telefono'            => 'nullable|string|max:20',
            'email_responsable'   => 'nullable|email',
            'cierre_sesion'       => 'nullable|regex:/^\d{1,2}:\d{2}(:\d{2})?$/',
            'hora_limite_login'   => 'nullable|regex:/^\d{1,2}:\d{2}(:\d{2})?$/',
            'umbral_timeon_bajo'  => 'nullable|integer|min:0',
            'umbral_timeon_medio' => 'nullable|integer|min:0',
            'umbral_timeon_alto'  => 'nullable|integer|min:0',
            'umbral_satisfaccion' => 'nullable|string|max:5',
            'logo_path'           => 'nullable|image|mimes:jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('logo_path')) {
            $data['logo_path'] = $request->file('logo_path')->store('logos', 'public');
        }

        // Normalizar HH:MM:SS → HH:MM
        foreach (['cierre_sesion', 'hora_limite_login'] as $campo) {
            if (!empty($data[$campo])) {
                $data[$campo] = substr($data[$campo], 0, 5);
            }
        }

        ConfiguracionGeneral::updateOrCreate(['id' => 1], $data);

        return $this->getGeneral();
    }

    public function saveRoles(Request $request)
    {
        $data = $request->validate([
            'roles' => 'required|array',
            'roles.*.id' => 'required',
            'roles.*.nombre' => 'required|string|max:60',
            'roles.*.permisos' => 'nullable|array',
        ]);

        foreach ($data['roles'] as $rolData) {
            $id = $rolData['id'];
            if (is_numeric($id)) {
                Rol::where('id', $id)->update([
                    'nombre' => $rolData['nombre'],
                    'permisos' => $rolData['permisos'] ?? [],
                ]);
            } else {
                Rol::create([
                    'nombre' => $rolData['nombre'],
                    'permisos' => $rolData['permisos'] ?? [],
                ]);
            }
        }

        return response()->json(['mensaje' => 'Roles actualizados.']);
    }

    public function getAlertas()
    {
        $alertas = AlertaConfig::all()->keyBy('emocion');

        return response()->json($alertas);
    }

    public function saveAlertas(Request $request)
    {
        $data = $request->validate([
            'alertas' => 'required|array',
            'alertas.*.emocion' => 'required|in:positiva,neutral,negativa',
            'alertas.*.activa' => 'boolean',
            'alertas.*.destinatarios_ids' => 'nullable|array',
        ]);

        foreach ($data['alertas'] as $alerta) {
            AlertaConfig::updateOrCreate(
                ['emocion' => $alerta['emocion']],
                ['activa' => $alerta['activa'] ?? false, 'destinatarios_ids' => $alerta['destinatarios_ids'] ?? []]
            );
        }

        return response()->json(['mensaje' => 'Alertas actualizadas.']);
    }

    public function getFuentes()
    {
        return response()->json(Fuente::orderBy('nombre')->get());
    }

    public function storeFuente(Request $request)
    {
        $data = $request->validate(['nombre' => 'required|string|max:120', 'descripcion' => 'nullable|string']);

        return response()->json(Fuente::create($data), 201);
    }

    public function updateFuente(Request $request, Fuente $fuente)
    {
        $data = $request->validate(['nombre' => 'sometimes|string|max:120', 'descripcion' => 'nullable|string']);
        $fuente->update($data);

        return response()->json($fuente);
    }

    public function destroyFuente(Fuente $fuente)
    {
        $fuente->delete();

        return response()->json(['mensaje' => 'Fuente eliminada.']);
    }

    public function getCategorias()
    {
        return response()->json(Categoria::orderBy('nombre')->get());
    }

    public function storeCategoria(Request $request)
    {
        $data = $request->validate(['nombre' => 'required|string|max:120', 'descripcion' => 'nullable|string']);

        return response()->json(Categoria::create($data), 201);
    }

    public function updateCategoria(Request $request, Categoria $categoria)
    {
        $data = $request->validate(['nombre' => 'sometimes|string|max:120', 'descripcion' => 'nullable|string']);
        $categoria->update($data);

        return response()->json($categoria);
    }

    public function destroyCategoria(Categoria $categoria)
    {
        $categoria->delete();

        return response()->json(['mensaje' => 'Categoría eliminada.']);
    }
}
