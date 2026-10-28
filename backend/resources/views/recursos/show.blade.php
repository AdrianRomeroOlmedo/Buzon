@php
    $licencia = $recurso->licenciaDatos();
@endphp
<x-catedra :titulo="$recurso->titulo" :descripcion="$recurso->resumen()">
  @push('estilos')
  <style>
    .hero-recurso .hero-inner{padding-block:40px 52px;}
    .volver{color:#DCE6F7; font-size:.9rem; font-weight:600; text-decoration:none;}
    .volver:hover{color:#fff; text-decoration:underline;}

    .ficha{display:grid; grid-template-columns:minmax(0, 1fr); gap:36px;}
    @media (min-width:900px){ .ficha{grid-template-columns:minmax(0, 1fr) 320px; gap:56px; align-items:start;} }

    .rc-imagen{display:block; border-radius:14px; overflow:hidden; border:1px solid var(--line); background:var(--surface-2);}
    .rc-imagen img{width:100%; height:auto;}
    .rc-paginas{display:flex; flex-direction:column; gap:28px;}
    .rc-pagina{margin:0;}
    .rc-ampliar{display:inline-block; margin-top:10px; font-size:.88rem; font-weight:600; color:var(--primary);}
    .descargas-paginas{list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:6px; font-size:.9rem;}
    .descargas-paginas a{color:var(--primary); font-weight:600;}

    .ficha-datos{
      background:var(--surface); border:1px solid var(--line); border-radius:14px;
      padding:22px; display:flex; flex-direction:column; gap:18px;
    }
    @media (min-width:900px){ .ficha-datos{position:sticky; top:96px;} }
    .ficha-datos dl{margin:0; display:flex; flex-direction:column; gap:14px;}
    .ficha-datos dt{font-size:.75rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-faint);}
    .ficha-datos dd{margin:4px 0 0; color:var(--ink); font-size:.95rem; line-height:1.45;}
    .ficha-datos dd a{color:var(--primary);}
    .descargas{display:flex; flex-direction:column; gap:10px;}
    .descargas .btn{justify-content:center;}

    .prosa{color:var(--ink-soft); font-size:1rem; line-height:1.7;}
    .prosa h2.prosa-titulo{font-size:1.2rem; color:var(--ink); margin:0 0 .6em;}
    .prosa p, .prosa ul, .prosa ol{margin:0 0 1em;}
    .prosa li{margin-bottom:.4em;}
    .prosa strong{color:var(--ink);}
  </style>
  @endpush

  <section class="hero hero-recurso">
    <div class="wrap">
      <div class="hero-inner">
        <a class="volver" href="{{ route('recursos.index') }}">← Todos los recursos</a>
        <span class="eyebrow">{{ $recurso->tipoEtiqueta() }} · {{ $recurso->temaEtiqueta() }}</span>
        <h1>{{ $recurso->titulo }}</h1>
      </div>
    </div>
  </section>

  <section>
    <div class="wrap ficha">
      <div class="rc-paginas">
        {{-- El texto alternativo remite a la descripción completa de abajo, que es la versión accesible. --}}
        @foreach ($recurso->imagenes as $imagen)
          @php
              $alt = $recurso->tieneVariasPaginas()
                  ? "Página {$loop->iteration} de {$loop->count} de la infografía «{$recurso->titulo}». Su contenido se describe a continuación."
                  : "Infografía «{$recurso->titulo}». Su contenido se describe a continuación.";
          @endphp
          <figure class="rc-pagina">
            <a class="rc-imagen" href="{{ asset($imagen) }}" target="_blank" rel="noopener">
              <img src="{{ asset($imagen) }}" alt="{{ $alt }}" aria-describedby="contenido-recurso" @if (! $loop->first) loading="lazy" @endif>
            </a>
            <a class="rc-ampliar" href="{{ asset($imagen) }}" target="_blank" rel="noopener">
              {{ $recurso->tieneVariasPaginas() ? "Ver página {$loop->iteration} a tamaño completo" : 'Ver a tamaño completo' }} ↗
            </a>
          </figure>
        @endforeach
      </div>

      <aside class="ficha-datos" aria-label="Datos y descargas">
        <dl>
          @if ($recurso->autoria)
            <div><dt>Autoría</dt><dd>{{ $recurso->autoria }}</dd></div>
          @endif
          <div><dt>Tema</dt><dd>{{ $recurso->temaEtiqueta() }}</dd></div>
          @if ($licencia)
            <div><dt>Licencia</dt><dd><a href="{{ $licencia['url'] }}" target="_blank" rel="noopener license">{{ $licencia['etiqueta'] }}</a></dd></div>
          @endif
        </dl>
        <div class="descargas">
          @if ($recurso->pdf)
            <a class="btn btn-accent" href="{{ asset($recurso->pdf) }}" download>Descargar PDF</a>
          @endif
          @if ($recurso->tieneVariasPaginas())
            <ul class="descargas-paginas">
              @foreach ($recurso->imagenes as $imagen)
                <li><a href="{{ asset($imagen) }}" download>Página {{ $loop->iteration }} ({{ \App\Models\Recurso::formato($imagen) }})</a></li>
              @endforeach
            </ul>
          @else
            <a @class(['btn', 'btn-accent' => ! $recurso->pdf, 'btn-ghost' => $recurso->pdf]) href="{{ asset($recurso->imagenes[0]) }}" download>Descargar imagen ({{ \App\Models\Recurso::formato($recurso->imagenes[0]) }})</a>
          @endif
        </div>
      </aside>

      <article class="prosa" id="contenido-recurso">
        <h2 class="prosa-titulo">En esta infografía</h2>
        {!! $recurso->descripcionHtml() !!}
      </article>
    </div>
  </section>
</x-catedra>
