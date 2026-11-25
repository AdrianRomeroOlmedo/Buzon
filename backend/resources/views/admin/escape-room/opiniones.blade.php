@php
    // De rojo (desacuerdo) a azul (acuerdo), con el punto neutro en gris.
    $colores = [1 => '#b91c1c', 2 => '#f87171', 3 => '#d1d5db', 4 => '#60a5fa', 5 => '#1d4ed8'];
    $textoSobre = [1 => '#fff', 2 => '#111827', 3 => '#111827', 4 => '#111827', 5 => '#fff'];
    $formatoMedia = fn (?float $media): string => $media === null ? '—' : number_format($media, 1, ',', '');
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Escape room · Cuestionario de opinión
        </h2>
    </x-slot>

    <style>
        .op-tarjeta { background: #fff; border-radius: .5rem; box-shadow: 0 1px 2px rgba(0,0,0,.05); padding: 1.5rem; }
        .op-tarjeta + .op-tarjeta { margin-top: 1.5rem; }
        .op-tarjeta h3 { font-size: 1.1rem; font-weight: 600; color: #111827; }
        .op-ayuda { margin-top: .25rem; font-size: .875rem; color: #6b7280; }
        .op-leyenda { display: flex; flex-wrap: wrap; gap: .4rem 1rem; margin-top: 1rem; font-size: .8rem; color: #4b5563; }
        .op-leyenda span { display: inline-flex; align-items: center; gap: .35rem; }
        .op-leyenda i { width: .8rem; height: .8rem; border-radius: 2px; display: inline-block; }
        .op-items { margin-top: 1.25rem; display: grid; gap: 1.1rem; }
        .op-item__cabecera { display: flex; gap: 1rem; justify-content: space-between; align-items: baseline; }
        .op-item__texto { font-size: .925rem; color: #111827; }
        .op-item__texto b { color: #6b7280; font-weight: 600; margin-right: .25rem; }
        .op-item__media { white-space: nowrap; font-variant-numeric: tabular-nums; font-size: .925rem; }
        .op-item__media strong { font-size: 1.15rem; }
        .op-item__media span { color: #9ca3af; }
        .op-barra { display: flex; height: 1.4rem; margin-top: .4rem; border-radius: .25rem; overflow: hidden; background: #f3f4f6; }
        .op-barra div { display: flex; align-items: center; justify-content: center; font-size: .75rem; font-weight: 600; font-variant-numeric: tabular-nums; min-width: 0; overflow: hidden; }
        .op-item__extra { margin-top: .25rem; font-size: .75rem; color: #9ca3af; }
        .op-abierta + .op-abierta { margin-top: 1.5rem; }
        .op-abierta h4 { font-weight: 600; color: #111827; font-size: .95rem; }
        .op-abierta ul { margin-top: .6rem; display: grid; gap: .5rem; }
        .op-abierta li { border-left: 3px solid #c7d2fe; background: #f9fafb; padding: .6rem .8rem; border-radius: 0 .25rem .25rem 0; font-size: .9rem; color: #1f2937; white-space: pre-line; }
        .op-abierta li small { display: block; margin-top: .3rem; color: #9ca3af; font-size: .75rem; }
        .op-tabla { width: 100%; border-collapse: collapse; font-size: .8rem; font-variant-numeric: tabular-nums; }
        .op-tabla th, .op-tabla td { padding: .35rem .3rem; text-align: center; border-bottom: 1px solid #f3f4f6; }
        .op-tabla th { color: #6b7280; font-weight: 600; }
        .op-tabla td:first-child, .op-tabla th:first-child { text-align: left; white-space: nowrap; }
        .op-celda { display: inline-block; min-width: 1.6rem; padding: .15rem .25rem; border-radius: .2rem; font-weight: 600; }
        .op-celda--nv { color: #6b7280; background: #f3f4f6; font-weight: 400; }
        .op-celda--vacia { color: #d1d5db; }
        .op-aviso { display: inline-block; margin-left: .35rem; padding: .05rem .4rem; border-radius: 999px; background: #fef3c7; color: #92400e; font-size: .7rem; font-weight: 600; }
        .op-vacio { padding: 1.5rem; border: 1px dashed #d1d5db; border-radius: .5rem; text-align: center; color: #6b7280; }
        .op-version { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; margin-top: .75rem; font-size: .875rem; }
        .op-version select { border: 1px solid #d1d5db; border-radius: .375rem; padding: .3rem 2rem .3rem .6rem; font-size: .875rem; }
        .op-cifras { display: flex; flex-wrap: wrap; gap: 2rem; margin-top: 1rem; }
        .op-cifras div { font-size: .8rem; color: #6b7280; }
        .op-cifras strong { display: block; font-size: 1.75rem; color: #111827; font-variant-numeric: tabular-nums; }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if ($versiones->isEmpty())
                <div class="op-tarjeta">
                    <p class="op-vacio">Todavía nadie ha respondido el cuestionario de opinión.</p>
                </div>
            @else
                <section class="op-tarjeta">
                    <h3>Resumen</h3>
                    <p class="op-ayuda">
                        Respuestas anónimas al cuestionario que aparece al final del escape room. Solo se suman respuestas de una misma versión del cuestionario.
                    </p>

                    @if ($versiones->count() > 1)
                        <form method="GET" class="op-version">
                            <label for="version">Versión del cuestionario:</label>
                            <select id="version" name="version" onchange="this.form.submit()">
                                @foreach ($versiones as $nombre => $cantidad)
                                    <option value="{{ $nombre }}" @selected($nombre === $version)>{{ $nombre !== '' ? $nombre : 'Sin versión' }} ({{ $cantidad }})</option>
                                @endforeach
                            </select>
                            <noscript><button type="submit">Ver</button></noscript>
                        </form>
                    @else
                        <p class="op-ayuda">Versión: {{ $version !== '' ? $version : 'sin versión' }}</p>
                    @endif

                    @unless ($esVersionActual)
                        <p class="op-ayuda" style="color:#92400e">
                            Ojo: los textos de los ítems que se muestran son los de la versión actual; esta versión pudo tener otros.
                        </p>
                    @endunless

                    <div class="op-cifras">
                        <div><strong>{{ $resumen['total'] }}</strong>cuestionarios respondidos</div>
                        <div><strong>{{ collect($resumen['partidas'])->where('finalizada', true)->count() }}</strong>de partidas finalizadas</div>
                        <div><strong>{{ collect($resumen['abiertas'])->sum(fn ($a) => count($a['respuestas'])) }}</strong>respuestas abiertas</div>
                    </div>
                </section>

                <section class="op-tarjeta">
                    <h3>Valoración de cada frase</h3>
                    <p class="op-ayuda">
                        La barra reparte las respuestas del 1 al 5; la media se calcula solo con ellas, sin contar «NV» (no puedo valorarlo) ni las que se dejaron en blanco.
                    </p>
                    <div class="op-leyenda" aria-hidden="true">
                        @foreach ($escala as $punto => $etiqueta)
                            <span><i style="background: {{ $colores[$punto] }}"></i>{{ $punto }} · {{ $etiqueta }}</span>
                        @endforeach
                    </div>

                    <div class="op-items">
                        @foreach ($resumen['items'] as $item)
                            <div>
                                <div class="op-item__cabecera">
                                    <p class="op-item__texto"><b>{{ $item['numero'] }}.</b>{{ $item['texto'] }}</p>
                                    <p class="op-item__media" title="Media sobre {{ $item['valoradas'] }} respuestas">
                                        <strong>{{ $formatoMedia($item['media']) }}</strong> <span>/ 5 · n={{ $item['valoradas'] }}</span>
                                    </p>
                                </div>
                                <div class="op-barra" role="img"
                                     aria-label="{{ collect($item['conteos'])->map(fn ($c, $p) => "$p: $c")->implode(', ') }}">
                                    @if ($item['valoradas'] > 0)
                                        @foreach ($item['conteos'] as $punto => $cantidad)
                                            @if ($cantidad > 0)
                                                <div style="width: {{ $cantidad / $item['valoradas'] * 100 }}%; background: {{ $colores[$punto] }}; color: {{ $textoSobre[$punto] }}"
                                                     title="{{ $punto }} · {{ $escala[$punto] }}: {{ $cantidad }} ({{ round($cantidad / $item['valoradas'] * 100) }} %)">
                                                    {{ $cantidad }}
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                                @if ($item['nv'] > 0 || $item['vacias'] > 0)
                                    <p class="op-item__extra">
                                        @if ($item['nv'] > 0) NV: {{ $item['nv'] }} @endif
                                        @if ($item['nv'] > 0 && $item['vacias'] > 0) · @endif
                                        @if ($item['vacias'] > 0) En blanco: {{ $item['vacias'] }} @endif
                                        @if ($item['numero'] === 12 && $item['nv'] > 0) (NV en este ítem = no usó pistas) @endif
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="op-tarjeta">
                    <h3>Respuestas abiertas</h3>
                    @foreach ($resumen['abiertas'] as $abierta)
                        <div class="op-abierta">
                            <h4>{{ $abierta['numero'] }}. {{ $abierta['texto'] }} <span class="op-ayuda">({{ count($abierta['respuestas']) }})</span></h4>
                            @if (count($abierta['respuestas']) === 0)
                                <p class="op-ayuda">Nadie ha respondido esta pregunta.</p>
                            @else
                                <ul>
                                    @foreach ($abierta['respuestas'] as $respuesta)
                                        <li>{{ $respuesta['texto'] }}<small>{{ $respuesta['codigo'] }} · {{ $respuesta['fecha'] }}</small></li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </section>

                <section class="op-tarjeta">
                    <h3>Respuestas por partida</h3>
                    <p class="op-ayuda">
                        Una fila por cuestionario. La etiqueta <span class="op-aviso" style="margin:0">Todo igual</span> marca a quien puso la misma nota en todas las frases: puede que no las leyera.
                    </p>
                    <div class="overflow-x-auto" style="margin-top: 1rem">
                        <table class="op-tabla">
                            <thead>
                                <tr>
                                    <th>Partida</th>
                                    @foreach ($resumen['items'] as $item)
                                        <th title="{{ $item['texto'] }}">{{ $item['numero'] }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($resumen['partidas'] as $fila)
                                    <tr>
                                        <td>
                                            {{ $fila['codigo'] }}
                                            <span class="op-ayuda" style="display:inline">· {{ $fila['fecha'] }}{{ $fila['finalizada'] ? '' : ' · sin terminar' }}</span>
                                            @if ($fila['todas_iguales'])<span class="op-aviso">Todo igual</span>@endif
                                        </td>
                                        @foreach ($fila['valores'] as $valor)
                                            <td>
                                                @if ($valor === '')
                                                    <span class="op-celda op-celda--vacia">·</span>
                                                @elseif (isset($colores[(int) $valor]) && ctype_digit($valor))
                                                    <span class="op-celda" style="background: {{ $colores[(int) $valor] }}; color: {{ $textoSobre[(int) $valor] }}">{{ $valor }}</span>
                                                @else
                                                    <span class="op-celda op-celda--nv">{{ $valor }}</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

        </div>
    </div>
</x-app-layout>
