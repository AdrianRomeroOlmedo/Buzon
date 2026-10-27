<?php

namespace Tests\Feature;

use App\Models\Proyecto;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProyectoControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_index_lists_published_projects_with_featured_first(): void
    {
        Proyecto::factory()->create(['titulo' => 'Proyecto reciente', 'fecha_inicio' => '2026-09-01']);
        Proyecto::factory()->destacado()->create(['titulo' => 'Proyecto destacado', 'fecha_inicio' => '2025-01-01']);
        Proyecto::factory()->oculto()->create(['titulo' => 'Proyecto en borrador']);

        $response = $this->get(route('proyectos.index'));

        $response->assertOk();
        $response->assertSeeInOrder(['Proyecto destacado', 'Proyecto reciente']);
        $response->assertDontSee('Proyecto en borrador');
    }

    public function test_index_shows_placeholder_when_there_are_no_projects(): void
    {
        $response = $this->get(route('proyectos.index'));

        $response->assertOk();
        $response->assertSee('Muy pronto publicaremos aquí los proyectos de la cátedra.');
    }

    public function test_show_renders_markdown_description_and_project_details(): void
    {
        $proyecto = Proyecto::factory()->create([
            'titulo' => 'Ceuta Me Preocupa',
            'descripcion' => "## Cómo funciona\n\n- **Sin datos identificativos.**",
            'linea' => 'servicios-sociales',
            'socios' => 'Universidad de Granada · Ciudad Autónoma de Ceuta',
            'fecha_inicio' => '2026-10-01',
            'enlace_url' => '/buzon',
            'enlace_texto' => 'Ir al buzón',
        ]);

        $response = $this->get(route('proyectos.show', $proyecto));

        $response->assertOk();
        $response->assertSee('<h2>Cómo funciona</h2>', false);
        $response->assertSee('<strong>Sin datos identificativos.</strong>', false);
        $response->assertSee('Servicios Sociales');
        $response->assertSee('Universidad de Granada · Ciudad Autónoma de Ceuta');
        $response->assertSee('2026 –');
        $response->assertSee('href="/buzon"', false);
    }

    public function test_show_strips_raw_html_from_description(): void
    {
        $proyecto = Proyecto::factory()->create([
            'descripcion' => "Texto normal.\n\n<script>alert('x')</script>\n\n[enlace](javascript:alert(1))",
        ]);

        $response = $this->get(route('proyectos.show', $proyecto));

        $response->assertOk();
        $response->assertSee('Texto normal.');
        $response->assertDontSee("<script>alert('x')</script>", false);
        $response->assertDontSee('javascript:alert(1)', false);
    }

    public function test_show_does_not_render_unsafe_project_link(): void
    {
        $proyecto = Proyecto::factory()->create([
            'enlace_url' => 'javascript:alert(1)',
            'enlace_texto' => 'Enlace peligroso',
        ]);

        $response = $this->get(route('proyectos.show', $proyecto));

        $response->assertOk();
        $response->assertDontSee('Enlace peligroso');
    }

    public function test_show_returns_404_for_unpublished_project(): void
    {
        $proyecto = Proyecto::factory()->oculto()->create();

        $this->get(route('proyectos.show', $proyecto))->assertNotFound();
    }

    public function test_url_keeps_working_after_title_changes(): void
    {
        $proyecto = Proyecto::factory()->create(['titulo' => 'Título original']);
        $proyecto->update(['titulo' => 'Título nuevo']);

        $response = $this->get('/proyectos/titulo-original');

        $response->assertOk();
        $response->assertSee('Título nuevo');
    }
}
