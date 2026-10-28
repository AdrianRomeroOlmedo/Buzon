<?php

namespace Tests\Feature;

use App\Models\Recurso;
use Database\Seeders\RecursoSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RecursoControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_index_lists_published_resources_in_configured_order(): void
    {
        Recurso::factory()->create(['titulo' => 'Segunda infografía', 'orden' => 2]);
        Recurso::factory()->create(['titulo' => 'Primera infografía', 'orden' => 1]);
        Recurso::factory()->oculto()->create(['titulo' => 'Infografía en borrador']);

        $response = $this->get(route('recursos.index'));

        $response->assertOk();
        $response->assertSeeInOrder(['Primera infografía', 'Segunda infografía']);
        $response->assertDontSee('Infografía en borrador');
    }

    public function test_index_filters_by_theme(): void
    {
        Recurso::factory()->count(5)->create(['tema' => 'educacion']);
        Recurso::factory()->create(['titulo' => 'Sobre phishing', 'tema' => 'ciberseguridad']);

        $response = $this->get(route('recursos.index', ['tema' => 'ciberseguridad']));

        $response->assertOk();
        $response->assertSee('Sobre phishing');
        $this->assertSame(1, substr_count($response->getContent(), 'class="rc"'));
    }

    public function test_index_ignores_unknown_theme(): void
    {
        Recurso::factory()->count(6)->create();

        $response = $this->get(route('recursos.index', ['tema' => '<script>']));

        $response->assertOk();
        $this->assertSame(6, substr_count($response->getContent(), 'class="rc"'));
    }

    public function test_index_hides_theme_filters_with_few_resources(): void
    {
        Recurso::factory()->count(5)->create();

        $response = $this->get(route('recursos.index'));

        $response->assertDontSee('Filtrar por tema');
    }

    public function test_index_shows_theme_filters_with_enough_resources(): void
    {
        Recurso::factory()->count(6)->create(['tema' => 'educacion']);

        $response = $this->get(route('recursos.index'));

        $response->assertSee('Filtrar por tema');
        $response->assertSee(route('recursos.index', ['tema' => 'educacion']), false);
        $response->assertDontSee(route('recursos.index', ['tema' => 'ciberseguridad']), false);
    }

    public function test_show_renders_description_license_and_downloads(): void
    {
        $recurso = Recurso::factory()->create([
            'titulo' => 'Marco AILit',
            'descripcion' => '**Tres niveles de uso:** restringido, asistido e integrado.',
            'autoria' => 'Cátedra Ceuta-IATEB',
            'imagenes' => ['media/infografias/marco.jpg'],
            'pdf' => 'media/infografias/marco.pdf',
        ]);

        $response = $this->get(route('recursos.show', $recurso));

        $response->assertOk();
        $response->assertSee('<strong>Tres niveles de uso:</strong>', false);
        $response->assertSee('CC BY-NC-SA 4.0');
        $response->assertSee(asset('media/infografias/marco.pdf'), false);
        $response->assertSee('Descargar imagen (JPG)');
        $response->assertSee('aria-describedby="contenido-recurso"', false);
    }

    public function test_show_renders_every_page_of_a_multipage_resource(): void
    {
        $recurso = Recurso::factory()->paginas(3)->create(['titulo' => 'Marco AILit']);

        $response = $this->get(route('recursos.show', $recurso));

        $response->assertOk();
        $response->assertSeeInOrder(array_map(fn ($ruta) => asset($ruta), $recurso->imagenes), false);
        $response->assertSee('Página 2 de 3 de la infografía «Marco AILit»', false);
        $response->assertSee('Página 3 (JPG)');
        $response->assertDontSee('Descargar imagen (');
    }

    public function test_show_strips_raw_html_from_description(): void
    {
        $recurso = Recurso::factory()->create([
            'descripcion' => "Texto normal.\n\n<script>alert('x')</script>\n\n[enlace](javascript:alert(1))",
        ]);

        $response = $this->get(route('recursos.show', $recurso));

        $response->assertSee('Texto normal.');
        $response->assertDontSee("<script>alert('x')</script>", false);
        $response->assertDontSee('javascript:alert(1)', false);
    }

    public function test_show_returns_404_for_unpublished_resource(): void
    {
        $recurso = Recurso::factory()->oculto()->create();

        $this->get(route('recursos.show', $recurso))->assertNotFound();
    }

    public function test_public_folder_does_not_shadow_public_routes(): void
    {
        // Apache y `php artisan serve` sirven una carpeta de public/ antes que la ruta de Laravel del mismo nombre.
        foreach (['recursos', 'proyectos', 'buzon', 'privacidad', 'redes'] as $ruta) {
            $this->assertDirectoryDoesNotExist(public_path($ruta), "public/{$ruta}/ taparía la ruta /{$ruta}.");
        }
    }

    public function test_seeded_resources_point_to_existing_files(): void
    {
        $this->seed(RecursoSeeder::class);

        $recursos = Recurso::all();

        $this->assertNotEmpty($recursos);
        foreach ($recursos as $recurso) {
            foreach (array_filter([...$recurso->imagenes, $recurso->miniatura, $recurso->pdf]) as $ruta) {
                $this->assertFileExists(public_path($ruta), "Falta el fichero de «{$recurso->titulo}».");
            }
        }
    }
}
