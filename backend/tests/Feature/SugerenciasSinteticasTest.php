<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\HistorialSugerencia;
use App\Models\PropuestaIa;
use App\Models\Sugerencia;
use App\Support\GeneradorSugerenciasSinteticas;
use Database\Seeders\CategoriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SugerenciasSinteticasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategoriaSeeder::class);
    }

    private function crearReal(): Sugerencia
    {
        $urbanismo = Categoria::where('nombre', 'Urbanismo')->firstOrFail();

        return Sugerencia::create([
            'fecha_recepcion' => now(),
            'categoria_id' => $urbanismo->id,
            'categoria_ciudadano_id' => $urbanismo->id,
            'estado' => 'recibido',
            'version_aviso_privacidad' => 'v1',
            'fecha_aceptacion' => now(),
        ]);
    }

    public function test_creates_pending_suggestions_exactly_like_the_public_form(): void
    {
        $ids = GeneradorSugerenciasSinteticas::crear(60);

        $sugerencias = Sugerencia::with(['datoOriginal', 'subcategorias', 'contacto'])->orderBy('id')->get();

        $this->assertSame($ids, $sugerencias->pluck('id')->all());
        foreach ($sugerencias as $s) {
            $this->assertSame('pendiente', $s->refresh()->ia_estado);
            $this->assertSame('recibido', $s->estado);
            $this->assertNotEmpty($s->datoOriginal->texto_original);
            $this->assertStringNotContainsString('{zona}', $s->datoOriginal->texto_original);
            $this->assertNotEmpty($s->subcategorias_ciudadano);
            $this->assertCount(count($s->subcategorias_ciudadano), $s->subcategorias);
            $this->assertNull($s->contacto);
            // Sin rastro del momento real de creación.
            $this->assertTrue($s->created_at->equalTo($s->fecha_recepcion));
            $this->assertTrue($s->datoOriginal->created_at->equalTo($s->fecha_recepcion));
            $this->assertTrue($s->fecha_recepcion->isPast());
        }
        $this->assertSame(
            $sugerencias->pluck('fecha_recepcion')->map->timestamp->all(),
            $sugerencias->pluck('fecha_recepcion')->map->timestamp->sort()->values()->all(),
        );
        $this->assertSame(
            $sugerencias->pluck('fecha_recepcion')->map->toDateTimeString()->sort()->values()->all(),
            DB::table('sugerencia_subcategoria')->whereIn('sugerencia_id', $ids)->distinct()->orderBy('created_at')->pluck('created_at')->map(fn ($f) => substr($f, 0, 19))->unique()->values()->all(),
        );
    }

    public function test_command_prints_the_ids_and_validates_the_amount(): void
    {
        $this->artisan('buzon:sinteticas', ['cantidad' => 3, '--force' => true])
            ->expectsOutputToContain('1-3')
            ->assertSuccessful();

        $this->artisan('buzon:sinteticas', ['cantidad' => '0', '--force' => true])->assertFailed();
        $this->artisan('buzon:sinteticas', ['cantidad' => 'muchas', '--force' => true])->assertFailed();
        $this->assertSame(3, Sugerencia::count());
    }

    public function test_deleting_by_ids_removes_only_those_and_everything_attached(): void
    {
        $real = $this->crearReal();
        $ids = GeneradorSugerenciasSinteticas::crear(4);
        $sintetica = Sugerencia::find($ids[0]);
        PropuestaIa::create(['sugerencia_id' => $sintetica->id, 'nivel_urgencia' => 'ordinaria']);
        $sintetica->registrar('estado_cambiado');
        Sugerencia::find($ids[1])->delete();

        $this->artisan('buzon:sinteticas', ['--borrar' => GeneradorSugerenciasSinteticas::rangos($ids), '--force' => true])
            ->assertSuccessful();

        $this->assertSame([$real->id], Sugerencia::withTrashed()->pluck('id')->all());
        $this->assertSame(0, PropuestaIa::count());
        $this->assertSame(0, HistorialSugerencia::where('sugerencia_id', $sintetica->id)->count());
        $this->assertDatabaseCount('datos_originales', 0);
    }

    public function test_invalid_id_list_deletes_nothing(): void
    {
        $this->crearReal();

        $this->artisan('buzon:sinteticas', ['--borrar' => '5-2', '--force' => true])->assertFailed();
        $this->artisan('buzon:sinteticas', ['--borrar' => 'todas', '--force' => true])->assertFailed();

        $this->assertSame(1, Sugerencia::count());
    }

    public function test_id_ranges_round_trip(): void
    {
        $this->assertSame('3-5,9,11-12', GeneradorSugerenciasSinteticas::rangos([9, 3, 4, 5, 12, 11]));
        $this->assertSame([3, 4, 5, 9, 11, 12], GeneradorSugerenciasSinteticas::leerRangos('3-5, 9,11-12'));
        $this->assertNull(GeneradorSugerenciasSinteticas::leerRangos(''));
    }
}
