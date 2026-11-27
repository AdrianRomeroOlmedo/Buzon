<?php

namespace Tests\Feature;

use Tests\TestCase;

class EscapeRoomTest extends TestCase
{
    public function test_route_redirects_to_the_static_game(): void
    {
        $this->get(route('escape-room'))->assertRedirect('/juegos/escape-room/index.html');
    }

    public function test_route_redirects_to_the_teacher_guide(): void
    {
        $this->get(route('escape-room.guia-docente'))->assertRedirect('/juegos/escape-room/guia-docente/index.html');
    }

    public function test_teacher_guide_files_it_links_to_exist(): void
    {
        $carpeta = public_path('juegos/escape-room/guia-docente');
        $indice = file_get_contents("{$carpeta}/index.html");

        preg_match_all('#(?:src|href)="(?!https?://|\#)([^"?]+)#', $indice, $enlaces);

        $this->assertNotEmpty($enlaces[1]);
        foreach ($enlaces[1] as $ruta) {
            $this->assertFileExists("{$carpeta}/{$ruta}", "La guía enlaza a {$ruta}, que no existe");
        }
    }

    public function test_static_game_loads_its_content_and_logic(): void
    {
        $carpeta = public_path('juegos/escape-room');
        $indice = file_get_contents("{$carpeta}/index.html");

        foreach (['content.js', 'app.js', 'styles.css'] as $archivo) {
            $this->assertFileExists("{$carpeta}/{$archivo}");
            $this->assertMatchesRegularExpression('#"'.preg_quote($archivo, '#').'(\?v=[^"]+)?"#', $indice, "index.html no carga {$archivo}");
        }

        $this->assertLessThan(
            strpos($indice, '"app.js'),
            strpos($indice, '"content.js'),
            'content.js tiene que cargarse antes que app.js'
        );
    }

    public function test_static_game_makes_no_external_requests_on_load(): void
    {
        $carpeta = public_path('juegos/escape-room');

        foreach (['index.html', 'styles.css'] as $archivo) {
            $contenido = file_get_contents("{$carpeta}/{$archivo}");
            $this->assertDoesNotMatchRegularExpression('#(src|href)="https?://|url\(["\']?https?://#i', $contenido, "{$archivo} carga recursos externos");
        }

        foreach (glob("{$carpeta}/assets/fuentes/*.woff2") ?: [] as $fuente) {
            $this->assertStringContainsString(basename($fuente), file_get_contents("{$carpeta}/styles.css"));
        }
        $this->assertFileExists("{$carpeta}/assets/fuentes/OFL.txt");
    }
}
