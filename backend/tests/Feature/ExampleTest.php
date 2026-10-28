<?php

namespace Tests\Feature;

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

    public function test_el_buzon_se_sirve_en_su_propia_ruta(): void
    {
        $response = $this->get('/buzon');

        $response->assertStatus(200);
        $response->assertSee(route('sugerencias.store'));
    }
}
