<?php

use App\Models\Sugerencia;
use App\Models\User;
use App\Support\GeneradorSugerenciasSinteticas;
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
 * Sugerencias inventadas para probar la IA real en la web publicada. Se crean igual que
 * las del formulario, sin ninguna marca: APUNTA LOS IDS que muestra, son la única forma
 * de localizarlas después.
 *   php artisan buzon:sinteticas 100                 → crea 100 y muestra sus IDs
 *   php artisan buzon:sinteticas --borrar=120-219    → borra esas sugerencias (y lo que cuelga de ellas)
 */
Artisan::command('buzon:sinteticas {cantidad=100 : Cuántas crear} {--borrar= : IDs a borrar, p. ej. 120-219,225} {--force : No pide confirmación}', function (string $cantidad) {
    if ($this->option('borrar') !== null) {
        $ids = GeneradorSugerenciasSinteticas::leerRangos((string) $this->option('borrar'));

        if ($ids === null) {
            $this->error('Formato de IDs no válido. Ejemplo: --borrar=120-219,225');

            return 1;
        }

        $existentes = Sugerencia::withTrashed()->whereIn('id', $ids)->pluck('id')->all();

        if ($existentes === []) {
            $this->info('No existe ninguna sugerencia con esos IDs.');

            return 0;
        }
        if (! $this->option('force') && ! $this->confirm('Se borrarán definitivamente '.count($existentes).' sugerencias ('.GeneradorSugerenciasSinteticas::rangos($existentes).'), con sus propuestas de la IA. ¿Continuar?')) {
            return 1;
        }

        $this->info('Borradas '.GeneradorSugerenciasSinteticas::borrar($existentes).' sugerencias.');

        return 0;
    }

    if (! ctype_digit($cantidad) || (int) $cantidad < 1 || (int) $cantidad > 1000) {
        $this->error('La cantidad debe ser un número entre 1 y 1000.');

        return 1;
    }
    if (! $this->option('force') && ! $this->confirm("Se crearán {$cantidad} sugerencias en la base de datos «".config('database.connections.'.config('database.default').'.database').'». ¿Continuar?')) {
        return 1;
    }

    $ids = GeneradorSugerenciasSinteticas::crear((int) $cantidad);

    $this->info('Creadas '.count($ids).' sugerencias, pendientes de la IA.');
    $this->warn('Apunta estos IDs: no se guardan en ningún sitio y son la única forma de borrarlas.');
    $this->line(GeneradorSugerenciasSinteticas::rangos($ids));

    return 0;
})->purpose('Crea sugerencias de prueba para la IA o borra las indicadas');

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
