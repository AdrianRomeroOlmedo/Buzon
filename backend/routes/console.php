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
 * Sugerencias inventadas para probar la IA real en la web publicada. Quedan pendientes
 * de IA, marcadas como sintéticas y fuera de la gráfica pública.
 *   php artisan buzon:sinteticas 100          → crea 100
 *   php artisan buzon:sinteticas --borrar     → borra todas las sintéticas (y lo que cuelga de ellas)
 */
Artisan::command('buzon:sinteticas {cantidad=100 : Cuántas crear} {--borrar : Borra todas las sintéticas} {--force : No pide confirmación}', function (string $cantidad) {
    if ($this->option('borrar')) {
        $total = Sugerencia::withTrashed()->where('sintetica', true)->count();

        if ($total === 0) {
            $this->info('No hay sugerencias sintéticas.');

            return 0;
        }
        if (! $this->option('force') && ! $this->confirm("Se borrarán definitivamente {$total} sugerencias sintéticas, con sus propuestas de la IA. ¿Continuar?")) {
            return 1;
        }

        $this->info('Borradas '.GeneradorSugerenciasSinteticas::borrar().' sugerencias sintéticas.');

        return 0;
    }

    if (! ctype_digit($cantidad) || (int) $cantidad < 1 || (int) $cantidad > 1000) {
        $this->error('La cantidad debe ser un número entre 1 y 1000.');

        return 1;
    }
    if (! $this->option('force') && ! $this->confirm("Se crearán {$cantidad} sugerencias sintéticas en la base de datos «".config('database.connections.'.config('database.default').'.database').'». ¿Continuar?')) {
        return 1;
    }

    $this->info('Creadas '.GeneradorSugerenciasSinteticas::crear((int) $cantidad).' sugerencias sintéticas, pendientes de la IA.');

    return 0;
})->purpose('Crea o borra sugerencias sintéticas para probar la IA');

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
