<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartidaEscapeRoom;
use App\Support\CuestionarioOpinionEscapeRoom;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OpinionEscapeRoomController extends Controller
{
    /**
     * Resumen del cuestionario de opinión del escape room.
     *
     * Solo se resumen juntas las respuestas de una misma versión del cuestionario: si cambian
     * los ítems, mezclar versiones daría medias sin sentido. Por defecto, la versión actual.
     */
    public function index(Request $request): View
    {
        $respondidas = PartidaEscapeRoom::whereNotNull('opinion')
            ->latest('updated_at')
            ->get()
            ->filter(fn (PartidaEscapeRoom $partida): bool => CuestionarioOpinionEscapeRoom::estaRespondido($partida));

        $versiones = $respondidas
            ->map(fn (PartidaEscapeRoom $partida): string => (string) ($partida->opinion['cuestionario_version'] ?? ''))
            ->countBy()
            ->sortKeys();

        $version = $request->string('version')->toString();
        if (! $versiones->has($version)) {
            $version = $versiones->has(CuestionarioOpinionEscapeRoom::VERSION)
                ? CuestionarioOpinionEscapeRoom::VERSION
                : (string) $versiones->keys()->first();
        }

        $partidas = $respondidas
            ->filter(fn (PartidaEscapeRoom $partida): bool => (string) ($partida->opinion['cuestionario_version'] ?? '') === $version)
            ->values();

        return view('admin.escape-room.opiniones', [
            'resumen' => CuestionarioOpinionEscapeRoom::resumen($partidas),
            'versiones' => $versiones,
            'version' => $version,
            'esVersionActual' => $version === CuestionarioOpinionEscapeRoom::VERSION,
            'escala' => CuestionarioOpinionEscapeRoom::ESCALA,
        ]);
    }
}
