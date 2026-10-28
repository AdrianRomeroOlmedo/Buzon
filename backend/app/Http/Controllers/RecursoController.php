<?php

namespace App\Http\Controllers;

use App\Models\Recurso;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecursoController extends Controller
{
    /** Con menos recursos que esto, los filtros por tema estorban más que ayudan. */
    private const MINIMO_PARA_FILTROS = 6;

    public function index(Request $request): View
    {
        $todos = Recurso::publicados()->get();

        // Solo se aceptan temas conocidos y con algún recurso; cualquier otro valor se ignora.
        $temasConRecursos = array_intersect_key(Recurso::TEMAS, $todos->pluck('tema')->flip()->all());
        $tema = array_key_exists((string) $request->query('tema'), $temasConRecursos) ? $request->query('tema') : null;

        return view('recursos.index', [
            'recursos' => $tema ? $todos->where('tema', $tema)->values() : $todos,
            'temas' => $todos->count() >= self::MINIMO_PARA_FILTROS ? $temasConRecursos : [],
            'temaActivo' => $tema,
        ]);
    }

    public function show(Recurso $recurso): View
    {
        abort_unless($recurso->publicado, 404);

        return view('recursos.show', compact('recurso'));
    }
}
