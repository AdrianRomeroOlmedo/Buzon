<?php

use App\Support\MetricasIa;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component {
    #[Url(except: '')] public string $desde = '';
    #[Url(except: '')] public string $hasta = '';
    #[Url(except: '')] public string $modelo = '';
    #[Url(except: '')] public string $origen = '';

    /** Por debajo de esta muestra el porcentaje se marca como poco fiable. */
    public const MUESTRA_MINIMA = 30;

    public function boot(): void
    {
        Gate::authorize('revisar-sugerencias');
    }

    public function limpiar(): void
    {
        $this->reset(['desde', 'hasta', 'modelo', 'origen']);
    }

    /**
     * @return array<string, mixed>
     */
    #[Computed]
    public function metricas(): array
    {
        return MetricasIa::calcular(
            $this->fecha($this->desde),
            $this->fecha($this->hasta),
            $this->modelo ?: null,
            $this->origen ?: null,
        );
    }

    /**
     * @return list<string>
     */
    #[Computed]
    public function versiones(): array
    {
        return MetricasIa::versiones();
    }

    private function fecha(string $valor): ?Carbon
    {
        try {
            return $valor === '' ? null : Carbon::createFromFormat('Y-m-d', $valor);
        } catch (\Throwable) {
            return null;
        }
    }
};
?>

@php
    $m = $this->metricas;
    $r = $m['resumen'];
    $minima = $this::MUESTRA_MINIMA;
    $color = fn (?float $pct) => match (true) {
        $pct === null => 'bg-gray-200',
        $pct >= 90 => 'bg-green-500',
        $pct >= 70 => 'bg-amber-400',
        default => 'bg-red-500',
    };
    $tarjetas = [
        ['Categoría', $m['clasificacion']['categoria'], null],
        ['Subcategorías (exactas)', $m['clasificacion']['subcategorias'], $m['clasificacion']['subcategorias_parcial'] !== null ? 'Acierto parcial medio: '.number_format($m['clasificacion']['subcategorias_parcial'], 1, ',', '.').' %' : null],
        ['Organismo', $m['clasificacion']['organismo'], 'Solo sugerencias con organismo confirmado'],
        ['Urgencia', $m['clasificacion']['urgencia'], null],
        ['Anonimización sin fugas', $m['anonimizacion']['sin_fugas'], 'Sin datos personales que se le escaparan a la IA'],
    ];
    $pct = fn (?float $v) => $v === null ? '—' : number_format($v, 1, ',', '.').' %';
@endphp

<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

    {{-- Filtros --}}
    <div class="bg-white shadow-sm sm:rounded-lg p-4 flex flex-wrap items-end gap-4 text-sm">
        <label class="block">
            <span class="block text-xs font-medium text-gray-500 mb-1">Desde</span>
            <input type="date" wire:model.live="desde" max="{{ $hasta ?: '' }}" class="rounded-md border-gray-300 text-sm">
        </label>
        <label class="block">
            <span class="block text-xs font-medium text-gray-500 mb-1">Hasta</span>
            <input type="date" wire:model.live="hasta" min="{{ $desde ?: '' }}" class="rounded-md border-gray-300 text-sm">
        </label>
        <label class="block">
            <span class="block text-xs font-medium text-gray-500 mb-1">Versión del modelo</span>
            <select wire:model.live="modelo" class="rounded-md border-gray-300 text-sm">
                <option value="">Todas</option>
                @foreach ($this->versiones as $version)
                    <option value="{{ $version }}">{{ $version }}</option>
                @endforeach
                <option value="{{ \App\Support\MetricasIa::SIN_VERSION }}">Sin versión</option>
            </select>
        </label>
        <label class="block">
            <span class="block text-xs font-medium text-gray-500 mb-1">Origen</span>
            <select wire:model.live="origen" class="rounded-md border-gray-300 text-sm">
                <option value="">Reales y sintéticas</option>
                @foreach (\App\Support\MetricasIa::ORIGENES as $valor => $etiqueta)
                    <option value="{{ $valor }}">{{ $etiqueta }}</option>
                @endforeach
            </select>
        </label>
        @if ($desde || $hasta || $modelo || $origen)
            <button type="button" wire:click="limpiar" class="px-3 py-2 text-gray-600 hover:text-gray-900">Quitar filtros</button>
        @endif
        <span wire:loading class="text-xs text-gray-400">Calculando…</span>
    </div>

    {{-- Resumen --}}
    <div class="bg-white shadow-sm sm:rounded-lg p-4 text-sm text-gray-600 flex flex-wrap gap-x-6 gap-y-2">
        <span><strong class="text-gray-900">{{ $r['sugerencias'] }}</strong> sugerencias recibidas</span>
        <span><strong class="text-gray-900">{{ $r['procesadas'] }}</strong> procesadas por la IA</span>
        <span><strong class="text-gray-900">{{ $r['clasificacion_evaluada'] }}</strong> con clasificación evaluada</span>
        <span><strong class="text-gray-900">{{ $r['privacidad_evaluada'] }}</strong> con anonimización evaluada</span>
        <span><strong class="text-gray-900">{{ $r['pendientes'] }}</strong> pendientes de evaluar</span>
        <span class="{{ $r['errores_tecnicos'] ? 'text-red-700' : '' }}"><strong>{{ $r['errores_tecnicos'] }}</strong> errores técnicos de la IA</span>
    </div>

    {{-- Acierto por campo --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        @foreach ($tarjetas as [$titulo, $dato, $nota])
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <p class="text-xs font-medium text-gray-500">{{ $titulo }}</p>
                <p class="mt-1 text-3xl font-semibold tabular-nums text-gray-900">{{ $pct($dato['pct']) }}</p>
                <div class="mt-2 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                    <div class="h-full {{ $color($dato['pct']) }}" style="width: {{ $dato['pct'] ?? 0 }}%"></div>
                </div>
                <p class="mt-2 text-xs text-gray-500 tabular-nums">
                    {{ $dato['aciertos'] }} de {{ $dato['n'] }}
                    @if ($dato['n'])· IC 95 %: {{ $pct($dato['min']) }}–{{ $pct($dato['max']) }}@endif
                </p>
                @if ($dato['n'] && $dato['n'] < $minima)
                    <p class="mt-1 text-xs text-amber-700">Pocas muestras: tómalo con cautela.</p>
                @endif
                @if ($nota)<p class="mt-1 text-xs text-gray-400">{{ $nota }}</p>@endif
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Urgencia --}}
        <section class="bg-white shadow-sm sm:rounded-lg p-6 text-sm">
            <h3 class="font-semibold text-gray-900">Urgencia</h3>
            <p class="mt-1 text-xs text-gray-500">Quedarse corta es el error grave: una sugerencia urgente tratada como ordinaria.</p>
            @php $u = $m['urgencia']; @endphp
            <dl class="mt-4 grid grid-cols-3 gap-4">
                <div>
                    <dt class="text-xs text-gray-500">Se quedó corta</dt>
                    <dd class="text-2xl font-semibold tabular-nums {{ $u['infraestimada'] ? 'text-red-700' : 'text-gray-900' }}">{{ $u['infraestimada'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Se pasó</dt>
                    <dd class="text-2xl font-semibold tabular-nums text-gray-900">{{ $u['sobreestimada'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Urgentes detectadas</dt>
                    <dd class="text-2xl font-semibold tabular-nums text-gray-900">{{ $pct($u['urgentes_detectadas']['pct']) }}</dd>
                    <dd class="text-xs text-gray-500">{{ $u['urgentes_detectadas']['aciertos'] }} de {{ $u['urgentes_detectadas']['n'] }}</dd>
                </div>
            </dl>
            <p class="mt-3 text-xs text-gray-400">Sobre {{ $u['n'] }} sugerencias con urgencia confirmada.</p>
        </section>

        {{-- IA frente al ciudadano y sesgo --}}
        <section class="bg-white shadow-sm sm:rounded-lg p-6 text-sm">
            <h3 class="font-semibold text-gray-900">Categoría: IA frente al ciudadano</h3>
            <p class="mt-1 text-xs text-gray-500">Sobre las mismas sugerencias: quién acertó la categoría que confirmó la persona revisora.</p>
            <div class="mt-4 space-y-3">
                @foreach ([['IA', $m['ciudadano']['ia'], 'bg-violet-500'], ['Ciudadano', $m['ciudadano']['ciudadano'], 'bg-gray-400']] as [$quien, $dato, $barra])
                    <div>
                        <div class="flex justify-between text-xs"><span class="text-gray-700">{{ $quien }}</span><span class="tabular-nums text-gray-500">{{ $pct($dato['pct']) }} · {{ $dato['aciertos'] }}/{{ $dato['n'] }}</span></div>
                        <div class="mt-1 h-2 rounded-full bg-gray-100 overflow-hidden"><div class="h-full {{ $barra }}" style="width: {{ $dato['pct'] ?? 0 }}%"></div></div>
                    </div>
                @endforeach
            </div>

            <h4 class="mt-6 font-medium text-gray-900">Independencia de la revisión</h4>
            <p class="mt-1 text-xs text-gray-500">Acierto en categoría según se pulsara «Usar» en la propuesta de la IA antes de confirmar. Si partir de la IA da mucho más acierto, la revisión puede estar dando por buena la propuesta sin mirarla.</p>
            <table class="mt-3 w-full text-xs">
                <tbody>
                    @foreach ([['Partiendo de la propuesta', $m['sesgo']['aplicada']], ['Clasificando sin usarla', $m['sesgo']['independiente']]] as [$etiqueta, $dato])
                        <tr class="border-t border-gray-100">
                            <td class="py-1.5 text-gray-700">{{ $etiqueta }}</td>
                            <td class="py-1.5 text-right tabular-nums">{{ $pct($dato['pct']) }}</td>
                            <td class="py-1.5 text-right tabular-nums text-gray-500">n = {{ $dato['n'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        {{-- Errores de categoría --}}
        <section class="bg-white shadow-sm sm:rounded-lg p-6 text-sm">
            <h3 class="font-semibold text-gray-900">Errores de categoría más frecuentes</h3>
            @if ($m['confusion'])
                <table class="mt-3 w-full">
                    <thead><tr class="text-left text-xs text-gray-500"><th class="py-1.5">La IA dijo</th><th class="py-1.5">Era</th><th class="py-1.5 text-right">Veces</th></tr></thead>
                    <tbody>
                        @foreach ($m['confusion'] as $fila)
                            <tr class="border-t border-gray-100">
                                <td class="py-1.5 text-violet-800">{{ $fila['ia'] }}</td>
                                <td class="py-1.5 text-gray-900">{{ $fila['correcta'] }}</td>
                                <td class="py-1.5 text-right tabular-nums">{{ $fila['veces'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="mt-3 text-gray-400">Sin errores de categoría en este periodo.</p>
            @endif
        </section>

        {{-- Acierto por categoría --}}
        <section class="bg-white shadow-sm sm:rounded-lg p-6 text-sm">
            <h3 class="font-semibold text-gray-900">Acierto según la categoría correcta</h3>
            @if ($m['por_categoria'])
                <table class="mt-3 w-full">
                    <tbody>
                        @foreach ($m['por_categoria'] as $fila)
                            <tr class="border-t border-gray-100">
                                <td class="py-1.5 text-gray-900">{{ $fila['categoria'] }}</td>
                                <td class="py-1.5 w-1/3">
                                    <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden"><div class="h-full {{ $color($fila['pct']) }}" style="width: {{ $fila['pct'] ?? 0 }}%"></div></div>
                                </td>
                                <td class="py-1.5 text-right tabular-nums">{{ $pct($fila['pct']) }}</td>
                                <td class="py-1.5 text-right tabular-nums text-gray-500">{{ $fila['aciertos'] }}/{{ $fila['n'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="mt-3 text-gray-400">Todavía no hay clasificaciones evaluadas.</p>
            @endif
        </section>

        {{-- Anonimización --}}
        <section class="bg-white shadow-sm sm:rounded-lg p-6 text-sm">
            <h3 class="font-semibold text-gray-900">Anonimización</h3>
            @php $a = $m['anonimizacion']; @endphp
            <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-3">
                <div><dt class="text-xs text-gray-500">Datos que se le escaparon (marcados)</dt><dd class="text-xl font-semibold tabular-nums {{ $a['fugas_marcadas'] ? 'text-red-700' : 'text-gray-900' }}">{{ $a['fugas_marcadas'] }}</dd></div>
                <div><dt class="text-xs text-gray-500">Con identificadores detectados</dt><dd class="text-xl font-semibold tabular-nums {{ $a['fugas_detector'] ? 'text-red-700' : 'text-gray-900' }}">{{ $a['fugas_detector'] }}</dd></div>
                <div><dt class="text-xs text-gray-500">Tapó información no personal</dt><dd class="text-xl font-semibold tabular-nums text-gray-900">{{ $a['sobreanonimizada'] }}</dd></div>
                <div><dt class="text-xs text-gray-500">Aceptado sin editar</dt><dd class="text-xl font-semibold tabular-nums text-gray-900">{{ $pct($a['sin_editar']['pct']) }}</dd></div>
                <div><dt class="text-xs text-gray-500">Parecido medio con el texto final</dt><dd class="text-xl font-semibold tabular-nums text-gray-900">{{ $pct($a['similitud_media']) }}</dd></div>
            </dl>
            <p class="mt-3 text-xs text-gray-400">Sobre {{ $a['sin_fugas']['n'] }} textos evaluados. El detector solo encuentra identificadores directos (DNI, teléfono, correo…), no nombres propios.</p>
        </section>

        {{-- Por versión --}}
        <section class="bg-white shadow-sm sm:rounded-lg p-6 text-sm">
            <h3 class="font-semibold text-gray-900">Por versión del modelo</h3>
            @if ($m['por_version'])
                <table class="mt-3 w-full">
                    <thead><tr class="text-left text-xs text-gray-500"><th class="py-1.5">Versión</th><th class="py-1.5 text-right">Categoría</th><th class="py-1.5 text-right">Urgencia</th><th class="py-1.5 text-right">Sin fugas</th></tr></thead>
                    <tbody>
                        @foreach ($m['por_version'] as $fila)
                            <tr class="border-t border-gray-100">
                                <td class="py-1.5 text-gray-900">{{ $fila['version'] }}</td>
                                @foreach (['categoria', 'urgencia', 'sin_fugas'] as $campo)
                                    <td class="py-1.5 text-right tabular-nums">
                                        {{ $pct($fila[$campo]['pct']) }} <span class="text-xs text-gray-400">({{ $fila[$campo]['n'] }})</span>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="mt-3 text-gray-400">Todavía no hay propuestas evaluadas.</p>
            @endif
        </section>
    </div>

    <p class="text-xs text-gray-400 pb-4">
        Se cuenta solo la propuesta más reciente de cada sugerencia y se excluyen las de la papelera. El acierto se anota al confirmar la clasificación
        y al marcar la privacidad como revisada. IC 95 % = intervalo de confianza (Wilson): con pocas muestras sale ancho.
    </p>
</div>
