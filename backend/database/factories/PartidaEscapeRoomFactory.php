<?php

namespace Database\Factories;

use App\Models\PartidaEscapeRoom;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartidaEscapeRoom>
 */
class PartidaEscapeRoomFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $codigo = strtoupper('OV-'.fake()->unique()->bothify('????-????'));

        return [
            'codigo' => $codigo,
            'version_web' => '19-11-2026 (contenido 5)',
            'estado' => 'En curso, en el módulo 1',
            'finalizada' => false,
            'datos' => ['codigo_partida' => $codigo, 'modulos_restaurados' => 0],
            'opinion' => null,
            'version_aviso' => 'v1',
            'fecha_aceptacion' => now(),
        ];
    }
}
