<?php

namespace App\Support;

use App\Models\PartidaEscapeRoom;
use Illuminate\Support\Collection;

/**
 * Resumen de las métricas de juego de las partidas del escape room «Operación Verdad».
 *
 * - Lee las columnas que el juego guarda en `partidas_escape_room.datos` (las del CSV de
 *   «Datos de la partida», ver `datosPartida()` en public/juegos/escape-room/app.js).
 * - Un valor ausente o null significa «no disponible» (p. ej. un módulo al que no se llegó),
 *   nunca cero: no cuenta para las medias.
 */
final class EstadisticasPartidasEscapeRoom
{
    /**
     * Módulos del juego y las columnas de cada uno.
     *
     * @var array<int, array{nombre: string, aciertos: string, intentos: string|null, fallos: string, pistas: string|null, etiqueta_aciertos: string}>
     */
    public const MODULOS = [
        1 => ['nombre' => 'Detectar el bulo', 'aciertos' => 'm1_aciertos_primer_intento', 'intentos' => 'm1_rondas_respondidas', 'fallos' => 'm1_respuestas_incorrectas', 'pistas' => 'pistas_m1', 'etiqueta_aciertos' => 'rondas acertadas al primer intento'],
        2 => ['nombre' => 'Verificar la noticia', 'aciertos' => 'm2_aciertos_primer_intento', 'intentos' => 'm2_rondas_respondidas', 'fallos' => 'm2_respuestas_incorrectas', 'pistas' => 'pistas_m2', 'etiqueta_aciertos' => 'rondas acertadas al primer intento'],
        3 => ['nombre' => 'Clasificar los bulos', 'aciertos' => 'm3_correctas_primera_comprobacion', 'intentos' => null, 'fallos' => 'm3_conexiones_incorrectas', 'pistas' => 'pistas_m3', 'etiqueta_aciertos' => 'conexiones correctas en la primera comprobación'],
        4 => ['nombre' => 'Cuestionario final', 'aciertos' => 'm4_correctas_primera_correccion', 'intentos' => null, 'fallos' => 'm4_respuestas_incorrectas', 'pistas' => null, 'etiqueta_aciertos' => 'preguntas acertadas al primer intento'],
    ];

    /** Tramos del histograma de tiempos, en minutos: [desde, hasta) ; el último no tiene tope. */
    private const TRAMOS_MINUTOS = [0, 10, 15, 20, 25, 30, 40];

    /**
     * @param  Collection<int, PartidaEscapeRoom>  $partidas
     * @return array{
     *     total: int,
     *     finalizadas: int,
     *     tiempo: array{media: float|null, mediana: float|null, minimo: int|null, maximo: int|null},
     *     tiempo_activo_medio: float|null,
     *     tiempo_agotado: int,
     *     pistas_media: float|null,
     *     fallos_media: float|null,
     *     con_opinion: int,
     *     histograma: list<array{etiqueta: string, cantidad: int}>,
     *     abandonos: array<string, int>,
     *     modulos: list<array{numero: int, nombre: string, etiqueta_aciertos: string, partidas: int, aciertos_media: float|null, aciertos_porcentaje: float|null, fallos_media: float|null, pistas_media: float|null}>,
     *     filas: list<array<string, mixed>>
     * }
     */
    public static function resumen(Collection $partidas): array
    {
        $finalizadas = $partidas->filter(fn (PartidaEscapeRoom $p): bool => $p->finalizada);
        $tiempos = $finalizadas->map(fn (PartidaEscapeRoom $p): ?int => self::entero($p, 'tiempo_total_min'))->filter(fn (?int $t): bool => $t !== null)->values();

        return [
            'total' => $partidas->count(),
            'finalizadas' => $finalizadas->count(),
            'tiempo' => [
                'media' => self::media($tiempos),
                'mediana' => $tiempos->isEmpty() ? null : round((float) $tiempos->median(), 1),
                'minimo' => $tiempos->min(),
                'maximo' => $tiempos->max(),
            ],
            'tiempo_activo_medio' => self::media($finalizadas->map(fn (PartidaEscapeRoom $p): ?int => self::entero($p, 'tiempo_activo_min'))),
            'tiempo_agotado' => $partidas->filter(fn (PartidaEscapeRoom $p): bool => ($p->datos['tiempo_agotado'] ?? null) === 'si')->count(),
            'pistas_media' => self::media($finalizadas->map(fn (PartidaEscapeRoom $p): ?int => self::entero($p, 'pistas_total'))),
            'fallos_media' => self::media($finalizadas->map(fn (PartidaEscapeRoom $p): ?int => self::fallosTotales($p))),
            'con_opinion' => $partidas->filter(fn (PartidaEscapeRoom $p): bool => ($p->datos['cuestionario_opinion'] ?? null) === 'si')->count(),
            'histograma' => self::histograma($tiempos),
            'abandonos' => $partidas->reject(fn (PartidaEscapeRoom $p): bool => $p->finalizada)
                ->countBy(fn (PartidaEscapeRoom $p): string => $p->estado ?: 'Sin estado')
                ->sortKeys()
                ->all(),
            'modulos' => self::modulos($partidas),
            'filas' => $partidas->map(fn (PartidaEscapeRoom $p): array => self::fila($p))->values()->all(),
        ];
    }

    /**
     * Respuestas o conexiones incorrectas en todos los módulos, más los códigos de activación mal escritos.
     * Null si la partida no tiene ningún dato de fallos.
     */
    public static function fallosTotales(PartidaEscapeRoom $partida): ?int
    {
        $columnas = [...array_column(self::MODULOS, 'fallos'), 'codigos_incorrectos'];
        $valores = collect($columnas)->map(fn (string $c): ?int => self::entero($partida, $c))->filter(fn (?int $v): bool => $v !== null);

        return $valores->isEmpty() ? null : $valores->sum();
    }

    /**
     * @param  Collection<int, PartidaEscapeRoom>  $partidas
     * @return list<array{numero: int, nombre: string, etiqueta_aciertos: string, partidas: int, aciertos_media: float|null, aciertos_porcentaje: float|null, fallos_media: float|null, pistas_media: float|null}>
     */
    private static function modulos(Collection $partidas): array
    {
        $resultado = [];
        foreach (self::MODULOS as $numero => $modulo) {
            $conDatos = $partidas->filter(fn (PartidaEscapeRoom $p): bool => self::entero($p, $modulo['aciertos']) !== null);
            $aciertos = $conDatos->sum(fn (PartidaEscapeRoom $p): int => (int) self::entero($p, $modulo['aciertos']));
            $intentos = $modulo['intentos'] === null ? 0 : $conDatos->sum(fn (PartidaEscapeRoom $p): int => (int) self::entero($p, $modulo['intentos']));

            $resultado[] = [
                'numero' => $numero,
                'nombre' => $modulo['nombre'],
                'etiqueta_aciertos' => $modulo['etiqueta_aciertos'],
                'partidas' => $conDatos->count(),
                'aciertos_media' => self::media($conDatos->map(fn (PartidaEscapeRoom $p): ?int => self::entero($p, $modulo['aciertos']))),
                'aciertos_porcentaje' => $intentos > 0 ? round($aciertos / $intentos * 100) : null,
                'fallos_media' => self::media($conDatos->map(fn (PartidaEscapeRoom $p): ?int => self::entero($p, $modulo['fallos']))),
                'pistas_media' => $modulo['pistas'] === null ? null : self::media($conDatos->map(fn (PartidaEscapeRoom $p): ?int => self::entero($p, $modulo['pistas']))),
            ];
        }

        return $resultado;
    }

    /**
     * @param  Collection<int, int>  $tiempos
     * @return list<array{etiqueta: string, cantidad: int}>
     */
    private static function histograma(Collection $tiempos): array
    {
        $tramos = [];
        foreach (self::TRAMOS_MINUTOS as $i => $desde) {
            $hasta = self::TRAMOS_MINUTOS[$i + 1] ?? null;
            $tramos[] = [
                'etiqueta' => $hasta === null ? "{$desde}+ min" : ($desde === 0 ? "< {$hasta} min" : "{$desde}–{$hasta} min"),
                'cantidad' => $tiempos->filter(fn (int $t): bool => $t >= $desde && ($hasta === null || $t < $hasta))->count(),
            ];
        }

        return $tramos;
    }

    /**
     * @return array<string, mixed>
     */
    private static function fila(PartidaEscapeRoom $partida): array
    {
        $fila = [
            'codigo' => $partida->codigo,
            'inicio' => $partida->datos['fecha_inicio'] ?? $partida->fecha_aceptacion?->format('d/m/Y H:i'),
            'estado' => $partida->estado ?: 'Sin estado',
            'finalizada' => $partida->finalizada,
            'tiempo_min' => $partida->finalizada ? self::entero($partida, 'tiempo_total_min') : self::entero($partida, 'tiempo_hasta_ahora_min'),
            'tiempo_activo_min' => self::entero($partida, 'tiempo_activo_min'),
            'tiempo_agotado' => ($partida->datos['tiempo_agotado'] ?? null) === 'si',
            'bateria_restante' => $partida->datos['bateria_restante'] ?? null,
            'pistas' => self::entero($partida, 'pistas_total'),
            'codigos_incorrectos' => self::entero($partida, 'codigos_incorrectos'),
            'fallos_total' => self::fallosTotales($partida),
            'opinion' => ($partida->datos['cuestionario_opinion'] ?? null) === 'si',
        ];
        foreach (self::MODULOS as $numero => $modulo) {
            $fila["m{$numero}_fallos"] = self::entero($partida, $modulo['fallos']);
            $fila["m{$numero}_pistas"] = $modulo['pistas'] === null ? null : self::entero($partida, $modulo['pistas']);
        }

        return $fila;
    }

    /**
     * @param  Collection<int, int|null>  $valores
     */
    private static function media(Collection $valores): ?float
    {
        $numeros = $valores->filter(fn (?int $v): bool => $v !== null);

        return $numeros->isEmpty() ? null : round((float) $numeros->avg(), 1);
    }

    private static function entero(PartidaEscapeRoom $partida, string $columna): ?int
    {
        $valor = $partida->datos[$columna] ?? null;

        return is_numeric($valor) ? (int) $valor : null;
    }
}
