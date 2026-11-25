@php
    $num = fn ($valor): string => $valor === null ? '—' : number_format((float) $valor, fmod((float) $valor, 1) === 0.0 ? 0 : 1, ',', '');
    $maxHistograma = max(1, collect($resumen['histograma'])->max('cantidad'));
    $porcentaje = fn (int $parte): string => $resumen['total'] > 0 ? round($parte / $resumen['total'] * 100).' %' : '—';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Escape room · Partidas
        </h2>
    </x-slot>

    <style>
        .pa-tarjeta { background: #fff; border-radius: .5rem; box-shadow: 0 1px 2px rgba(0,0,0,.05); padding: 1.5rem; }
        .pa-tarjeta + .pa-tarjeta, .pa-rejilla + .pa-tarjeta { margin-top: 1.5rem; }
        .pa-tarjeta h3 { font-size: 1.1rem; font-weight: 600; color: #111827; }
        .pa-ayuda { margin-top: .25rem; font-size: .875rem; color: #6b7280; }
        .pa-cifras { display: grid; grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr)); gap: 1rem; }
        .pa-cifra { background: #fff; border-radius: .5rem; box-shadow: 0 1px 2px rgba(0,0,0,.05); padding: 1rem 1.25rem; }
        .pa-cifra strong { display: block; font-size: 1.75rem; line-height: 1.2; color: #111827; font-variant-numeric: tabular-nums; }
        .pa-cifra span { font-size: .8rem; color: #6b7280; }
        .pa-cifra small { display: block; margin-top: .15rem; font-size: .75rem; color: #9ca3af; }
        .pa-rejilla { display: grid; grid-template-columns: repeat(auto-fit, minmax(20rem, 1fr)); gap: 1.5rem; margin-top: 1.5rem; }
        .pa-rejilla .pa-tarjeta { margin-top: 0; }
        .pa-histo { display: grid; gap: .45rem; margin-top: 1rem; }
        .pa-histo__fila { display: grid; grid-template-columns: 5.5rem 1fr 2rem; align-items: center; gap: .6rem; font-size: .85rem; }
        .pa-histo__etiqueta { color: #4b5563; font-variant-numeric: tabular-nums; }
        .pa-histo__pista { background: #f3f4f6; border-radius: .25rem; height: 1.1rem; overflow: hidden; }
        .pa-histo__barra { background: #4f46e5; height: 100%; }
        .pa-histo__cifra { text-align: right; font-variant-numeric: tabular-nums; color: #111827; font-weight: 600; }
        .pa-lista { margin-top: 1rem; display: grid; gap: .4rem; font-size: .9rem; }
        .pa-lista li { display: flex; justify-content: space-between; gap: 1rem; border-bottom: 1px solid #f3f4f6; padding-bottom: .4rem; }
        .pa-lista b { font-variant-numeric: tabular-nums; }
        .pa-tabla { width: 100%; border-collapse: collapse; font-size: .85rem; font-variant-numeric: tabular-nums; }
        .pa-tabla th, .pa-tabla td { padding: .5rem .5rem; border-bottom: 1px solid #f3f4f6; text-align: right; white-space: nowrap; }
        .pa-tabla th { color: #6b7280; font-weight: 600; font-size: .8rem; vertical-align: bottom; }
        .pa-tabla th:first-child, .pa-tabla td:first-child, .pa-tabla .pa-izq { text-align: left; }
        .pa-tabla th[data-orden] { cursor: pointer; user-select: none; }
        .pa-tabla th[data-orden]:hover { color: #111827; }
        .pa-tabla th[aria-sort="ascending"]::after { content: " ▲"; }
        .pa-tabla th[aria-sort="descending"]::after { content: " ▼"; }
        .pa-tabla tbody tr:hover { background: #f9fafb; }
        .pa-sub { display: block; font-size: .7rem; color: #9ca3af; font-weight: 400; }
        .pa-gris { color: #d1d5db; }
        .pa-etiqueta { display: inline-block; padding: .05rem .45rem; border-radius: 999px; font-size: .7rem; font-weight: 600; }
        .pa-etiqueta--ok { background: #dcfce7; color: #166534; }
        .pa-etiqueta--curso { background: #f3f4f6; color: #4b5563; }
        .pa-etiqueta--aviso { background: #fef3c7; color: #92400e; }
        .pa-filtro { display: inline-flex; align-items: center; gap: .4rem; font-size: .875rem; color: #374151; }
        .pa-vacio { padding: 1.5rem; border: 1px dashed #d1d5db; border-radius: .5rem; text-align: center; color: #6b7280; }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('admin.escape-room.pestanas')

            @if ($resumen['total'] === 0 && ! $soloFinalizadas)
                <div class="pa-tarjeta">
                    <p class="pa-vacio">Todavía no se ha registrado ninguna partida.</p>
                </div>
            @else
                <div class="pa-cifras">
                    <div class="pa-cifra">
                        <strong>{{ $resumen['total'] }}</strong><span>partidas registradas</span>
                    </div>
                    <div class="pa-cifra">
                        <strong>{{ $resumen['finalizadas'] }}</strong><span>finalizadas</span>
                        <small>{{ $porcentaje($resumen['finalizadas']) }} del total</small>
                    </div>
                    <div class="pa-cifra">
                        <strong>{{ $num($resumen['tiempo']['mediana']) }} min</strong><span>tiempo mediano</span>
                        <small>media {{ $num($resumen['tiempo']['media']) }} · de {{ $num($resumen['tiempo']['minimo']) }} a {{ $num($resumen['tiempo']['maximo']) }} min</small>
                    </div>
                    <div class="pa-cifra">
                        <strong>{{ $num($resumen['fallos_media']) }}</strong><span>fallos por partida</span>
                        <small>media de las finalizadas</small>
                    </div>
                    <div class="pa-cifra">
                        <strong>{{ $num($resumen['pistas_media']) }}</strong><span>pistas por partida</span>
                        <small>media de las finalizadas</small>
                    </div>
                    <div class="pa-cifra">
                        <strong>{{ $resumen['tiempo_agotado'] }}</strong><span>agotaron los 20 min</span>
                        <small>{{ $porcentaje($resumen['tiempo_agotado']) }} del total</small>
                    </div>
                </div>

                <div class="pa-rejilla">
                    <section class="pa-tarjeta">
                        <h3>¿Cuánto tardan?</h3>
                        <p class="pa-ayuda">Partidas finalizadas según su tiempo total (incluye pausas). Tiempo activo medio: {{ $num($resumen['tiempo_activo_medio']) }} min.</p>
                        <div class="pa-histo">
                            @foreach ($resumen['histograma'] as $tramo)
                                <div class="pa-histo__fila">
                                    <span class="pa-histo__etiqueta">{{ $tramo['etiqueta'] }}</span>
                                    <span class="pa-histo__pista"><span class="pa-histo__barra" style="display:block; width: {{ $tramo['cantidad'] / $maxHistograma * 100 }}%"></span></span>
                                    <span class="pa-histo__cifra">{{ $tramo['cantidad'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="pa-tarjeta">
                        <h3>¿Dónde se quedan?</h3>
                        <p class="pa-ayuda">Partidas sin terminar según el último punto al que llegaron.</p>
                        @if (count($resumen['abandonos']) === 0)
                            <p class="pa-ayuda" style="margin-top:1rem">Todas las partidas están finalizadas.</p>
                        @else
                            <ul class="pa-lista">
                                @foreach ($resumen['abandonos'] as $estado => $cantidad)
                                    <li><span>{{ $estado }}</span><b>{{ $cantidad }}</b></li>
                                @endforeach
                            </ul>
                        @endif
                    </section>
                </div>

                <section class="pa-tarjeta">
                    <h3>Por módulo</h3>
                    <p class="pa-ayuda">Medias de las partidas que llegaron a cada módulo.</p>
                    <div class="overflow-x-auto" style="margin-top: 1rem">
                        <table class="pa-tabla">
                            <thead>
                                <tr>
                                    <th>Módulo</th>
                                    <th>Partidas</th>
                                    <th>Aciertos al primer intento</th>
                                    <th>Fallos</th>
                                    <th>Pistas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($resumen['modulos'] as $modulo)
                                    <tr>
                                        <td>{{ $modulo['numero'] }}. {{ $modulo['nombre'] }}</td>
                                        <td>{{ $modulo['partidas'] }}</td>
                                        <td>
                                            {{ $modulo['aciertos_porcentaje'] !== null ? $num($modulo['aciertos_porcentaje']).' %' : $num($modulo['aciertos_media']) }}
                                            <span class="pa-sub">{{ $modulo['aciertos_porcentaje'] !== null ? 'de las '.$modulo['etiqueta_aciertos'] : 'media de '.$modulo['etiqueta_aciertos'] }}</span>
                                        </td>
                                        <td>{{ $num($modulo['fallos_media']) }}</td>
                                        <td>{!! $modulo['pistas_media'] === null && $modulo['numero'] === 4 ? '<span class="pa-gris">sin pistas</span>' : e($num($modulo['pistas_media'])) !!}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="pa-tarjeta">
                    <div style="display:flex; flex-wrap:wrap; justify-content:space-between; gap:1rem; align-items:baseline">
                        <h3>Partida a partida</h3>
                        <form method="GET">
                            <label class="pa-filtro">
                                <input type="checkbox" name="finalizadas" value="1" @checked($soloFinalizadas) onchange="this.form.submit()">
                                Solo finalizadas
                            </label>
                            <noscript><button type="submit">Aplicar</button></noscript>
                        </form>
                    </div>
                    <p class="pa-ayuda">
                        Pulsa en una columna para ordenar. «Fallos» suma respuestas o conexiones incorrectas de todos los módulos y los códigos de activación mal escritos.
                        En las partidas sin terminar, el tiempo es el transcurrido hasta ahora.
                    </p>

                    @if ($resumen['total'] === 0)
                        <p class="pa-vacio" style="margin-top:1rem">No hay partidas finalizadas todavía.</p>
                    @else
                        <div class="overflow-x-auto" style="margin-top: 1rem">
                            <table class="pa-tabla" id="tabla-partidas">
                                <thead>
                                    <tr>
                                        <th data-orden="texto">Partida</th>
                                        <th data-orden="texto" class="pa-izq">Estado</th>
                                        <th data-orden="numero">Tiempo<span class="pa-sub">min</span></th>
                                        <th data-orden="numero">Activo<span class="pa-sub">min</span></th>
                                        <th data-orden="numero">Fallos<span class="pa-sub">total</span></th>
                                        @foreach ($modulos as $numero => $modulo)
                                            <th data-orden="numero" title="{{ $modulo['nombre'] }}">M{{ $numero }}<span class="pa-sub">fallos</span></th>
                                        @endforeach
                                        <th data-orden="numero">Códigos<span class="pa-sub">erróneos</span></th>
                                        <th data-orden="numero">Pistas<span class="pa-sub">total (M1·M2·M3)</span></th>
                                        <th data-orden="texto">Batería<span class="pa-sub">al despegar</span></th>
                                        <th data-orden="texto">Opinión</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($resumen['filas'] as $fila)
                                        <tr>
                                            <td data-valor="{{ $fila['codigo'] }}">
                                                {{ $fila['codigo'] }}
                                                <span class="pa-sub">{{ $fila['inicio'] }}</span>
                                            </td>
                                            <td class="pa-izq" data-valor="{{ $fila['finalizada'] ? '0' : '1' }}{{ $fila['estado'] }}">
                                                @if ($fila['finalizada'])
                                                    <span class="pa-etiqueta pa-etiqueta--ok">Finalizada</span>
                                                @else
                                                    <span class="pa-etiqueta pa-etiqueta--curso">{{ $fila['estado'] }}</span>
                                                @endif
                                                @if ($fila['tiempo_agotado'])
                                                    <span class="pa-etiqueta pa-etiqueta--aviso" title="Se agotaron los 20 minutos de batería">20 min agotados</span>
                                                @endif
                                            </td>
                                            <td data-valor="{{ $fila['tiempo_min'] }}">{{ $num($fila['tiempo_min']) }}</td>
                                            <td data-valor="{{ $fila['tiempo_activo_min'] }}">{{ $num($fila['tiempo_activo_min']) }}</td>
                                            <td data-valor="{{ $fila['fallos_total'] }}"><b>{{ $num($fila['fallos_total']) }}</b></td>
                                            @foreach ($modulos as $numero => $modulo)
                                                <td data-valor="{{ $fila["m{$numero}_fallos"] }}" @class(['pa-gris' => $fila["m{$numero}_fallos"] === null])>{{ $num($fila["m{$numero}_fallos"]) }}</td>
                                            @endforeach
                                            <td data-valor="{{ $fila['codigos_incorrectos'] }}" @class(['pa-gris' => $fila['codigos_incorrectos'] === null])>{{ $num($fila['codigos_incorrectos']) }}</td>
                                            <td data-valor="{{ $fila['pistas'] }}">
                                                <b>{{ $num($fila['pistas']) }}</b>
                                                @if ($fila['pistas'])
                                                    <span class="pa-sub">{{ $num($fila['m1_pistas']) }} · {{ $num($fila['m2_pistas']) }} · {{ $num($fila['m3_pistas']) }}</span>
                                                @endif
                                            </td>
                                            <td data-valor="{{ $fila['bateria_restante'] }}" @class(['pa-gris' => ! $fila['bateria_restante']])>{{ $fila['bateria_restante'] ?? '—' }}</td>
                                            <td data-valor="{{ $fila['opinion'] ? '1' : '0' }}">
                                                @if ($fila['opinion'])
                                                    <a href="{{ route('admin.escape-room.opiniones') }}" class="text-indigo-600 hover:text-indigo-800">Sí</a>
                                                @else
                                                    <span class="pa-gris">No</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            @endif
        </div>
    </div>

    <script>
        // Ordenar la tabla al pulsar una cabecera. Los «—» (sin dato) van siempre al final.
        document.querySelectorAll('#tabla-partidas th[data-orden]').forEach((cabecera, columna) => {
            cabecera.addEventListener('click', () => {
                const tabla = cabecera.closest('table');
                const cuerpo = tabla.tBodies[0];
                const ascendente = cabecera.getAttribute('aria-sort') !== 'ascending';
                const numerico = cabecera.dataset.orden === 'numero';
                tabla.querySelectorAll('th').forEach((th) => th.removeAttribute('aria-sort'));
                cabecera.setAttribute('aria-sort', ascendente ? 'ascending' : 'descending');

                const filas = Array.from(cuerpo.rows);
                filas.sort((a, b) => {
                    const va = a.cells[columna].dataset.valor ?? '';
                    const vb = b.cells[columna].dataset.valor ?? '';
                    if (va === '' || vb === '') return (va === '') - (vb === '');
                    const resultado = numerico ? Number(va) - Number(vb) : va.localeCompare(vb, 'es');
                    return ascendente ? resultado : -resultado;
                });
                filas.forEach((fila) => cuerpo.appendChild(fila));
            });
        });
    </script>
</x-app-layout>
