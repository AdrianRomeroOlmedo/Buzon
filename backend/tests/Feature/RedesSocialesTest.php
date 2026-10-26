<?php

namespace Tests\Feature;

use App\Support\InstagramUltimoPost;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RedesSocialesTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const API_MEDIA = 'https://graph.instagram.com/me/media*';

    private const CDN_IMAGEN = 'https://scontent.cdninstagram.com/*';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.redes.instagram.url' => 'https://www.instagram.com/catedra_iateb/',
            'services.redes.instagram.token' => 'token-inicial',
            'services.redes.facebook.url' => 'https://www.facebook.com/catedraiateb',
            'services.redes.linkedin.url' => 'https://www.linkedin.com/company/catedra-iateb',
            'services.redes.linkedin.post_embed' => null,
        ]);
    }

    private function respuestaMedia(string $texto = 'Jornada de IA en el Campus de Ceuta'): array
    {
        return ['data' => [[
            'id' => '1789',
            'caption' => $texto,
            'media_type' => 'IMAGE',
            'media_url' => 'https://scontent.cdninstagram.com/v/foto.jpg',
            'permalink' => 'https://www.instagram.com/p/ABC123/',
            'timestamp' => '2026-10-20T09:30:00+0000',
        ]]];
    }

    public function test_renders_latest_instagram_post_with_image_served_from_own_domain(): void
    {
        Storage::fake('local');
        Http::preventStrayRequests();
        Http::fake([
            self::API_MEDIA => Http::response($this->respuestaMedia()),
            self::CDN_IMAGEN => Http::response('bytes-de-imagen'),
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Jornada de IA en el Campus de Ceuta');
        $response->assertSee('20 de octubre de 2026');
        $response->assertSee('https://www.instagram.com/p/ABC123/');
        $response->assertSee(route('redes.instagram.imagen', ['v' => '1789']));
        $response->assertDontSee('cdninstagram.com');

        $this->get(route('redes.instagram.imagen'))->assertOk();
    }

    public function test_escapes_html_in_instagram_caption(): void
    {
        Storage::fake('local');
        Http::preventStrayRequests();
        Http::fake([
            self::API_MEDIA => Http::response($this->respuestaMedia('<script>alert("x")</script>')),
            self::CDN_IMAGEN => Http::response('bytes-de-imagen'),
        ]);

        $response = $this->get('/');

        $response->assertSee('&lt;script&gt;', false);
        $response->assertDontSee('<script>alert("x")</script>', false);
    }

    public function test_queries_instagram_api_at_most_once_per_hour(): void
    {
        Storage::fake('local');
        Http::preventStrayRequests();
        Http::fake([
            self::API_MEDIA => Http::response($this->respuestaMedia()),
            self::CDN_IMAGEN => Http::response('bytes-de-imagen'),
        ]);

        $this->get('/');
        $this->get('/');

        Http::assertSentCount(2);
    }

    public function test_falls_back_to_profile_link_when_instagram_api_fails(): void
    {
        Storage::fake('local');
        Http::preventStrayRequests();
        Http::fake([self::API_MEDIA => Http::response(['error' => ['message' => 'Invalid token']], 400)]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Ver perfil');
        $response->assertSee('@catedra_iateb');
        $this->get(route('redes.instagram.imagen'))->assertNotFound();
    }

    public function test_keeps_last_good_post_when_instagram_api_later_fails(): void
    {
        Storage::fake('local');
        Http::preventStrayRequests();
        Http::fake([
            self::API_MEDIA => Http::sequence()
                ->push($this->respuestaMedia())
                ->push(['error' => ['message' => 'Timeout']], 500),
            self::CDN_IMAGEN => Http::response('bytes-de-imagen'),
        ]);

        $this->get('/');
        $this->travel(2)->hours();
        $response = $this->get('/');

        $response->assertSee('Jornada de IA en el Campus de Ceuta');
    }

    public function test_retries_instagram_api_a_few_minutes_after_a_failure(): void
    {
        Storage::fake('local');
        Http::preventStrayRequests();
        Http::fake([
            self::API_MEDIA => Http::sequence()
                ->push(['error' => ['message' => 'Timeout']], 500)
                ->push($this->respuestaMedia()),
            self::CDN_IMAGEN => Http::response('bytes-de-imagen'),
        ]);

        $this->get('/');
        $this->travel(6)->minutes();
        $response = $this->get('/');

        $response->assertSee('Jornada de IA en el Campus de Ceuta');
    }

    public function test_does_not_write_access_token_to_log_on_connection_error(): void
    {
        Storage::fake('local');
        Log::spy();
        Http::preventStrayRequests();
        Http::fake([
            self::API_MEDIA => fn () => throw new ConnectionException(
                'cURL error 77: error setting certificate file for https://graph.instagram.com/me/media?access_token=token-inicial'
            ),
        ]);

        $this->get('/')->assertOk();

        Log::shouldHaveReceived('warning')->withArgs(
            fn (string $mensaje, array $contexto) => str_contains($contexto['error'], 'cURL error 77')
                && ! str_contains($contexto['error'], 'token-inicial')
        );
    }

    public function test_does_not_render_facebook_posts_button_when_embed_is_disabled(): void
    {
        Storage::fake('local');
        Http::preventStrayRequests();
        Http::fake([self::API_MEDIA => Http::response(['data' => []])]);

        $response = $this->get('/');

        $response->assertSee('Abrir en Facebook');
        $response->assertDontSee('https://www.facebook.com/plugins/page.php', false);
        $response->assertDontSee('Últimas publicaciones');
    }

    public function test_does_not_load_third_party_embeds_until_clicked(): void
    {
        config(['services.redes.facebook.embed' => true]);
        Storage::fake('local');
        Http::preventStrayRequests();
        Http::fake([self::API_MEDIA => Http::response(['data' => []])]);

        $response = $this->get('/');

        $response->assertSee('data-red-embed', false);
        $response->assertSee('https://www.facebook.com/plugins/page.php', false);
        $response->assertDontSee('<iframe', false);
    }

    public function test_does_not_render_social_section_when_no_network_is_configured(): void
    {
        config([
            'services.redes.instagram.url' => null,
            'services.redes.facebook.url' => null,
            'services.redes.linkedin.url' => null,
        ]);
        Http::preventStrayRequests();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Síguenos');
    }

    public function test_refresh_command_stores_new_token_used_by_later_requests(): void
    {
        Storage::fake('local');
        Http::preventStrayRequests();
        Http::fake([
            'https://graph.instagram.com/refresh_access_token*' => Http::response([
                'access_token' => 'token-renovado',
                'token_type' => 'bearer',
                'expires_in' => 5184000,
            ]),
        ]);

        $this->artisan('redes:instagram-token')
            ->expectsOutputToContain('caduca dentro de 60 días')
            ->assertSuccessful();

        Http::assertSent(fn ($request) => $request['access_token'] === 'token-inicial');
        $this->assertSame('token-renovado', app(InstagramUltimoPost::class)->token());
    }
}
