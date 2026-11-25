<?php

namespace App\Support;

use App\Models\PropuestaIa;
use App\Models\Sugerencia;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Eficacia de la IA a partir de lo que anotan las personas revisoras en
 * propuestas_ia al confirmar la privacidad y la clasificación.
 *
 * Solo cuenta la propuesta más reciente de cada sugerencia (es la única que se
 * evalúa) y deja fuera las sugerencias de la papelera (spam, pruebas…).
 * Los cálculos se hacen en PHP: el volumen del buzón es pequeño y así funciona
 * igual en MySQL y en SQLite.
 */
class MetricasIa
{
    /** Valor del filtro de versión para las propuestas sin modelo_version. */
    public const SIN_VERSION = '__sin_version__';

    /**
     * @return array<string, mixed>
     */
    public static function calcular(?CarbonInterface $desde = null, ?CarbonInterface $hasta = null, ?string $modelo = null): array
    {
        $propuestas = PropuestaIa::query()
            ->whereHas('sugerencia')
            ->when($desde, fn ($q) => $q->where('created_at', '>=', $desde->copy()->startOfDay()))
            ->when($hasta, fn ($q) => $q->where('created_at', '<=', $hasta->copy()->endOfDay()))
            ->when($modelo === self::SIN_VERSION, fn ($q) => $q->whereNull('modelo_version'))
            ->when($modelo && $modelo !== self::SIN_VERSION, fn ($q) => $q->where('modelo_version', $modelo))
            ->with(['categoria:id,nombre', 'sugerencia.categoria:id,nombre'])
            ->orderByDesc('id')
            ->get()
            ->unique('sugerencia_id')
            ->values();

        $clasificadas = $propuestas->whereNotNull('revisada_at')->values();
        $anonimizadas = $propuestas->whereNotNull('privacidad_evaluada_at')->values();

        return [
            'resumen' => self::resumen($propuestas, $clasificadas, $anonimizadas, $desde, $hasta),
            'clasificacion' => self::clasificacion($clasificadas),
            'urgencia' => self::urgencia($clasificadas),
            'ciudadano' => self::comparacionCiudadano($clasificadas),
            'sesgo' => self::sesgoAutomatizacion($clasificadas),
            'confusion' => self::confusion($clasificadas),
            'por_categoria' => self::porCategoria($clasificadas),
            'anonimizacion' => self::anonimizacion($anonimizadas),
            'por_version' => self::porVersion($clasificadas, $anonimizadas),
        ];
    }

    /**
     * Versiones del modelo que aparecen en propuestas_ia, para el filtro.
     *
     * @return list<string>
     */
    public static function versiones(): array
    {
        return PropuestaIa::query()->whereNotNull('modelo_version')
            ->distinct()->orderBy('modelo_version')->pluck('modelo_version')->all();
    }

    /**
     * Proporción con intervalo de confianza del 95 % (Wilson), que se comporta
     * bien con pocas muestras: con n pequeño el intervalo sale ancho.
     *
     * @return array{aciertos: int, n: int, pct: ?float, min: ?float, max: ?float}
     */
    public static function proporcion(int $aciertos, int $n): array
    {
        if ($n === 0) {
            return ['aciertos' => 0, 'n' => 0, 'pct' => null, 'min' => null, 'max' => null];
        }

        $z = 1.96;
        $p = $aciertos / $n;
        $centro = ($p + $z * $z / (2 * $n)) / (1 + $z * $z / $n);
        $margen = $z * sqrt($p * (1 - $p) / $n + $z * $z / (4 * $n * $n)) / (1 + $z * $z / $n);

        return [
            'aciertos' => $aciertos,
            'n' => $n,
            'pct' => round(100 * $p, 1),
            'min' => round(100 * max(0, $centro - $margen), 1),
            'max' => round(100 * min(1, $centro + $margen), 1),
        ];
    }

    /**
     * @param  Collection<int, PropuestaIa>  $propuestas
     * @param  Collection<int, PropuestaIa>  $clasificadas
     * @param  Collection<int, PropuestaIa>  $anonimizadas
     * @return array<string, int>
     */
    private static function resumen(Collection $propuestas, Collection $clasificadas, Collection $anonimizadas, ?CarbonInterface $desde, ?CarbonInterface $hasta): array
    {
        $sugerencias = Sugerencia::query()
            ->when($desde, fn ($q) => $q->where('fecha_recepcion', '>=', $desde->copy()->startOfDay()))
            ->when($hasta, fn ($q) => $q->where('fecha_recepcion', '<=', $hasta->copy()->endOfDay()));

        return [
            'sugerencias' => (clone $sugerencias)->count(),
            'errores_tecnicos' => (clone $sugerencias)->where('ia_estado', 'error')->count(),
            'procesadas' => $propuestas->count(),
            'clasificacion_evaluada' => $clasificadas->count(),
            'privacidad_evaluada' => $anonimizadas->count(),
            'pendientes' => $propuestas->filter(fn (PropuestaIa $p) => $p->revisada_at === null || $p->privacidad_evaluada_at === null)->count(),
        ];
    }

    /**
     * @param  Collection<int, PropuestaIa>  $clasificadas
     * @return array<string, mixed>
     */
    private static function clasificacion(Collection $clasificadas): array
    {
        // El organismo solo se puede evaluar cuando la persona revisora lo ha confirmado.
        $conOrganismo = $clasificadas->filter(fn (PropuestaIa $p) => $p->sugerencia->organismo_id !== null && ! $p->sugerencia->competencia_por_confirmar);
        $conParcial = $clasificadas->whereNotNull('acierto_subcategorias');

        return [
            'categoria' => self::contar($clasificadas, 'categoria_correcta'),
            'subcategorias' => self::contar($clasificadas, 'subcategorias_correctas'),
            'subcategorias_parcial' => $conParcial->isEmpty() ? null : round($conParcial->avg('acierto_subcategorias'), 1),
            'organismo' => self::contar($conOrganismo, 'organismo_correcto'),
            'urgencia' => self::contar($clasificadas, 'urgencia_correcta'),
        ];
    }

    /**
     * Quedarse corta con la urgencia (decir «ordinaria» a algo urgente) es mucho
     * peor que pasarse, así que se cuentan por separado.
     *
     * @param  Collection<int, PropuestaIa>  $clasificadas
     * @return array<string, mixed>
     */
    private static function urgencia(Collection $clasificadas): array
    {
        $rango = Sugerencia::RANGO_URGENCIA;
        $validas = $clasificadas->filter(fn (PropuestaIa $p) => isset($rango[$p->nivel_urgencia], $rango[$p->sugerencia->nivel_urgencia]));
        $urgentes = $validas->filter(fn (PropuestaIa $p) => $p->sugerencia->nivel_urgencia === 'urgente');

        return [
            'n' => $validas->count(),
            'infraestimada' => $validas->filter(fn (PropuestaIa $p) => $rango[$p->nivel_urgencia] > $rango[$p->sugerencia->nivel_urgencia])->count(),
            'sobreestimada' => $validas->filter(fn (PropuestaIa $p) => $rango[$p->nivel_urgencia] < $rango[$p->sugerencia->nivel_urgencia])->count(),
            'urgentes_detectadas' => self::proporcion($urgentes->where('nivel_urgencia', 'urgente')->count(), $urgentes->count()),
        ];
    }

    /**
     * Sobre las mismas sugerencias: ¿acierta más la IA o la categoría que eligió la persona?
     *
     * @param  Collection<int, PropuestaIa>  $clasificadas
     * @return array{ia: array<string, mixed>, ciudadano: array<string, mixed>}
     */
    private static function comparacionCiudadano(Collection $clasificadas): array
    {
        $comparables = $clasificadas->filter(fn (PropuestaIa $p) => $p->sugerencia->categoria_ciudadano_id !== null);

        return [
            'ia' => self::contar($comparables, 'categoria_correcta'),
            'ciudadano' => self::proporcion(
                $comparables->filter(fn (PropuestaIa $p) => (int) $p->sugerencia->categoria_ciudadano_id === (int) $p->sugerencia->categoria_id)->count(),
                $comparables->count(),
            ),
        ];
    }

    /**
     * Acierto en categoría según se partiera o no de la propuesta de la IA.
     * Si la diferencia es grande, la revisión no está siendo independiente.
     *
     * @param  Collection<int, PropuestaIa>  $clasificadas
     * @return array{aplicada: array<string, mixed>, independiente: array<string, mixed>}
     */
    private static function sesgoAutomatizacion(Collection $clasificadas): array
    {
        [$aplicada, $independiente] = $clasificadas->partition(fn (PropuestaIa $p) => (bool) $p->propuesta_aplicada);

        return [
            'aplicada' => self::contar($aplicada, 'categoria_correcta'),
            'independiente' => self::contar($independiente, 'categoria_correcta'),
        ];
    }

    /**
     * Errores de categoría más frecuentes: lo que propuso la IA → lo correcto.
     *
     * @param  Collection<int, PropuestaIa>  $clasificadas
     * @return list<array{ia: string, correcta: string, veces: int}>
     */
    private static function confusion(Collection $clasificadas): array
    {
        return $clasificadas
            ->filter(fn (PropuestaIa $p) => $p->categoria_correcta === false)
            ->groupBy(fn (PropuestaIa $p) => ($p->categoria?->nombre ?? 'Sin categoría').'|'.($p->sugerencia->categoria?->nombre ?? '—'))
            ->map(function (Collection $grupo, string $clave) {
                [$ia, $correcta] = explode('|', $clave, 2);

                return ['ia' => $ia, 'correcta' => $correcta, 'veces' => $grupo->count()];
            })
            ->sortByDesc('veces')
            ->take(10)
            ->values()
            ->all();
    }

    /**
     * Acierto de categoría según la categoría correcta (dónde falla más).
     *
     * @param  Collection<int, PropuestaIa>  $clasificadas
     * @return list<array<string, mixed>>
     */
    private static function porCategoria(Collection $clasificadas): array
    {
        return $clasificadas
            ->groupBy(fn (PropuestaIa $p) => $p->sugerencia->categoria?->nombre ?? '—')
            ->map(fn (Collection $grupo, string $nombre) => ['categoria' => $nombre] + self::contar($grupo, 'categoria_correcta'))
            ->sortBy('pct')
            ->values()
            ->all();
    }

    /**
     * El fallo grave es que se escape un dato personal: lo marca la persona
     * revisora o lo encuentra el detector automático en el texto de la IA.
     *
     * @param  Collection<int, PropuestaIa>  $anonimizadas
     * @return array<string, mixed>
     */
    private static function anonimizacion(Collection $anonimizadas): array
    {
        $n = $anonimizadas->count();
        $sinFugas = $anonimizadas->reject(fn (PropuestaIa $p) => self::fugaDeDatos($p))->count();

        return [
            'sin_fugas' => self::proporcion($sinFugas, $n),
            'fugas_marcadas' => $anonimizadas->where('datos_personales_omitidos', true)->count(),
            'fugas_detector' => $anonimizadas->filter(fn (PropuestaIa $p) => $p->identificadores_residuales > 0)->count(),
            'sobreanonimizada' => $anonimizadas->where('sobreanonimizada', true)->count(),
            'sin_editar' => self::proporcion($anonimizadas->where('texto_editado', false)->count(), $n),
            'similitud_media' => $n ? round($anonimizadas->avg('similitud_texto'), 1) : null,
        ];
    }

    /**
     * @param  Collection<int, PropuestaIa>  $clasificadas
     * @param  Collection<int, PropuestaIa>  $anonimizadas
     * @return list<array<string, mixed>>
     */
    private static function porVersion(Collection $clasificadas, Collection $anonimizadas): array
    {
        $versiones = $clasificadas->pluck('modelo_version')
            ->merge($anonimizadas->pluck('modelo_version'))
            ->unique()
            ->sort()
            ->values();

        return $versiones->map(function (?string $version) use ($clasificadas, $anonimizadas) {
            $clas = $clasificadas->where('modelo_version', $version);
            $anon = $anonimizadas->where('modelo_version', $version);

            return [
                'version' => $version ?? 'Sin versión',
                'categoria' => self::contar($clas, 'categoria_correcta'),
                'urgencia' => self::contar($clas, 'urgencia_correcta'),
                'sin_fugas' => self::proporcion($anon->reject(fn (PropuestaIa $p) => self::fugaDeDatos($p))->count(), $anon->count()),
            ];
        })->all();
    }

    private static function fugaDeDatos(PropuestaIa $propuesta): bool
    {
        return $propuesta->datos_personales_omitidos === true || $propuesta->identificadores_residuales > 0;
    }

    /**
     * @param  Collection<int, PropuestaIa>  $propuestas
     * @return array{aciertos: int, n: int, pct: ?float, min: ?float, max: ?float}
     */
    private static function contar(Collection $propuestas, string $campo): array
    {
        $evaluadas = $propuestas->whereNotNull($campo);

        return self::proporcion($evaluadas->where($campo, true)->count(), $evaluadas->count());
    }
}
