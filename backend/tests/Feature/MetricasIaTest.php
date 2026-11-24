<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\PropuestaIa;
use App\Models\Sugerencia;
use App\Models\User;
use App\Support\MetricasIa;
use Database\Seeders\CategoriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetricasIaTest extends TestCase
{
    use RefreshDatabase;

    private Categoria $urbanismo;

    private Categoria $movilidad;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategoriaSeeder::class);
        $this->urbanismo = Categoria::where('nombre', 'Urbanismo')->firstOrFail();
        $this->movilidad = Categoria::where('nombre', 'Movilidad')->firstOrFail();
    }

    /**
     * Sugerencia ya revisada con la clasificación final indicada.
     *
     * @param  array<string, mixed>  $atributos
     */
    private function crearSugerencia(array $atributos = []): Sugerencia
    {
        return Sugerencia::create(array_merge([
            'fecha_recepcion' => now(),
            'categoria_id' => $this->urbanismo->id,
            'categoria_ciudadano_id' => $this->urbanismo->id,
            'estado' => 'en_revision',
            'nivel_urgencia' => 'ordinaria',
            'version_aviso_privacidad' => 'v1',
            'fecha_aceptacion' => now(),
            'clasificacion_revisada_at' => now(),
        ], $atributos));
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    private function crearPropuesta(Sugerencia $sugerencia, array $atributos = []): PropuestaIa
    {
        return PropuestaIa::create(array_merge([
            'sugerencia_id' => $sugerencia->id,
            'categoria_id' => $this->urbanismo->id,
            'nivel_urgencia' => 'ordinaria',
            'modelo_version' => 'v1',
            'revisada_at' => now(),
            'categoria_correcta' => true,
            'urgencia_correcta' => true,
        ], $atributos));
    }

    public function test_only_reviewers_can_see_the_panel(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.metricas-ia'))->assertForbidden();

        $this->crearPropuesta($this->crearSugerencia());

        $this->actingAs(User::factory()->revisor()->create())
            ->get(route('admin.metricas-ia'))
            ->assertOk()
            ->assertSee('Eficacia de la IA')
            ->assertSee('100,0 %');
    }

    public function test_counts_hits_misses_and_under_estimated_urgency(): void
    {
        $this->crearPropuesta($this->crearSugerencia());
        $this->crearPropuesta(
            $this->crearSugerencia(['nivel_urgencia' => 'urgente', 'categoria_ciudadano_id' => $this->movilidad->id]),
            ['categoria_id' => $this->movilidad->id, 'categoria_correcta' => false, 'urgencia_correcta' => false],
        );

        $m = MetricasIa::calcular();

        $this->assertSame(['aciertos' => 1, 'n' => 2], array_intersect_key($m['clasificacion']['categoria'], ['aciertos' => 0, 'n' => 0]));
        $this->assertSame(50.0, $m['clasificacion']['categoria']['pct']);
        $this->assertSame(1, $m['urgencia']['infraestimada']);
        $this->assertSame(0, $m['urgencia']['sobreestimada']);
        $this->assertSame(0, $m['urgencia']['urgentes_detectadas']['aciertos']);
        $this->assertSame([['ia' => 'Movilidad', 'correcta' => 'Urbanismo', 'veces' => 1]], $m['confusion']);
        $this->assertSame(50.0, $m['ciudadano']['ciudadano']['pct']);
    }

    public function test_only_the_latest_proposal_of_live_suggestions_counts(): void
    {
        $sugerencia = $this->crearSugerencia();
        $this->crearPropuesta($sugerencia, ['categoria_correcta' => false]);
        $this->crearPropuesta($sugerencia);

        $enPapelera = $this->crearSugerencia();
        $this->crearPropuesta($enPapelera, ['categoria_correcta' => false]);
        $enPapelera->delete();

        $this->assertSame(1, MetricasIa::calcular()['clasificacion']['categoria']['n']);
        $this->assertSame(100.0, MetricasIa::calcular()['clasificacion']['categoria']['pct']);
    }

    public function test_leaked_personal_data_counts_as_anonymization_failure(): void
    {
        $evaluada = ['privacidad_evaluada_at' => now(), 'texto_editado' => true, 'similitud_texto' => 80];

        $this->crearPropuesta($this->crearSugerencia(), $evaluada + ['datos_personales_omitidos' => false, 'identificadores_residuales' => 0]);
        $this->crearPropuesta($this->crearSugerencia(), $evaluada + ['datos_personales_omitidos' => true, 'identificadores_residuales' => 0]);
        $this->crearPropuesta($this->crearSugerencia(), $evaluada + ['datos_personales_omitidos' => false, 'identificadores_residuales' => 2]);

        $a = MetricasIa::calcular()['anonimizacion'];

        $this->assertSame(1, $a['sin_fugas']['aciertos']);
        $this->assertSame(3, $a['sin_fugas']['n']);
        $this->assertSame(1, $a['fugas_marcadas']);
        $this->assertSame(1, $a['fugas_detector']);
    }

    public function test_filters_by_model_version(): void
    {
        $this->crearPropuesta($this->crearSugerencia(), ['modelo_version' => 'v1']);
        $this->crearPropuesta($this->crearSugerencia(), ['modelo_version' => 'v2', 'categoria_correcta' => false]);

        $this->assertSame(100.0, MetricasIa::calcular(modelo: 'v1')['clasificacion']['categoria']['pct']);
        $this->assertSame(0.0, MetricasIa::calcular(modelo: 'v2')['clasificacion']['categoria']['pct']);
        $this->assertSame(['v1', 'v2'], MetricasIa::versiones());
    }

    public function test_wilson_interval_is_wide_with_few_samples(): void
    {
        $pocas = MetricasIa::proporcion(4, 5);
        $muchas = MetricasIa::proporcion(400, 500);

        $this->assertSame(80.0, $pocas['pct']);
        $this->assertGreaterThan($muchas['max'] - $muchas['min'], $pocas['max'] - $pocas['min']);
        $this->assertNull(MetricasIa::proporcion(0, 0)['pct']);
    }
}
