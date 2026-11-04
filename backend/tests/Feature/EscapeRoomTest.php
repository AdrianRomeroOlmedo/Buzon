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

        $this->assertNotEmpty($config['fases']);
        $this->assertNotEmpty($config['final']['decalogo']);
        $this->assertNotEmpty($config['final']['competencias']);

        foreach ($config['fases'] as $fase) {
            $this->assertCount(3, $fase['noticias'], "{$fase['id']} debe tener tres noticias");

            $reales = array_filter($fase['noticias'], fn (array $noticia): bool => $noticia['real']);
            $this->assertCount(1, $reales, "{$fase['id']} debe tener exactamente una noticia real");

            foreach ($fase['noticias'] as $noticia) {
                $this->assertNotEmpty($noticia['titular'], "{$fase['id']}: falta un titular");
                $this->assertNotEmpty($noticia['narrativa'], "{$fase['id']}: falta la narrativa de «{$noticia['titular']}»");
                $this->assertNotEmpty($noticia['explicacion'], "{$fase['id']}: falta la explicación de «{$noticia['titular']}»");
            }
        }
    }
}
