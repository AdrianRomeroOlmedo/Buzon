<?php

namespace Tests\Feature;

use Tests\TestCase;

class EscapeRoomTest extends TestCase
{
    public function test_route_redirects_to_the_static_game(): void
    {
        $this->get(route('escape-room'))->assertRedirect('/juegos/escape-room/index.html');
    }

    public function test_static_game_loads_its_content_and_logic(): void
    {
        $carpeta = public_path('juegos/escape-room');
        $indice = file_get_contents("{$carpeta}/index.html");

        foreach (['content.js', 'app.js', 'styles.css'] as $archivo) {
            $this->assertFileExists("{$carpeta}/{$archivo}");
            $this->assertStringContainsString("\"{$archivo}\"", $indice, "index.html no carga {$archivo}");
        }

        $this->assertLessThan(
            strpos($indice, '"app.js"'),
            strpos($indice, '"content.js"'),
            'content.js tiene que cargarse antes que app.js'
        );
    }
}
