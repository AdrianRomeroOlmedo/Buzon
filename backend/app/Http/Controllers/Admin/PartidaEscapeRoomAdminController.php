<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartidaEscapeRoom;
use App\Support\EstadisticasPartidasEscapeRoom;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartidaEscapeRoomAdminController extends Controller
{
    /**
     * Métricas de juego de las partidas: tiempos, fallos y pistas, en conjunto y partida a partida.
     */
    public function index(Request $request): View
    {
        $soloFinalizadas = $request->boolean('finalizadas');

        $partidas = PartidaEscapeRoom::query()
            ->when($soloFinalizadas, fn ($consulta) => $consulta->where('finalizada', true))
            ->latest('fecha_aceptacion')
            ->get();

        return view('admin.escape-room.partidas', [
            'resumen' => EstadisticasPartidasEscapeRoom::resumen($partidas),
            'modulos' => EstadisticasPartidasEscapeRoom::MODULOS,
            'soloFinalizadas' => $soloFinalizadas,
        ]);
    }
}
