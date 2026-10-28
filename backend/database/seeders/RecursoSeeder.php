<?php

namespace Database\Seeders;

use App\Models\Recurso;
use Illuminate\Database\Seeder;

class RecursoSeeder extends Seeder
{
    /**
     * Recursos de la cátedra (fase 1: se mantienen aquí hasta tener panel de administración).
     *
     * Para añadir una infografía:
     *   1. Copia la imagen a public/media/infografias/{slug}.{png|jpg|webp}; si tiene varias páginas,
     *      una por página ({slug}-1.jpg, {slug}-2.jpg...). Y el PDF, si lo hay.
     *   2. Crea la miniatura en public/media/miniaturas/{slug}.webp (480×640, parte superior).
     *   3. Copia un bloque de abajo, cambia los datos y ejecuta:
     *        php artisan db:seed --class=RecursoSeeder
     *      Es seguro repetirlo: actualiza por slug en vez de duplicar.
     *
     * - descripcion: Markdown. Es la alternativa accesible a la imagen: resume su contenido.
     * - tema: educacion | emprendimiento | investigacion | ciberseguridad.
     * - licencia: cc-by-nc-sa-4.0 | cc-by-4.0 | null.
     * - orden: menor = antes en la galería.
     */
    public function run(): void
    {
        $investigo = 'Investigación promovida dentro del Programa Investigo (Ministerio de Trabajo y Economía Social, SEPE).';

        $recursos = [
            [
                'slug' => 'ia-para-emprender',
                'titulo' => 'IA para emprender',
                'tema' => 'emprendimiento',
                'autoria' => 'David Jesús Peral Sánchez y la Cátedra Ceuta-IATEB',
                'imagenes' => ['media/infografias/ia-para-emprender.webp'],
                'pdf' => null,
                'orden' => 1,
                'descripcion' => <<<'MD'
                    Herramientas clave de inteligencia artificial para acelerar un negocio en 2026, agrupadas según la fase del proyecto.

                    - **Valida tus ideas:** modelos de lenguaje (LLM) para crear borradores de contenido y simular diálogos, y Perplexity para validar ideas con información verificada.
                    - **Diseña y prototipa:** Canva IA para crear contenido visual de forma sencilla y Figma AI para diseñar interfaces y experiencia de usuario de forma colaborativa.
                    - **Construye tu producto:** Glide para crear aplicaciones a partir de hojas de cálculo y Bubble para desarrollar aplicaciones web completas con bases de datos y lógica de negocio.
                    - **Automatiza procesos:** Zapier para integraciones sencillas entre aplicaciones y Make para orquestar procesos más avanzados.
                    - **Publicita tu negocio:** Freepik para generar imágenes a partir de texto, FlexClip para editar vídeo con IA y ElevenLabs para generar voz natural en varios idiomas.
                    - **Crea tu propio chatbot:** Flowise para construir chatbots basados en LLM de forma visual y n8n para automatizar flujos de trabajo con IA.
                    - **Realiza métricas:** NotebookLM para analizar y resumir documentos extensos y Google Analytics para medir el tráfico web y el resultado de las campañas.
                    MD,
            ],
            [
                'slug' => 'programacion-nueva-alfabetizacion',
                'titulo' => 'La programación como nueva alfabetización',
                'tema' => 'educacion',
                'autoria' => 'David Jesús Peral Sánchez y la Cátedra Ceuta-IATEB',
                'imagenes' => ['media/infografias/programacion-nueva-alfabetizacion.jpg'],
                'pdf' => null,
                'orden' => 2,
                'descripcion' => <<<'MD'
                    La propuesta pedagógica *Coding as Another Language* (CAL), de Marina Umaschi Bers, entiende la programación no como una habilidad técnica, sino como una forma de pensar, crear y relacionarse con un entorno digital, igual que la lengua materna.

                    - **El código se enseña como una alfabetización:** se aprende a «leer y escribir» código con historias, canciones y proyectos propios, no memorizando comandos.
                    - **El proceso de diseño es el corazón del método:** imaginar, planificar, crear, probar y depurar, y compartir, en un ciclo que se repite en cada proyecto.
                    - **Un «patio», no un «corralito»:** actividades abiertas, fáciles de empezar y sin límite para crecer, donde el error se investiga en vez de penalizarse.
                    - **Más allá de las pantallas:** el pensamiento computacional empieza antes de usar un ordenador, con la secuenciación, la resolución de problemas y la depuración.
                    - **Las «seis C» de la educación tecnológica:** colaboración, comunicación, creación de contenidos, pensamiento crítico, creatividad y construcción de comunidad.

                    Referencias: Bers et al. (2023), *Early Childhood Research Quarterly*, 64; Bers (2020), *Coding as a playground*, Routledge.
                    MD,
            ],
            [
                'slug' => 'marco-alfabetizacion-ia-ailit',
                'titulo' => 'Marco de Alfabetización en Inteligencia Artificial (AILit)',
                'tema' => 'educacion',
                'autoria' => 'Cátedra Ceuta-IATEB',
                'imagenes' => [
                    'media/infografias/marco-alfabetizacion-ia-ailit-1.jpg',
                    'media/infografias/marco-alfabetizacion-ia-ailit-2.jpg',
                    'media/infografias/marco-alfabetizacion-ia-ailit-3.jpg',
                ],
                'pdf' => 'media/infografias/marco-alfabetizacion-ia-ailit.pdf',
                'orden' => 3,
                'descripcion' => <<<'MD'
                    El AILit Framework es una iniciativa conjunta de la Comisión Europea y la OCDE, con el apoyo de CodeAI, para preparar al alumnado de Educación Primaria y Secundaria para la era de la IA. Niñas, niños y jóvenes ya usan la inteligencia artificial para aprender, crear y relacionarse, y necesitan orientación para comprender su papel y usarla de forma ética y creativa, manteniendo el control sobre cómo moldea sus vidas.

                    **Barreras que aborda:** la falta de una comprensión compartida de qué es la alfabetización en IA, la adopción desigual de iniciativas entre centros, la ansiedad y las concepciones erróneas sobre la IA, y el escaso conocimiento de cómo usarla para mejorar el aprendizaje. Se dirige al profesorado, a los líderes de centros, a los responsables de políticas educativas, a quienes diseñan formación y a las familias.

                    ## Tres pilares

                    Cada competencia combina conocimientos, destrezas y actitudes, e incluye expectativas y escenarios de aprendizaje para aplicarla en el aula.

                    - **Conocimientos, en cuatro áreas:** la naturaleza de la IA (no es humana: usa algoritmos y estadística, y consume energía, minerales y agua); la IA refleja decisiones humanas y sus sesgos; sus capacidades y limitaciones (puede «alucinar» y carece de razonamiento ético); y su papel en la sociedad, que exige equidad, transparencia y respeto a la privacidad.
                    - **Siete destrezas humanas:** pensamiento crítico, creatividad, pensamiento computacional, colaboración, resolución de problemas, comunicación y conciencia personal y social.
                    - **Seis actitudes ante la IA:** reflexiva, responsable, curiosa, innovadora, adaptable y empática.

                    ## Cuatro dominios y 19 competencias

                    1. **Interactuar con la IA** (7 competencias): reconocer su papel en distintos contextos, explicar cómo realiza tareas y sus errores frecuentes, decidir si aceptar, revisar o rechazar sus resultados, entender cómo la IA predictiva amplía o limita lo que vemos, comparar su consumo de recursos, explicar cómo amplifica sesgos y analizar si su uso se ajusta a principios éticos.
                    2. **Crear con la IA** (4): explorar nuevas perspectivas, prototipar y combinar ideas, dirigir la IA generativa para obtener feedback y reflexionar, y analizar cómo afecta a la autenticidad y la propiedad intelectual.
                    3. **Gestionar la IA** (4): decidir si conviene usarla según la tarea, elegir el enfoque adecuado, descomponer problemas para decidir cuándo usarla y supervisar su uso durante todo el proceso.
                    4. **Configurar la IA** (4): investigar cómo funciona un sistema y sus límites, evaluarlo con criterios y casos de prueba, diseñar teniendo en cuenta los datos, y mejorarlo para promover el bienestar y el beneficio social.

                    Cada competencia progresa en tres niveles: básico, intermedio y avanzado. Más de 2.000 personas participaron en la consulta global que dio forma al marco, que contribuye al dominio innovador de PISA 2029 y está disponible completo en 24 idiomas.

                    Fuente: [ailiteracyframework.org](https://ailiteracyframework.org) · © 2026 OCDE / Unión Europea.
                    MD,
            ],
            [
                'slug' => 'marco-ia-educacion',
                'titulo' => 'Marco de referencia sobre el uso de la IA en la educación',
                'tema' => 'educacion',
                'autoria' => 'Cátedra Ceuta-IATEB',
                'imagenes' => ['media/infografias/marco-ia-educacion.jpg'],
                'pdf' => null,
                'orden' => 4,
                'descripcion' => <<<'MD'
                    Resumen del marco de referencia del Consejo Escolar del Estado sobre el uso de la inteligencia artificial en Educación Primaria y Secundaria, para preparar al alumnado para la era de la IA.

                    **Tres ecosistemas de la vida escolar:** el formal (proyecto educativo, evaluación y políticas digitales), el informal (la cultura cotidiana y los usos espontáneos de la IA) y el comunitario (las familias y el entorno, con sus oportunidades de inclusión y riesgos de desigualdad).

                    **Ocho principios orientadores:** la IA está al servicio de la educación; la última palabra es siempre humana; la relación educativa es irreemplazable; el acceso equitativo no es negociable; la comunidad educativa tiene derecho a saber; participar es un derecho; la IA puede personalizar el aprendizaje; y tiene un coste ambiental que no se puede ignorar.

                    **Tres niveles de uso**, según el propósito pedagógico: *restringido* (la IA no interviene), *asistido* (la IA apoya bajo supervisión docente y el alumnado declara su uso) e *integrado* (forma parte del diseño pedagógico del centro, con políticas explícitas y protección de datos).

                    **Por dónde empezar:** preguntar antes de regular, revisar lo que ya existe en el centro y usar la lista de verificación en uno o dos ámbitos concretos.

                    Fuente: Consejo Escolar del Estado, *Marco de referencia sobre el uso de la inteligencia artificial en la educación*, XXVI Encuentro de Consejos Escolares Autonómicos y del Estado (Gijón, mayo de 2026).
                    MD,
            ],
            [
                'slug' => 'ia-revision-literatura-analisis-cualitativo',
                'titulo' => 'IA para la revisión de la literatura y el análisis cualitativo',
                'tema' => 'investigacion',
                'autoria' => 'Adrián Romero Olmedo y la Cátedra Ceuta-IATEB',
                'imagenes' => ['media/infografias/ia-revision-literatura-analisis-cualitativo.png'],
                'pdf' => null,
                'orden' => 5,
                'descripcion' => <<<'MD'
                    Herramientas de inteligencia artificial para cada fase de una investigación.

                    - **Búsqueda de documentos:** Elicit y SciSpace generan colecciones de documentos y texto a partir de un prompt.
                    - **Relación entre documentos:** ResearchRabbit y Connected Papers muestran gráficos de conexiones entre artículos y encuentran artículos relacionados.
                    - **Generación de resúmenes:** Paper Digest y SciSummary resumen documentos o consultas booleanas.
                    - **Transcripción de audio:** Otter.ai y Fathom transcriben entrevistas y reuniones automáticamente, en varios idiomas.
                    - **Generación de gráficos:** Miro y Lucidchart crean formularios y diagramas editables a partir de un prompt.
                    - **Análisis de datos:** MAXQDA y ATLAS.ti organizan e interpretan información de texto, imágenes, audio, vídeo y encuestas.
                    MD,
            ],
            [
                'slug' => 'ataques-ingenieria-social',
                'titulo' => 'Ataques de ingeniería social',
                'tema' => 'ciberseguridad',
                'autoria' => 'Adrián Romero Olmedo y la Cátedra Ceuta-IATEB',
                'imagenes' => ['media/infografias/ataques-ingenieria-social.webp'],
                'pdf' => null,
                'orden' => 6,
                'descripcion' => <<<'MD'
                    Los ataques de ingeniería social manipulan a las personas para que hagan algo que no deberían en entornos tecnológicos, como compartir información con quien no deben, descargar software malicioso o entrar en webs fraudulentas.

                    **Según quién realiza el ataque:**
                    - **Por humanos:** una persona interactúa con la víctima, por ejemplo con llamadas que buscan datos confidenciales.
                    - **Por máquinas:** programas o IA que atacan a muchas personas a la vez, por ejemplo con correos maliciosos masivos.

                    **Según cómo se realiza:**
                    - **Técnicos:** a través de webs o redes sociales, para conseguir contraseñas, tarjetas u otra información sensible.
                    - **Sociales:** aprovechan una relación con la víctima, como en las estafas amorosas.
                    - **Físicos:** buscan información de forma presencial, por ejemplo en la basura o colándose en una sala de servidores.
                    MD,
            ],
            [
                'slug' => 'ia-ataques-ingenieria-social',
                'titulo' => 'IA en ataques de ingeniería social',
                'tema' => 'ciberseguridad',
                'autoria' => 'Adrián Romero Olmedo y la Cátedra Ceuta-IATEB',
                'imagenes' => ['media/infografias/ia-ataques-ingenieria-social.png'],
                'pdf' => null,
                'orden' => 7,
                'descripcion' => <<<'MD'
                    Ventajas y riesgos de la inteligencia artificial frente a los ataques de ingeniería social.

                    - **Detección de ataques:** la IA reconoce patrones y detecta intentos de ataque automáticamente, pero también permite crear ataques masivos adaptados a cada víctima.
                    - **Automatización:** permite protocolos de seguridad que responden solos a los ataques, y también automatizar ataques más numerosos y complejos.
                    - **Filtros web:** detecta webs maliciosas y las bloquea, aunque también sirve para crear webs maliciosas que esquivan los filtros convencionales.
                    - **Correo electrónico:** filtra correos maliciosos antes de que lleguen a la bandeja de entrada, pero también genera correos personalizados más difíciles de identificar.
                    - **Deepfakes:** hay algoritmos para detectar vídeos, imágenes o audios falsos, pero la IA también facilita crearlos para engañar.
                    - **Redes sociales:** detecta y elimina publicaciones maliciosas generadas por IA, mientras los atacantes crean perfiles falsos con imágenes generadas para engañar a sus víctimas.

                    Referencia: Schmitt y Flechais (2024), *Artificial Intelligence Review*, 57, 324.
                    MD,
            ],
            [
                'slug' => 'phishing',
                'titulo' => 'Phishing',
                'tema' => 'ciberseguridad',
                'autoria' => 'Adrián Romero Olmedo y la Cátedra Ceuta-IATEB',
                'imagenes' => ['media/infografias/phishing.png'],
                'pdf' => null,
                'orden' => 8,
                'descripcion' => <<<'MD'
                    El phishing es una estafa que busca engañar a la víctima para que comparta información sensible, suplantando a una marca, institución o persona de confianza, o provocando miedo, urgencia o codicia.

                    **Tipos de ataques:**
                    1. **Llamadas falsas:** se hacen pasar por bancos o empresas y meten prisa («han hackeado tu cuenta») para conseguir datos o transferencias.
                    2. **Correos falsos:** imitan a empresas legítimas, con enlaces o archivos maliciosos y mensajes alarmantes («tu cuenta será bloqueada»).
                    3. **Webs maliciosas:** copian páginas reales para robar los datos que introduce el usuario.
                    4. **Engaños amorosos:** perfiles falsos en aplicaciones que se ganan la confianza de la víctima y después piden dinero.
                    5. **Mensajes de «hijos»:** se hacen pasar por un hijo o hija, normalmente por WhatsApp, diciendo que han cambiado de número o tienen un problema urgente.
                    6. **Redes sociales:** perfiles falsos o cuentas robadas que envían enlaces o promociones engañosas.
                    MD,
            ],
            [
                'slug' => 'ataques-fisicos',
                'titulo' => 'Ataques físicos',
                'tema' => 'ciberseguridad',
                'autoria' => 'Adrián Romero Olmedo y la Cátedra Ceuta-IATEB',
                'imagenes' => ['media/infografias/ataques-fisicos.png'],
                'pdf' => null,
                'orden' => 9,
                'descripcion' => <<<'MD'
                    En los ataques físicos, el atacante busca información sobre la víctima de forma presencial: inspeccionando documentos o accediendo a equipos o instalaciones con información sensible.

                    - **Tailgating:** seguir a una persona con acceso para colarse detrás de ella o aprovechar, por ejemplo, un ordenador que se ha dejado con la sesión iniciada.
                    - **Rebuscar en la basura:** buscar documentos con información importante en los residuos de las empresas.
                    - **Ataques de desviación:** desviar un transporte de documentos o equipos para que entregue la carga en el lugar equivocado.
                    - **Robar documentos:** aprovechar un descuido para llevarse documentos o archivos de una persona o institución.

                    Referencia: Salahdine y Kaabouch (2019), *Future Internet*, 11(4), 89.
                    MD,
            ],
        ];

        foreach ($recursos as $datos) {
            if (str_starts_with($datos['autoria'], 'Adrián Romero')) {
                $datos['descripcion'] .= "\n\n".$investigo;
            }

            Recurso::updateOrCreate(['slug' => $datos['slug']], $datos + [
                'tipo' => 'infografia',
                'licencia' => 'cc-by-nc-sa-4.0',
                'miniatura' => 'media/miniaturas/'.$datos['slug'].'.webp',
                'publicado' => true,
            ]);
        }
    }
}
