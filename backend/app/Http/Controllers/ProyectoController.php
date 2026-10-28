<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use Illuminate\View\View;

class ProyectoController extends Controller
{
    public function index(): View
    {
        $proyectos = Proyecto::publicados()->get();

        // El primero ya viene ordenado como destacado (o el más reciente si no hay ninguno).
        $destacado = $proyectos->first();
        $resto = $proyectos->slice(1)->values();

        return view('proyectos.index', compact('destacado', 'resto'));
    }

    public function show(Proyecto $proyecto): View
    {
        abort_unless($proyecto->publicado, 404);

        return view('proyectos.show', compact('proyecto'));
    }
}
