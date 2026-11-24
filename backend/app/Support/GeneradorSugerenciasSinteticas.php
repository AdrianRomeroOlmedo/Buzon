<?php

namespace App\Support;

use App\Models\Categoria;
use App\Models\HistorialSugerencia;
use App\Models\Sugerencia;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Sugerencias inventadas para probar la IA real en la web publicada.
 *
 * Se crean exactamente igual que las del formulario público (texto original,
 * elección del ciudadano, zona) y quedan pendientes de IA, para que el modelo
 * las procese y las personas revisoras las evalúen a ciegas. No llevan ninguna
 * marca: la única forma de localizarlas es la lista de IDs que devuelve crear().
 * Tampoco llevan contacto, para que nadie responda a una dirección inventada.
 *
 * Para que la prueba sirva:
 * - Unas cuantas llevan datos personales directos (inventados) y otras
 *   identificación indirecta, para medir la anonimización.
 * - En algunas el ciudadano elige mal la categoría, para comparar con la IA.
 * - Hay casos urgentes, para medir si la IA se queda corta.
 *
 * No usa Faker: en producción no se instalan las dependencias de desarrollo.
 */
class GeneradorSugerenciasSinteticas
{
    /**
     * categoria, subcategorias (1–2), texto ({zona} se sustituye), resultado esperado,
     * organismo que podría escribir el ciudadano y categoría que podría elegir por error.
     *
     * @var list<array{0: string, 1: list<string>, 2: string, 3?: ?string, 4?: ?string, 5?: ?string}>
     */
    private const ESCENARIOS = [
        // Urbanismo
        ['Urbanismo', ['Obras'], 'La acera de {zona} lleva meses levantada por unas obras que nadie ha terminado. Las personas mayores tropiezan casi a diario.', 'Que terminen la obra y arreglen la acera.', 'Fomento', 'Movilidad'],
        ['Urbanismo', ['Vivienda'], 'Llevamos años esperando la adjudicación de las viviendas de protección oficial de {zona}. Nadie nos informa de en qué punto está el proceso.', 'Que publiquen el estado de la lista de espera.', 'EMVICESA', 'Servicios Sociales'],
        ['Urbanismo', ['Accesibilidad urbana'], 'En {zona} no hay ni un solo paso de peatones rebajado. Con una silla de ruedas o un carrito es imposible cruzar.', 'Rebajar los bordillos de los pasos de peatones.', null, 'Movilidad'],
        ['Urbanismo', ['Licencias'], 'Pedí una licencia de obra menor para reformar el baño hace cinco meses y sigo sin respuesta.', 'Que agilicen las licencias de obra menor.', 'Urbanismo'],
        ['Urbanismo', ['Patrimonio'], 'Las murallas cerca de {zona} tienen pintadas y piedras sueltas. Es una pena cómo se está deteriorando el patrimonio.', 'Limpiar y restaurar ese tramo de muralla.', null, 'Medioambiente'],
        ['Urbanismo', ['Planeamiento'], 'Quieren construir un bloque de pisos en el único solar libre de {zona} y los vecinos pedimos que sea un parque. Nadie nos ha consultado.', 'Que se haga una consulta vecinal antes de aprobar el plan.'],
        ['Urbanismo', ['Obras', 'Accesibilidad urbana'], 'Han dejado una zanja abierta sin vallar en {zona}. Anoche se cayó una persona mayor y tuvo que ir al hospital.', 'Que vallen la zanja hoy mismo.', null, 'Seguridad'],

        // Movilidad
        ['Movilidad', ['Transporte público'], 'El autobús que pasa por {zona} tarda más de 40 minutos por las mañanas. Llegamos tarde al trabajo y a clase.', 'Más frecuencia en hora punta.', 'Hadú Bus'],
        ['Movilidad', ['Tráfico'], 'En la rotonda de {zona} los coches circulan muy rápido y no respetan el ceda el paso. Ha habido varios golpes este mes.', 'Poner un radar o reductores de velocidad.', 'Policía Local', 'Seguridad'],
        ['Movilidad', ['Aparcamiento'], 'Es imposible aparcar en {zona} por las tardes. Hay coches en doble fila todo el día y nadie los multa.', 'Más plazas de aparcamiento o un parking disuasorio.'],
        ['Movilidad', ['Itinerarios peatonales'], 'No hay forma de ir andando desde {zona} hasta el centro sin caminar por la carretera. Falta una acera en un tramo.', 'Construir una acera en ese tramo.', null, 'Urbanismo'],
        ['Movilidad', ['Carga y descarga'], 'Los camiones de reparto ocupan la calle principal de {zona} a la hora de salida del colegio.', 'Limitar la carga y descarga a primera hora.'],
        ['Movilidad', ['Accesibilidad'], 'La parada de autobús de {zona} no tiene marquesina ni rampa. Las personas en silla de ruedas no pueden subir.', 'Adaptar la parada.', null, 'Urbanismo'],

        // Educación no universitaria
        ['Educación no universitaria', ['Centros'], 'En el colegio de {zona} hay goteras en las aulas y cuando llueve los niños dan clase con cubos en el suelo.', 'Reparar el tejado del colegio.', 'Dirección Provincial de Educación'],
        ['Educación no universitaria', ['Escolarización'], 'No hay plazas suficientes de infantil en {zona}. A mi hijo le han asignado un colegio a media hora en autobús.', 'Más plazas de infantil en el barrio.'],
        ['Educación no universitaria', ['Profesorado'], 'En el instituto de {zona} llevan dos meses sin profesor de matemáticas en segundo de la ESO.', 'Que cubran la baja cuanto antes.', 'Dirección Provincial de Educación'],
        ['Educación no universitaria', ['Convivencia escolar'], 'Mi hija sufre acoso en el colegio desde hace meses. El centro dice que lo está mirando pero no cambia nada.', 'Que se active el protocolo de acoso escolar.', null, 'Servicios Sociales'],
        ['Educación no universitaria', ['Formación profesional'], 'Faltan ciclos de FP relacionados con la logística portuaria, que es donde hay trabajo en Ceuta.', 'Abrir un ciclo de logística.', null, 'Economía'],
        ['Educación no universitaria', ['Currículo'], 'Se debería enseñar más sobre la historia y la diversidad cultural de Ceuta en los colegios.'],

        // Universidad
        ['Universidad', ['Becas'], 'Las becas de transporte para ir a la península a estudiar llegan con meses de retraso y las familias tenemos que adelantar el dinero.', 'Pagar las ayudas al principio del curso.', 'Universidad de Granada', 'Economía'],
        ['Universidad', ['Instalaciones'], 'La biblioteca del campus cierra a las 21:00 en época de exámenes. Necesitamos horario ampliado.', 'Abrir hasta medianoche en exámenes.', 'Universidad de Granada'],
        ['Universidad', ['Docencia'], 'Faltan grados en el campus de Ceuta. Muchos jóvenes tienen que irse fuera para estudiar ingenierías.', 'Implantar algún grado técnico.'],
        ['Universidad', ['Vida universitaria'], 'No hay actividades culturales ni deportivas para los estudiantes del campus por las tardes.'],
        ['Universidad', ['Investigación'], 'Los grupos de investigación del campus no tienen apenas financiación para proyectos sobre la frontera y las migraciones.'],

        // Economía
        ['Economía', ['Empleo'], 'Los jóvenes de {zona} no encuentran trabajo. Hacen falta planes de empleo y formación en el barrio.', 'Un plan de empleo juvenil en el barrio.', 'PROCESA', 'Educación no universitaria'],
        ['Economía', ['Comercio'], 'El pequeño comercio de {zona} está cerrando. Cada mes baja otra persiana.', 'Ayudas y campañas para el comercio local.', 'Cámara de Comercio'],
        ['Economía', ['Emprendimiento'], 'Quiero montar un negocio y los trámites son un laberinto. No hay una ventanilla única que te oriente.', 'Una oficina de atención a emprendedores.', 'PROCESA'],
        ['Economía', ['Turismo'], 'Ceuta tiene playas y patrimonio pero casi no hay información turística en inglés ni en árabe.', 'Señalización y folletos en varios idiomas.'],
        ['Economía', ['Ayudas'], 'Solicité la ayuda para autónomos hace seis meses y sigo sin saber nada.', 'Que resuelvan las solicitudes pendientes.'],
        ['Economía', ['Transformación económica'], 'Ceuta depende demasiado del sector público. Hace falta atraer empresas tecnológicas.'],

        // Sanidad
        ['Sanidad', ['Atención sanitaria'], 'En urgencias del hospital la espera supera las seis horas. Mi madre de 80 años estuvo toda la noche en una silla.', 'Más personal en urgencias.', 'INGESA'],
        ['Sanidad', ['Accesibilidad sanitaria'], 'El centro de salud de {zona} no tiene ascensor y la consulta de pediatría está en la primera planta.', 'Instalar un ascensor.', 'INGESA', 'Urbanismo'],
        ['Sanidad', ['Salud pública'], 'Hay ratas en los alrededores del mercado de {zona}. Las he visto de día.', 'Una campaña de desratización.', null, 'Medioambiente'],
        ['Sanidad', ['Prevención'], 'No hay campañas de prevención de la diabetes en el barrio y aquí hay muchísimos casos.'],
        ['Sanidad', ['Atención sanitaria'], 'Para ver al especialista de traumatología me dan cita dentro de un año. Tengo dolores todos los días.', 'Reducir las listas de espera.', 'INGESA'],
        ['Sanidad', ['Salud pública'], 'Desde ayer sale agua marrón del grifo en todo {zona} y huele raro. Hay niños y mayores bebiéndola.', 'Que analicen el agua y avisen a los vecinos ya.', null, 'Medioambiente'],

        // Seguridad
        ['Seguridad', ['Seguridad ciudadana'], 'Por las noches hay peleas y botellones en la plaza de {zona}. Los vecinos tenemos miedo de salir.', 'Más presencia policial por la noche.', 'Policía Local'],
        ['Seguridad', ['Seguridad de instalaciones'], 'Hay un cable eléctrico suelto en el parque infantil de {zona} y los niños juegan al lado.', 'Que lo retiren de inmediato.', null, 'Urbanismo'],
        ['Seguridad', ['Policía local'], 'Llamé a la Policía Local por un coche bloqueando mi garaje y tardaron dos horas en venir.', 'Mejorar el tiempo de respuesta.', 'Policía Local', 'Movilidad'],
        ['Seguridad', ['Prevención'], 'Las farolas de {zona} llevan semanas apagadas y la zona está completamente a oscuras.', 'Reparar el alumbrado.', null, 'Urbanismo'],
        ['Seguridad', ['Seguridad ciudadana'], 'Están robando en los coches aparcados en {zona} casi todas las noches.', 'Vigilancia nocturna.', 'Policía Nacional'],
        ['Seguridad', ['Seguridad de instalaciones'], 'Un muro del edificio abandonado de {zona} se está agrietando y está a punto de caerse sobre la acera por donde pasan los niños del colegio.', 'Acordonar la zona y demoler el muro.', null, 'Urbanismo'],

        // Servicios Sociales
        ['Servicios Sociales', ['Dependencia'], 'Solicité la valoración de dependencia para mi padre hace un año y todavía no han venido a verle.', 'Agilizar las valoraciones.', 'IMSERSO'],
        ['Servicios Sociales', ['Vulnerabilidad'], 'Hay una familia con niños pequeños durmiendo en un coche en {zona}. Nadie hace nada.', 'Que servicios sociales les busque alojamiento.'],
        ['Servicios Sociales', ['Atención social'], 'Para conseguir cita con la trabajadora social hay que esperar dos meses.', 'Más trabajadoras sociales.'],
        ['Servicios Sociales', ['Igualdad'], 'Faltan recursos para mujeres víctimas de violencia de género en el barrio. Muchas no saben a dónde acudir.', 'Un punto de información en el barrio.', null, 'Seguridad'],
        ['Servicios Sociales', ['Inclusión'], 'Las personas con discapacidad intelectual de {zona} no tienen actividades ni centro de día cerca.'],
        ['Servicios Sociales', ['Apoyo comunitario'], 'Muchos mayores de {zona} viven solos y nadie les visita. Se podría organizar una red de voluntariado.'],

        // Medioambiente
        ['Medioambiente', ['Residuos', 'Limpieza'], 'Los contenedores de {zona} se desbordan todos los fines de semana y la basura acaba en la calle.', 'Más recogidas los fines de semana.', 'Servicios urbanos'],
        ['Medioambiente', ['Ruido'], 'El bar de {zona} pone la música alta hasta las tres de la madrugada todos los días. No se puede dormir.', 'Que controlen el horario y el ruido.', null, 'Seguridad'],
        ['Medioambiente', ['Zonas verdes'], 'El parque de {zona} está abandonado: césped seco, bancos rotos y columpios oxidados.', 'Rehabilitar el parque.', null, 'Urbanismo'],
        ['Medioambiente', ['Contaminación'], 'En la playa cerca de {zona} sale un vertido de aguas fecales al mar desde hace días.', 'Que reparen el emisario y analicen el agua.', null, 'Sanidad'],
        ['Medioambiente', ['Limpieza'], 'Las calles de {zona} no se barren desde hace semanas. Hay excrementos de perro por todas partes.', 'Limpieza regular y multas a quien no recoja.'],
        ['Medioambiente', ['Sostenibilidad'], 'Ceuta debería instalar placas solares en los edificios públicos y poner más puntos de recarga eléctrica.'],
    ];

    /** @var list<string> */
    private const ZONAS = [
        'El Príncipe', 'Hadú', 'Los Rosales', 'Benítez', 'San Amaro', 'El Sarchal', 'Juan XXIII', 'Villa Jovita',
        'Recinto Sur', 'La Almadraba', 'Pasaje Recreo', 'Bermudo Soriano', 'Loma Colmenar', 'la zona Centro',
        'Erquicia', 'Polígono Virgen de África', 'Monte Hacho', 'Calamocarro', 'Zurrón', 'Puertas del Campo',
    ];

    /** @var list<string> */
    private const NOMBRES = ['Ana', 'Mohamed', 'Laura', 'Karim', 'María José', 'Fátima', 'Antonio', 'Nadia', 'José Luis', 'Yasmina', 'Pedro', 'Rachid', 'Carmen', 'Hamido', 'Lucía'];

    /** @var list<string> */
    private const APELLIDOS = ['García', 'Ahmed', 'Martín', 'Mohamed', 'Sánchez', 'Abdelkader', 'Ruiz', 'Hamed', 'Navarro', 'Mustafa', 'López', 'Benaisa'];

    /** @var list<string> */
    private const CALLES = ['calle Real', 'calle Camoens', 'avenida de España', 'calle Jáudenes', 'paseo de las Palmeras', 'calle Teniente Arrabal', 'avenida Reyes Católicos'];

    /**
     * Frases con identificación indirecta: el detector no las encuentra, la IA debería.
     *
     * @var list<string>
     */
    private const INDIRECTOS = [
        ' Soy la única farmacéutica del barrio, todo el mundo me conoce.',
        ' Vivo en el bloque azul que hay justo encima de la panadería, en el último piso.',
        ' Soy el conserje del colegio desde hace veinte años.',
        ' Mi marido es el que tiene el taller de motos de la esquina.',
        ' Soy la presidenta de la asociación de vecinos.',
    ];

    /**
     * @return list<int> IDs de las sugerencias creadas.
     */
    public static function crear(int $cantidad): array
    {
        $categorias = Categoria::with('subcategorias')->get()->keyBy('nombre');

        if ($categorias->isEmpty()) {
            throw new RuntimeException('No hay categorías. Ejecuta antes el CategoriaSeeder.');
        }

        $escenarios = self::ESCENARIOS;

        // Fechas de los últimos 7 días, en orden: así el orden de los IDs coincide con el de llegada.
        $fechas = array_map(fn () => now()->subSeconds(random_int(60, 7 * 24 * 3600)), range(1, $cantidad));
        usort($fechas, fn (Carbon $a, Carbon $b) => $a <=> $b);

        try {
            return DB::transaction(function () use ($cantidad, $categorias, $escenarios, $fechas) {
                $ids = [];

                // Se recorren todos los escenarios en orden aleatorio antes de repetir ninguno.
                for ($i = 0; $i < $cantidad; $i++) {
                    if ($i % count($escenarios) === 0) {
                        shuffle($escenarios);
                    }

                    // Todas las marcas de tiempo (también created_at y la tabla pivote) con la fecha de llegada.
                    Carbon::setTestNow($fechas[$i]);
                    $ids[] = self::crearUna($escenarios[$i % count($escenarios)], $categorias);
                }

                return $ids;
            });
        } finally {
            Carbon::setTestNow();
        }
    }

    /**
     * Borra del todo las sugerencias indicadas, con sus datos originales, contactos,
     * propuestas de la IA y su historial (el historial no tiene clave foránea).
     *
     * @param  list<int>  $ids
     * @return int Sugerencias borradas.
     */
    public static function borrar(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            HistorialSugerencia::whereIn('sugerencia_id', $ids)->delete();

            return Sugerencia::withTrashed()->whereIn('id', $ids)->forceDelete();
        });
    }

    /**
     * Lista de IDs compacta para apuntarla: [3, 4, 5, 9] → «3-5,9».
     *
     * @param  list<int>  $ids
     */
    public static function rangos(array $ids): string
    {
        sort($ids);
        $tramos = [];

        foreach ($ids as $id) {
            $ultimo = array_key_last($tramos);
            if ($ultimo !== null && $tramos[$ultimo][1] === $id - 1) {
                $tramos[$ultimo][1] = $id;
            } else {
                $tramos[] = [$id, $id];
            }
        }

        return implode(',', array_map(fn (array $t) => $t[0] === $t[1] ? (string) $t[0] : "{$t[0]}-{$t[1]}", $tramos));
    }

    /**
     * Lo contrario de rangos(): «3-5,9» → [3, 4, 5, 9]. Null si el formato no es válido.
     *
     * @return list<int>|null
     */
    public static function leerRangos(string $texto): ?array
    {
        $ids = [];

        foreach (array_filter(array_map('trim', explode(',', $texto)), 'strlen') as $tramo) {
            if (! preg_match('/^(\d+)(?:-(\d+))?$/', $tramo, $m) || (isset($m[2]) && (int) $m[2] < (int) $m[1])) {
                return null;
            }
            array_push($ids, ...range((int) $m[1], (int) ($m[2] ?? $m[1])));
        }

        return $ids === [] ? null : array_values(array_unique($ids));
    }

    /**
     * @param  array<int, mixed>  $escenario
     * @param  Collection<string, Categoria>  $categorias
     */
    private static function crearUna(array $escenario, $categorias): int
    {
        [$nombreCategoria, $nombresSubcategorias, $texto] = $escenario;
        $resultado = $escenario[3] ?? null;
        $organismoCiudadano = $escenario[4] ?? null;
        $categoriaConfundida = $escenario[5] ?? null;

        $zona = self::elegir(self::ZONAS);
        $texto = str_replace('{zona}', $zona, $texto);
        [$texto, $resultado] = self::anadirDatosPersonales($texto, $resultado);

        // Lo que elige la persona en el formulario: a veces se equivoca de categoría.
        $categoria = $categorias[$nombreCategoria];
        if ($categoriaConfundida && isset($categorias[$categoriaConfundida]) && random_int(1, 100) <= 30) {
            $categoria = $categorias[$categoriaConfundida];
            $subcategoriaIds = [(int) $categoria->subcategorias->random()->id];
        } else {
            $subcategoriaIds = $categoria->subcategorias->whereIn('nombre', $nombresSubcategorias)
                ->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        }

        $fecha = now();

        $sugerencia = Sugerencia::create([
            'fecha_recepcion' => $fecha,
            'categoria_id' => $categoria->id,
            'categoria_ciudadano_id' => $categoria->id,
            'subcategorias_ciudadano' => $subcategoriaIds,
            'estado' => 'recibido',
            'version_aviso_privacidad' => 'v1',
            'fecha_aceptacion' => $fecha,
        ]);
        $sugerencia->subcategorias()->sync($subcategoriaIds);

        $sugerencia->datoOriginal()->create([
            'texto_original' => $texto,
            'resultado_esperado_original' => random_int(1, 100) <= 75 ? $resultado : null,
            'organismo_sugerido_ciudadano' => random_int(1, 100) <= 40 ? $organismoCiudadano : null,
            'zona_origen' => random_int(1, 100) <= 80 ? $zona : null,
        ]);

        return $sugerencia->id;
    }

    /**
     * Unas 4 de cada 10 llevan datos personales directos inventados y 1 de cada 10
     * solo identificación indirecta.
     *
     * @return array{0: string, 1: ?string}
     */
    private static function anadirDatosPersonales(string $texto, ?string $resultado): array
    {
        $tirada = random_int(1, 100);
        $nombre = self::elegir(self::NOMBRES).' '.self::elegir(self::APELLIDOS);

        if ($tirada <= 10) {
            return [$texto.self::elegir(self::INDIRECTOS), $resultado];
        }

        if ($tirada > 50) {
            return [$texto, $resultado];
        }

        $frases = [
            fn () => "Me llamo {$nombre}.",
            fn () => 'Mi teléfono es '.self::telefono().'.',
            fn () => 'Mi DNI es '.self::dni().', por si necesitan comprobarlo.',
            fn () => 'Vivo en la '.self::elegir(self::CALLES).' '.random_int(1, 90).'.',
        ];
        shuffle($frases);
        $anadidas = array_map(fn ($frase) => $frase(), array_slice($frases, 0, random_int(1, 3)));

        if ($resultado && random_int(1, 100) <= 30) {
            $resultado .= ' Escribidme a '.self::email($nombre).'.';
        }

        return [$texto.' '.implode(' ', $anadidas), $resultado];
    }

    private static function telefono(): string
    {
        return '6'.random_int(0, 9).random_int(0, 9).' '.random_int(100, 999).' '.random_int(100, 999);
    }

    /** DNI inventado con la letra de control correcta, para que el detector lo reconozca. */
    private static function dni(): string
    {
        $numero = random_int(10000000, 99999999);

        return $numero.'TRWAGMYFPDXBNJZSQVHLCKE'[$numero % 23];
    }

    private static function email(string $nombre): string
    {
        $usuario = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', str_replace(' ', '.', $nombre)) ?: 'vecino');

        return preg_replace('/[^a-z.]/', '', $usuario).random_int(1, 99).'@gmail.com';
    }

    /**
     * @template T
     *
     * @param  list<T>  $opciones
     * @return T
     */
    private static function elegir(array $opciones): mixed
    {
        return $opciones[array_rand($opciones)];
    }
}
