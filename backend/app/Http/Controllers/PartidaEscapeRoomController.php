<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarPartidaEscapeRoomRequest;
use App\Models\PartidaEscapeRoom;
use Illuminate\Http\JsonResponse;

class PartidaEscapeRoomController extends Controller
{
    /**
     * Guarda o actualiza las métricas de una partida del escape room.
     *
     * El juego manda la instantánea completa en cada cambio de pantalla, así que basta con quedarse
     * con la última. La fecha de aceptación del aviso es la de la primera vez que llega la partida.
     */
    public function store(GuardarPartidaEscapeRoomRequest $request): JsonResponse
    {
        $datos = $request->validated();
        $estado = isset($datos['datos']['estado']) ? (string) $datos['datos']['estado'] : null;
        $versionWeb = isset($datos['datos']['version_web']) ? (string) $datos['datos']['version_web'] : null;

        $partida = PartidaEscapeRoom::firstOrNew(['codigo' => $datos['codigo']]);
        $partida->fill([
            'version_web' => $versionWeb !== null ? mb_substr($versionWeb, 0, 60) : null,
            'estado' => $estado !== null ? mb_substr($estado, 0, 40) : null,
            'finalizada' => $estado === 'Finalizada',
            'datos' => $datos['datos'],
            'opinion' => $datos['opinion'] ?? null,
            'version_aviso' => $datos['version_aviso'],
        ]);
        $partida->fecha_aceptacion ??= now();
        $partida->save();

        return response()->json(['guardado' => true], $partida->wasRecentlyCreated ? 201 : 200);
    }
}
