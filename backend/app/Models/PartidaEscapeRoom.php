<?php

namespace App\Models;

use Database\Factories\PartidaEscapeRoomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Métricas de una partida del escape room «Operación Verdad».
 *
 * - El juego envía una instantánea completa cada vez que cambia de pantalla; se guarda la última.
 * - Solo llegan partidas en las que se ha aceptado el aviso de datos del juego.
 */
class PartidaEscapeRoom extends Model
{
    /** @use HasFactory<PartidaEscapeRoomFactory> */
    use HasFactory;

    protected $table = 'partidas_escape_room';

    protected $fillable = [
        'codigo', 'version_web', 'estado', 'finalizada', 'datos', 'opinion',
        'version_aviso', 'fecha_aceptacion',
    ];

    protected function casts(): array
    {
        return [
            'finalizada' => 'boolean',
            'datos' => 'array',
            'opinion' => 'array',
            'fecha_aceptacion' => 'datetime',
        ];
    }
}
