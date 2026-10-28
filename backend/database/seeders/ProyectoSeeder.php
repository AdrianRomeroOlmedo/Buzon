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
            [
                'slug' => 'virtual-emprecoach',
                'titulo' => 'Virtual EmpreCoach',
                'resumen' => 'Asistente virtual con inteligencia artificial que acompaña al alumnado del Máster en Educación Digital y Emprendimiento en el diseño de sus proyectos, con respuestas basadas en documentación validada.',
                'descripcion' => <<<'MD'
                    Virtual EmpreCoach es un asistente conversacional pensado para el alumnado del Máster en Educación Digital y Emprendimiento de la Universidad de Granada, que trabaja con una metodología experiencial bajo una estructura de Junior Empresa.

                    Ofrece mentoría en planes de negocio y apoyo técnico, y está diseñado también para acompañar emocionalmente: ayudar a gestionar la incertidumbre y el miedo al fracaso propios de emprender.

                    ## Cómo funciona

                    En emprendimiento, un mal consejo financiero, legal o de mercado puede invalidar un proyecto. Por eso el asistente usa una arquitectura RAG (generación aumentada por recuperación): antes de responder, busca la información en una base de documentos revisados y construye la respuesta a partir de ella, en lugar de improvisar.

                    ## De dónde aprende

                    - **Docentes de ADE:** metodologías, marcos de trabajo y casos reales aportados por profesorado de Administración y Dirección de Empresas.
                    - **Investigación científica:** literatura reciente sobre emprendimiento y educación digital.
                    - **Actualidad:** ayudas públicas, convocatorias y normativa local, recogidas y revisadas periódicamente.

                    ## Pruebas con alumnado

                    El asistente se probará en el aula en tres fases: una toma de contacto, un periodo de uso libre como apoyo 24/7 durante la redacción de los planes de negocio, y unas sesiones en las que el alumnado contrastará sus recomendaciones con la mentoría de su profesorado.

                    **Equipo:** Fernando Trujillo Sáez y Adrián Romero Olmedo.
                    MD,
                'imagen' => null,
                'linea' => 'educacion',
                'estado' => 'en-marcha',
                'fecha_inicio' => '2026-06-01',
                'fecha_fin' => null,
                'socios' => 'Máster en Educación Digital y Emprendimiento (Universidad de Granada, Campus de Ceuta)',
                'enlace_url' => null,
                'enlace_texto' => null,
                'destacado' => false,
                'publicado' => true,
            ],
        ];

        foreach ($proyectos as $datos) {
            Proyecto::updateOrCreate(['slug' => $datos['slug']], $datos);
        }
    }
}
