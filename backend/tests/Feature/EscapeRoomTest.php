<?php

namespace Tests\Feature;

use Tests\TestCase;

class EscapeRoomTest extends TestCase
{
    public function test_page_loads_the_game_and_its_config(): void
    {
        $response = $this->get(route('escape-room'));

        $response->assertOk();
        $response->assertSee('id="escape-room"', false);
        $response->assertSee('juegos/escape-room/config.json');
        $response->assertSee('juegos/escape-room/escape-room.js');
    }

    public function test_config_has_every_field_the_game_needs(): void
    {
        $config = json_decode(file_get_contents(public_path('juegos/escape-room/config.json')), true, flags: JSON_THROW_ON_ERROR);

        $this->assertNotEmpty($config['enigmas']);
        $this->assertNotEmpty($config['final']['decalogo']);

        foreach ($config['enigmas'] as $enigma) {
            $correctas = array_filter($enigma['opciones'], fn (array $opcion): bool => $opcion['correcta'] ?? false);
            $this->assertCount(1, $correctas, "{$enigma['id']} debe tener exactamente una opción correcta");
            $this->assertNotEmpty($enigma['clave'], "{$enigma['id']} no tiene clave");

            foreach ($enigma['opciones'] as $opcion) {
                if (! ($opcion['correcta'] ?? false)) {
                    $this->assertNotEmpty($opcion['motivo'] ?? null, "{$enigma['id']}: falta el motivo de una opción incorrecta");
                }
            }
            $this->assertNotEmpty($enigma['documento']['cuerpo'], "{$enigma['id']} no tiene documento");

            if ($enigma['tipo'] === 'trampa') {
                $this->assertArrayHasKey('trampa', $enigma);
            }
        }
    }
}
