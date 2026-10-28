<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

/**
 * Último post de la cuenta de Instagram de la cátedra (Instagram API con inicio de sesión de Instagram).
 *
 * - Se consulta la API como mucho una vez cada MINUTOS_REFRESCO; si falla, se sigue
 *   mostrando el último post bueno que se guardó.
 * - La imagen se descarga y se sirve desde nuestro dominio: el visitante no hace
 *   ninguna petición a Instagram (ni cookies ni IP) hasta que pulsa el enlace.
 * - El token de larga duración caduca a los 60 días. `redes:instagram-token` lo renueva
 *   y lo guarda en storage; el de INSTAGRAM_ACCESS_TOKEN solo se usa la primera vez.
 */
class InstagramUltimoPost
{
    private const API = 'https://graph.instagram.com';

    private const MINUTOS_REFRESCO = 60;

    /** Tras un fallo se reintenta antes, para no quedarse una hora sin post por un error puntual. */
    private const MINUTOS_REINTENTO = 5;

    private const CLAVE_POST = 'redes.instagram.ultimo_post';

    private const CLAVE_COMPROBADO = 'redes.instagram.comprobado';

    public const RUTA_IMAGEN = 'redes/instagram-ultimo.jpg';

    private const RUTA_TOKEN = 'redes/instagram-token.txt';

    /**
     * @return array{id: string, texto: string, enlace: string, fecha: string, tipo: string}|null
     */
    public function obtener(): ?array
    {
        if (! Cache::has(self::CLAVE_COMPROBADO) && $this->token()) {
            // Se marca antes de llamar para que varias visitas a la vez no repitan la consulta.
            Cache::put(self::CLAVE_COMPROBADO, true, now()->addMinutes(self::MINUTOS_REINTENTO));

            if ($this->actualizar()) {
                Cache::put(self::CLAVE_COMPROBADO, true, now()->addMinutes(self::MINUTOS_REFRESCO));
            }
        }

        return Cache::get(self::CLAVE_POST);
    }

    public function tieneImagen(): bool
    {
        return Storage::disk('local')->exists(self::RUTA_IMAGEN);
    }

    /**
     * Renueva el token de larga duración (debe tener más de 24 h y no haber caducado).
     *
     * @return int Días que le quedan al token nuevo.
     */
    public function refrescarToken(): int
    {
        try {
            $respuesta = Http::timeout(10)->get(self::API.'/refresh_access_token', [
                'grant_type' => 'ig_refresh_token',
                'access_token' => $this->token(),
            ])->throw();
        } catch (Throwable $e) {
            throw new RuntimeException('No se ha podido renovar el token de Instagram: '.$this->sinToken($e->getMessage()));
        }

        Storage::disk('local')->put(self::RUTA_TOKEN, $respuesta->json('access_token'));

        return intdiv((int) $respuesta->json('expires_in'), 86400);
    }

    public function token(): ?string
    {
        $disco = Storage::disk('local');

        if ($disco->exists(self::RUTA_TOKEN)) {
            return trim($disco->get(self::RUTA_TOKEN));
        }

        return config('services.redes.instagram.token') ?: null;
    }

    private function actualizar(): bool
    {
        try {
            $post = Http::timeout(5)->get(self::API.'/me/media', [
                'fields' => 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp',
                'limit' => 1,
                'access_token' => $this->token(),
            ])->throw()->json('data.0');

            if (! $post) {
                return true;
            }

            // En los vídeos media_url es el propio vídeo: para la tarjeta vale la miniatura.
            $urlImagen = $post['media_type'] === 'VIDEO' ? ($post['thumbnail_url'] ?? null) : ($post['media_url'] ?? null);

            $esNuevo = (Cache::get(self::CLAVE_POST)['id'] ?? null) !== $post['id'];

            if ($urlImagen && ($esNuevo || ! $this->tieneImagen())) {
                Storage::disk('local')->put(self::RUTA_IMAGEN, Http::timeout(10)->get($urlImagen)->throw()->body());
            }

            Cache::forever(self::CLAVE_POST, [
                'id' => $post['id'],
                'texto' => trim($post['caption'] ?? ''),
                'enlace' => $post['permalink'],
                'fecha' => Carbon::parse($post['timestamp'])->timezone(config('app.timezone'))->locale('es')->translatedFormat('j \d\e F \d\e Y'),
                'tipo' => $post['media_type'],
            ]);

            return true;
        } catch (Throwable $e) {
            Log::warning('No se ha podido actualizar el último post de Instagram.', ['error' => $this->sinToken($e->getMessage())]);

            return false;
        }
    }

    /** Los errores de conexión incluyen la URL con el token: nunca debe llegar a los logs. */
    private function sinToken(string $mensaje): string
    {
        $token = $this->token();

        return $token ? str_replace([$token, urlencode($token)], '[token]', $mensaje) : $mensaje;
    }
}
