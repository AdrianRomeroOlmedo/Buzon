<?php

namespace Tests\Feature;

use App\Models\PartidaEscapeRoom;
use App\Models\User;
use App\Support\CuestionarioOpinionEscapeRoom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpinionEscapeRoomTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /**
     * @param  array<string, string>  $respuestas
     * @return array<string, string>
     */
    private function opinion(array $respuestas, string $version = CuestionarioOpinionEscapeRoom::VERSION): array
    {
        return ['cuestionario_version' => $version] + $respuestas;
    }

    public function test_solo_los_revisores_ven_las_opiniones(): void
    {
        $this->get(route('admin.escape-room.opiniones'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.escape-room.opiniones'))
            ->assertForbidden();
    }

    public function test_resume_las_respuestas_de_la_version_actual(): void
    {
        PartidaEscapeRoom::factory()->create([
            'opinion' => $this->opinion(['item_1' => '5', 'item_12' => 'NV', 'pregunta_13' => 'La parte de detectar el bulo.']),
        ]);
        PartidaEscapeRoom::factory()->create([
            'opinion' => $this->opinion(['item_1' => '2', 'pregunta_14' => 'Explicaría mejor la clasificación.']),
        ]);
        PartidaEscapeRoom::factory()->create(['opinion' => $this->opinion(['item_1' => '1'], 'Versión antigua')]);
        PartidaEscapeRoom::factory()->create(['opinion' => $this->opinion(['item_1' => '', 'pregunta_13' => ''])]);
        PartidaEscapeRoom::factory()->create(['opinion' => null]);

        $respuesta = $this->actingAs(User::factory()->revisor()->create())
            ->get(route('admin.escape-room.opiniones'))
            ->assertOk()
            ->assertSee('La parte de detectar el bulo.')
            ->assertSee('Explicaría mejor la clasificación.');

        $resumen = $respuesta->viewData('resumen');
        $this->assertSame(2, $resumen['total']);
        $this->assertSame(3.5, $resumen['items'][0]['media']);
        $this->assertSame([1 => 0, 2 => 1, 3 => 0, 4 => 0, 5 => 1], $resumen['items'][0]['conteos']);
        $this->assertSame(1, $resumen['items'][11]['nv']);
        $this->assertSame(1, $resumen['items'][11]['vacias']);
        $this->assertNull($resumen['items'][11]['media']);
    }

    public function test_permite_ver_otra_version_del_cuestionario(): void
    {
        PartidaEscapeRoom::factory()->create(['opinion' => $this->opinion(['item_1' => '4'])]);
        PartidaEscapeRoom::factory()->create(['opinion' => $this->opinion(['item_1' => '1'], 'Versión antigua')]);

        $resumen = $this->actingAs(User::factory()->revisor()->create())
            ->get(route('admin.escape-room.opiniones', ['version' => 'Versión antigua']))
            ->assertOk()
            ->viewData('resumen');

        $this->assertSame(1, $resumen['total']);
        $this->assertSame(1.0, $resumen['items'][0]['media']);
    }

    public function test_marca_a_quien_pone_la_misma_nota_en_todo(): void
    {
        $todoCinco = collect(range(1, 12))->mapWithKeys(fn (int $n): array => ["item_{$n}" => '5'])->all();
        PartidaEscapeRoom::factory()->create(['codigo' => 'OV-AAAA-AAAA', 'opinion' => $this->opinion($todoCinco)]);
        PartidaEscapeRoom::factory()->create(['codigo' => 'OV-BBBB-BBBB', 'opinion' => $this->opinion(['item_1' => '5', 'item_2' => '5'])]);

        $partidas = collect($this->actingAs(User::factory()->revisor()->create())
            ->get(route('admin.escape-room.opiniones'))
            ->viewData('resumen')['partidas'])->keyBy('codigo');

        $this->assertTrue($partidas['OV-AAAA-AAAA']['todas_iguales']);
        $this->assertFalse($partidas['OV-BBBB-BBBB']['todas_iguales']);
    }

    public function test_los_textos_coinciden_con_los_del_juego(): void
    {
        $contenido = file_get_contents(public_path('juegos/escape-room/content.js'));

        $this->assertStringContainsString('version: "'.CuestionarioOpinionEscapeRoom::VERSION.'"', $contenido);
        foreach ([...CuestionarioOpinionEscapeRoom::ITEMS, ...CuestionarioOpinionEscapeRoom::ABIERTAS, ...CuestionarioOpinionEscapeRoom::ESCALA] as $texto) {
            $this->assertStringContainsString('"'.$texto.'"', $contenido);
        }
    }
}
