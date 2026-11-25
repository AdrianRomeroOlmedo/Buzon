<?php

namespace Tests\Feature;

use App\Models\PartidaEscapeRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartidasEscapeRoomAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function finalizada(string $codigo, array $datos): PartidaEscapeRoom
    {
        return PartidaEscapeRoom::factory()->create([
            'codigo' => $codigo,
            'estado' => 'Finalizada',
            'finalizada' => true,
            'datos' => ['codigo_partida' => $codigo, 'estado' => 'Finalizada'] + $datos,
        ]);
    }

    public function test_solo_los_revisores_ven_las_partidas(): void
    {
        $this->get(route('admin.escape-room.partidas'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.escape-room.partidas'))
            ->assertForbidden();
    }

    public function test_resume_tiempos_fallos_y_pistas(): void
    {
        $this->finalizada('OV-AAAA-AAAA', [
            'tiempo_total_min' => 14, 'tiempo_activo_min' => 12, 'pistas_total' => 3, 'pistas_m1' => 1, 'pistas_m2' => 2, 'pistas_m3' => 0,
            'm1_aciertos_primer_intento' => 2, 'm1_rondas_respondidas' => 3, 'm1_respuestas_incorrectas' => 1,
            'm2_respuestas_incorrectas' => 2, 'm3_conexiones_incorrectas' => 4, 'm4_respuestas_incorrectas' => 0,
            'codigos_incorrectos' => 1, 'tiempo_agotado' => 'no', 'bateria_restante' => '06:12',
        ]);
        $this->finalizada('OV-BBBB-BBBB', [
            'tiempo_total_min' => 22, 'pistas_total' => 1,
            'm1_aciertos_primer_intento' => 3, 'm1_rondas_respondidas' => 3, 'm1_respuestas_incorrectas' => 0,
            'tiempo_agotado' => 'si',
        ]);
        PartidaEscapeRoom::factory()->create([
            'estado' => 'En curso, en el módulo 2',
            'datos' => ['estado' => 'En curso, en el módulo 2', 'tiempo_hasta_ahora_min' => 9, 'pistas_total' => 5, 'tiempo_total_min' => null],
        ]);

        $resumen = $this->actingAs(User::factory()->revisor()->create())
            ->get(route('admin.escape-room.partidas'))
            ->assertOk()
            ->assertSee('OV-AAAA-AAAA')
            ->viewData('resumen');

        $this->assertSame(3, $resumen['total']);
        $this->assertSame(2, $resumen['finalizadas']);
        $this->assertSame(18.0, $resumen['tiempo']['media']);
        $this->assertSame(14, $resumen['tiempo']['minimo']);
        $this->assertSame(2.0, $resumen['pistas_media']); // la partida en curso no cuenta
        $this->assertSame(1, $resumen['tiempo_agotado']);
        $this->assertSame(['En curso, en el módulo 2' => 1], $resumen['abandonos']);

        $filas = collect($resumen['filas'])->keyBy('codigo');
        $this->assertSame(8, $filas['OV-AAAA-AAAA']['fallos_total']);
        $this->assertSame(0, $filas['OV-BBBB-BBBB']['fallos_total']);
        $this->assertNull($filas['OV-BBBB-BBBB']['m3_fallos']);

        $modulo1 = $resumen['modulos'][0];
        $this->assertSame(2, $modulo1['partidas']);
        $this->assertEquals(83, $modulo1['aciertos_porcentaje']); // 5 de 6 rondas
        $this->assertSame(0.5, $modulo1['fallos_media']);
    }

    public function test_filtra_solo_las_finalizadas(): void
    {
        $this->finalizada('OV-AAAA-AAAA', ['tiempo_total_min' => 14]);
        PartidaEscapeRoom::factory()->create(['codigo' => 'OV-CCCC-CCCC']);

        $this->actingAs(User::factory()->revisor()->create())
            ->get(route('admin.escape-room.partidas', ['finalizadas' => 1]))
            ->assertOk()
            ->assertSee('OV-AAAA-AAAA')
            ->assertDontSee('OV-CCCC-CCCC');
    }
}
