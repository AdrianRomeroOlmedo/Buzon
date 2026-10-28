<?php

namespace Tests\Feature;

use App\Models\Proyecto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee(route('sugerencias.create'));
    }

    public function test_landing_shows_three_published_projects_and_link_to_all(): void
    {
        Proyecto::factory()->count(4)->create();
        Proyecto::factory()->oculto()->create(['titulo' => 'Proyecto en borrador']);

        $response = $this->get('/');

        $response->assertOk();
        $this->assertSame(3, substr_count($response->getContent(), 'Ver proyecto →'));
        $response->assertDontSee('Proyecto en borrador');
        $response->assertSee(route('proyectos.index'));
    }

    public function test_buzon_and_privacy_pages_use_the_shared_nav(): void
    {
        foreach (['/buzon', '/privacidad'] as $ruta) {
            $response = $this->get($ruta);

            $response->assertOk();
            $response->assertSee('aria-label="Secciones"', false);
            $response->assertSee(route('proyectos.index'), false);
            $response->assertSee(route('recursos.index'), false);
        }
    }

    public function test_el_buzon_se_sirve_en_su_propia_ruta(): void
    {
        $response = $this->get('/buzon');

        $response->assertStatus(200);
        $response->assertSee(route('sugerencias.store'));
    }
}
