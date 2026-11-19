<?php

namespace Tests\Feature;

use App\Models\PartidaEscapeRoom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartidaEscapeRoomTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $cambios
     * @return array<string, mixed>
     */
    private function envio(array $cambios = []): array
    {
        return array_replace([
            'codigo' => 'OV-7K3Q-M2PA',
            'acepta_aviso' => true,
            'version_aviso' => 'v1',
            'datos' => [
                'codigo_partida' => 'OV-7K3Q-M2PA',
                'version_web' => '19-11-2026 (contenido 5)',
                'estado' => 'En curso, en el módulo 2',
                'modulos_restaurados' => 1,
                'tiempo_total_min' => null,
            ],
            'opinion' => null,
        ], $cambios);
    }

    public function test_guarda_las_metricas_de_una_partida_nueva(): void
    {
        $this->postJson(route('escape-room.partidas.store'), $this->envio())->assertCreated();

        $partida = PartidaEscapeRoom::sole();
        $this->assertSame('OV-7K3Q-M2PA', $partida->codigo);
        $this->assertSame('En curso, en el módulo 2', $partida->estado);
        $this->assertFalse($partida->finalizada);
        $this->assertSame(1, $partida->datos['modulos_restaurados']);
        $this->assertNull($partida->datos['tiempo_total_min']);
        $this->assertNotNull($partida->fecha_aceptacion);
    }

    public function test_actualiza_la_misma_partida_sin_cambiar_la_fecha_de_aceptacion(): void
    {
        $existente = PartidaEscapeRoom::factory()->create([
            'codigo' => 'OV-7K3Q-M2PA',
            'fecha_aceptacion' => now()->subMinutes(20),
        ]);

        $envio = $this->envio();
        $envio['datos']['estado'] = 'Finalizada';
        $envio['opinion'] = ['cuestionario_version' => 'Propuesta 1', 'item_1' => '5', 'pregunta_14' => 'Me ha gustado.'];

        $this->postJson(route('escape-room.partidas.store'), $envio)->assertOk();

        $partida = PartidaEscapeRoom::sole();
        $this->assertTrue($partida->finalizada);
        $this->assertSame('5', $partida->opinion['item_1']);
        $this->assertTrue($partida->fecha_aceptacion->equalTo($existente->fecha_aceptacion));
    }

    public function test_no_guarda_nada_sin_aceptar_el_aviso(): void
    {
        $this->postJson(route('escape-room.partidas.store'), $this->envio(['acepta_aviso' => false]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('acepta_aviso');

        $this->assertDatabaseCount('partidas_escape_room', 0);
    }

    public function test_rechaza_codigos_columnas_y_valores_no_validos(): void
    {
        $envio = $this->envio(['codigo' => 'cualquier-cosa']);
        $envio['datos']['Columna rara'] = 'x';
        $envio['datos']['estado'] = ['no', 'es', 'texto'];

        $this->postJson(route('escape-room.partidas.store'), $envio)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['codigo', 'datos', 'datos.estado']);

        $this->assertDatabaseCount('partidas_escape_room', 0);
    }
}
