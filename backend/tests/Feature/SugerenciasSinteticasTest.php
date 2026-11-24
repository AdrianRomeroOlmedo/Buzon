<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\HistorialSugerencia;
use App\Models\PropuestaIa;
use App\Models\Sugerencia;
use App\Support\GeneradorSugerenciasSinteticas;
use App\Support\MetricasIa;
use App\View\Components\MetricasCategorias;
use Database\Seeders\CategoriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            'clasificacion_revisada_at' => now(),
        ]);
    }

    public function test_command_creates_pending_synthetic_suggestions_like_the_public_form(): void
    {
        $this->artisan('buzon:sinteticas', ['cantidad' => 60, '--force' => true])->assertSuccessful();

        $sugerencias = Sugerencia::with(['datoOriginal', 'subcategorias'])->get();

        $this->assertCount(60, $sugerencias);
        foreach ($sugerencias as $s) {
            $this->assertTrue($s->sintetica);
            $this->assertSame('pendiente', $s->refresh()->ia_estado);
            $this->assertSame('recibido', $s->estado);
            $this->assertNotEmpty($s->datoOriginal->texto_original);
            $this->assertStringNotContainsString('{zona}', $s->datoOriginal->texto_original);
            $this->assertNotEmpty($s->subcategorias_ciudadano);
            $this->assertCount(count($s->subcategorias_ciudadano), $s->subcategorias);
            $this->assertNull($s->texto_desidentificado);
        }
        $this->assertSame(0, PropuestaIa::count());
    }

    public function test_command_rejects_invalid_amounts(): void
    {
        $this->artisan('buzon:sinteticas', ['cantidad' => '0', '--force' => true])->assertFailed();
        $this->artisan('buzon:sinteticas', ['cantidad' => 'muchas', '--force' => true])->assertFailed();

        $this->assertSame(0, Sugerencia::count());
    }

    public function test_deleting_removes_only_synthetic_ones_and_everything_attached(): void
    {
        $real = $this->crearReal();
        GeneradorSugerenciasSinteticas::crear(5);
        $sintetica = Sugerencia::where('sintetica', true)->first();
        PropuestaIa::create(['sugerencia_id' => $sintetica->id, 'nivel_urgencia' => 'ordinaria']);
        $sintetica->registrar('estado_cambiado');
        Sugerencia::where('sintetica', true)->latest('id')->first()->delete();

        $this->artisan('buzon:sinteticas', ['--borrar' => true, '--force' => true])->assertSuccessful();

        $this->assertSame([$real->id], Sugerencia::withTrashed()->pluck('id')->all());
        $this->assertSame(0, PropuestaIa::count());
        $this->assertSame(0, HistorialSugerencia::where('sugerencia_id', $sintetica->id)->count());
        $this->assertDatabaseCount('datos_originales', 0);
    }

    public function test_synthetic_suggestions_stay_out_of_the_public_chart(): void
    {
        $this->crearReal();
        GeneradorSugerenciasSinteticas::crear(3);
        Sugerencia::where('sintetica', true)->update(['clasificacion_revisada_at' => now()]);

        $this->assertSame(1, (new MetricasCategorias)->total);
    }

    public function test_ai_metrics_can_be_filtered_by_origin(): void
    {
        $real = $this->crearReal();
        GeneradorSugerenciasSinteticas::crear(1);
        $sintetica = Sugerencia::where('sintetica', true)->first();

        foreach ([[$real, true], [$sintetica, false]] as [$sugerencia, $acierto]) {
            PropuestaIa::create([
                'sugerencia_id' => $sugerencia->id,
                'nivel_urgencia' => 'ordinaria',
                'revisada_at' => now(),
                'categoria_correcta' => $acierto,
            ]);
        }

        $this->assertSame(2, MetricasIa::calcular()['clasificacion']['categoria']['n']);
        $this->assertSame(100.0, MetricasIa::calcular(origen: 'reales')['clasificacion']['categoria']['pct']);
        $this->assertSame(0.0, MetricasIa::calcular(origen: 'sinteticas')['clasificacion']['categoria']['pct']);
    }
}
