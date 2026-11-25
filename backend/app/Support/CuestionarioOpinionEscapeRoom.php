<?php

namespace App\Support;

use App\Models\PartidaEscapeRoom;
use Illuminate\Support\Collection;

/**
 * Cuestionario de opinión del escape room «Operación Verdad» y resumen de sus respuestas.
 *
 * - Los textos son copia de `cuestionarioOpinion` en public/juegos/escape-room/content.js:
 *   si se cambian allí, hay que cambiarlos aquí también (lo comprueba un test).
 * - Cada respuesta se guarda en `partidas_escape_room.opinion` con las columnas del CSV:
 *   `item_1`…`item_12` («1»…«5», «NV» o vacío) y `pregunta_13`, `pregunta_14` (texto libre).
 */
final class CuestionarioOpinionEscapeRoom
{
    public const VERSION = 'Propuesta 1 (no validada), con el personaje renombrado a Vere';

    /** @var array<int, string> */
    public const ITEMS = [
        1 => 'Me ha resultado interesante participar en la misión de Vere.',
        2 => 'Creo que esta actividad puede ayudarme a pensar qué comprobar antes de compartir una noticia.',
        3 => 'Me ha resultado fácil entender cómo avanzar por la web.',
        4 => 'He disfrutado realizando las pruebas de la actividad.',
        5 => 'Creo que lo trabajado puede servirme para pensar cómo comprobar una imagen o un vídeo que encuentro en Internet.',
        6 => 'Las explicaciones que aparecen después de responder me han resultado claras.',
        7 => 'Me ha gustado la historia de la nave y de Vere.',
        8 => 'Creo que la actividad puede ayudarme a distinguir mejor entre información fiable e información engañosa.',
        9 => 'La historia de Vere me ha ayudado a entender lo que tenía que hacer.',
        10 => 'La misión de ayudar a Vere me ha animado a continuar con la actividad.',
        11 => 'La dificultad de las pruebas me ha parecido adecuada.',
        12 => 'Las pistas me han ayudado a continuar cuando las he necesitado.',
    ];

    /** @var array<int, string> */
    public const ABIERTAS = [
        13 => '¿Qué parte de la actividad o de la historia te ha gustado más? ¿Por qué?',
        14 => '¿Qué cambiarías de la historia, las pruebas o la web para mejorarlas?',
    ];

    /** @var array<int, string> */
    public const ESCALA = [
        1 => 'Totalmente en desacuerdo',
        2 => 'En desacuerdo',
        3 => 'Ni de acuerdo ni en desacuerdo',
        4 => 'De acuerdo',
        5 => 'Totalmente de acuerdo',
    ];

    /** Mínimo de ítems valorados para avisar de que se ha marcado lo mismo en todos. */
    private const MINIMO_PARA_AVISO_IGUALES = 8;

    /**
     * Si la partida tiene alguna respuesta del cuestionario (no solo la versión).
     */
    public static function estaRespondido(PartidaEscapeRoom $partida): bool
    {
        return collect($partida->opinion ?? [])
            ->except('cuestionario_version')
            ->contains(fn ($valor): bool => trim((string) $valor) !== '');
    }

    /**
     * Resumen de las respuestas: reparto por ítem, respuestas abiertas y tabla por partida.
     *
     * @param  Collection<int, PartidaEscapeRoom>  $partidas  Partidas con el cuestionario respondido.
     * @return array{
     *     total: int,
     *     items: list<array{numero: int, texto: string, conteos: array<int, int>, nv: int, vacias: int, valoradas: int, media: float|null}>,
     *     abiertas: list<array{numero: int, texto: string, respuestas: list<array{codigo: string, fecha: string, texto: string}>}>,
     *     partidas: list<array{codigo: string, fecha: string, finalizada: bool, valores: array<int, string>, todas_iguales: bool}>
     * }
     */
    public static function resumen(Collection $partidas): array
    {
        $items = [];
        foreach (self::ITEMS as $numero => $texto) {
            $valores = $partidas->map(fn (PartidaEscapeRoom $partida): string => self::valor($partida, "item_{$numero}"));
            $numericos = $valores->filter(fn (string $valor): bool => isset(self::ESCALA[(int) $valor]) && ctype_digit($valor))
                ->map(fn (string $valor): int => (int) $valor);

            $items[] = [
                'numero' => $numero,
                'texto' => $texto,
                'conteos' => collect(array_keys(self::ESCALA))
                    ->mapWithKeys(fn (int $punto): array => [$punto => $numericos->filter(fn (int $v): bool => $v === $punto)->count()])
                    ->all(),
                'nv' => $valores->filter(fn (string $valor): bool => $valor === 'NV')->count(),
                'vacias' => $valores->filter(fn (string $valor): bool => $valor === '')->count(),
                'valoradas' => $numericos->count(),
                'media' => $numericos->isEmpty() ? null : round($numericos->avg(), 1),
            ];
        }

        $abiertas = [];
        foreach (self::ABIERTAS as $numero => $texto) {
            $abiertas[] = [
                'numero' => $numero,
                'texto' => $texto,
                'respuestas' => $partidas
                    ->filter(fn (PartidaEscapeRoom $partida): bool => self::valor($partida, "pregunta_{$numero}") !== '')
                    ->map(fn (PartidaEscapeRoom $partida): array => [
                        'codigo' => $partida->codigo,
                        'fecha' => $partida->updated_at?->format('d/m/Y H:i') ?? '',
                        'texto' => self::valor($partida, "pregunta_{$numero}"),
                    ])
                    ->values()
                    ->all(),
            ];
        }

        $filas = $partidas->map(function (PartidaEscapeRoom $partida): array {
            $valores = collect(array_keys(self::ITEMS))
                ->mapWithKeys(fn (int $numero): array => [$numero => self::valor($partida, "item_{$numero}")]);
            $numericos = $valores->filter(fn (string $valor): bool => $valor !== '' && $valor !== 'NV');

            return [
                'codigo' => $partida->codigo,
                'fecha' => $partida->updated_at?->format('d/m/Y H:i') ?? '',
                'finalizada' => $partida->finalizada,
                'valores' => $valores->all(),
                'todas_iguales' => $numericos->count() >= self::MINIMO_PARA_AVISO_IGUALES && $numericos->unique()->count() === 1,
            ];
        });

        return [
            'total' => $partidas->count(),
            'items' => $items,
            'abiertas' => $abiertas,
            'partidas' => $filas->values()->all(),
        ];
    }

    private static function valor(PartidaEscapeRoom $partida, string $columna): string
    {
        return trim((string) ($partida->opinion[$columna] ?? ''));
    }
}
