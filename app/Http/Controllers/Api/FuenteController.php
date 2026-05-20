<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fuente;

class FuenteController extends Controller
{
    public function index()
    {
        return response()->json(Fuente::where('activa', true)->orderBy('nombre')->get(['id', 'nombre', 'distrito']));
    }
}
