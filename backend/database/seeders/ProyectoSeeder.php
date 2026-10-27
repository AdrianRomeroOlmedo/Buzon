<?php

namespace Database\Seeders;

use App\Models\Proyecto;
use Illuminate\Database\Seeder;

class ProyectoSeeder extends Seeder
{
    /**
     * Proyectos de la cátedra (fase 1: se mantienen aquí hasta tener panel de administración).
     *
     * Para añadir uno: copia un bloque, cambia el slug y ejecuta
     *   php artisan db:seed --class=ProyectoSeeder
     * Es seguro repetirlo: actualiza por slug en vez de duplicar.
     *
     * - descripcion: Markdown (## títulos, **negrita**, listas con -, [enlaces](https://...)).
     * - imagen: ruta dentro de public/ (p. ej. 'proyectos/mi-proyecto.webp'), o null.
     * - linea: educacion | salud | servicios-sociales | economia.
     * - estado: en-marcha | finalizado.
     */
    public function run(): void
    {
        $proyectos = [
            [
                'slug' => 'ceuta-me-preocupa',
                'titulo' => 'Ceuta Me Preocupa',
                'resumen' => 'Buzón anónimo para que la ciudadanía cuente sus preocupaciones, incidencias no urgentes y propuestas de mejora sobre la gestión pública de Ceuta.',
                'descripcion' => <<<'MD'
                    «Ceuta Me Preocupa» recoge, con las propias palabras de la ciudadanía, preocupaciones, incidencias no urgentes y propuestas de mejora relacionadas con la gestión pública de la ciudad.

                    No es un registro de quejas formales ni un canal de emergencias: su objetivo es identificar patrones, proponer a qué organismo corresponde cada asunto y devolver a la ciudadanía una respuesta agregada y verificable.

                    ## Cómo funciona

                    - **Sin datos identificativos.** El envío es anónimo salvo que la persona deje un contacto para recibir respuesta.
                    - **Revisión humana.** Cada aportación se seudonimiza y un equipo la revisa y clasifica.
                    - **Resultados públicos.** Se publican recuentos y tendencias por categoría, nunca textos individuales.
                    MD,
                'imagen' => null,
                'linea' => 'servicios-sociales',
                'estado' => 'en-marcha',
                'fecha_inicio' => '2026-10-01',
                'fecha_fin' => null,
                'socios' => 'Universidad de Granada · Ciudad Autónoma de Ceuta',
                'enlace_url' => '/buzon',
                'enlace_texto' => 'Ir al buzón',
                'destacado' => true,
                'publicado' => true,
            ],
        ];

        foreach ($proyectos as $datos) {
            Proyecto::updateOrCreate(['slug' => $datos['slug']], $datos);
        }
    }
}
