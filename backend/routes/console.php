<?php

use App\Models\User;
use App\Support\InstagramUltimoPost;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Alta y baja de cuentas revisoras del panel (el registro público está desactivado).
 *   php artisan buzon:revisor ana@ugr.es            → da permiso (crea la cuenta si no existe)
 *   php artisan buzon:revisor ana@ugr.es --quitar   → retira el permiso
 */
Artisan::command('buzon:revisor {email} {--quitar : Retira el permiso de revisión}', function (string $email) {
    $user = User::where('email', $email)->first();

    if ($this->option('quitar')) {
        if (! $user) {
            $this->error("No existe ninguna cuenta con el correo {$email}.");

            return 1;
        }

        $user->forceFill(['es_revisor' => false])->save();
        $this->info("{$email} ya no puede acceder al panel de revisión.");

        return 0;
    }

    if (! $user) {
        if (! $this->confirm("No existe ninguna cuenta con {$email}. ¿Crearla?", true)) {
            return 1;
        }

        $nombre = $this->ask('Nombre', Str::before($email, '@'));
        $password = $this->secret('Contraseña (mínimo 8 caracteres)');

        if (strlen((string) $password) < 8) {
            $this->error('La contraseña debe tener al menos 8 caracteres.');

            return 1;
        }

        $user = User::create(['name' => $nombre, 'email' => $email, 'password' => $password]);
    }

    $user->forceFill(['es_revisor' => true])->save();
    $this->info("{$email} puede acceder al panel de revisión.");

    return 0;
})->purpose('Concede o retira el permiso de revisión de sugerencias');

/*
 * Renueva el token de Instagram de la landing (caduca a los 60 días si no se renueva).
 * Se programa semanalmente; requiere el cron de `php artisan schedule:run`.
 */
Artisan::command('redes:instagram-token', function (InstagramUltimoPost $instagram) {
    if (! $instagram->token()) {
        $this->warn('No hay token de Instagram configurado (INSTAGRAM_ACCESS_TOKEN).');

        return 0;
    }

    $dias = $instagram->refrescarToken();
    $this->info("Token de Instagram renovado: caduca dentro de {$dias} días.");

    return 0;
})->purpose('Renueva el token de larga duración de Instagram');

Schedule::command('redes:instagram-token')->weekly();
