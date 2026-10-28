@php
    // Solo rutas internas o http(s): nunca javascript: ni similares.
    $enlace = preg_match('#^(/(?!/)|https?://)#i', (string) $proyecto->enlace_url) ? $proyecto->enlace_url : null;
    $enlaceExterno = $enlace && ! str_starts_with($enlace, '/');
@endphp
<x-catedra :titulo="$proyecto->titulo" :descripcion="$proyecto->resumen">
  @push('estilos')
  <style>
    .hero-proyecto .hero-inner{padding-block:40px 52px;}
    .volver{color:#DCE6F7; font-size:.9rem; font-weight:600; text-decoration:none;}
    .volver:hover{color:#fff; text-decoration:underline;}

    .ficha{display:grid; grid-template-columns:minmax(0, 1fr); gap:36px;}
    @media (min-width:900px){ .ficha{grid-template-columns:minmax(0, 1fr) 300px; gap:56px; align-items:start;} }

    .ficha-imagen{border-radius:14px; overflow:hidden; border:1px solid var(--line); margin-bottom:28px;}
    .ficha-imagen img{width:100%; height:auto;}

    .prosa{color:var(--ink-soft); font-size:1.05rem; line-height:1.7; max-width:68ch;}
    .prosa > :first-child{margin-top:0;}
    .prosa h2{font-size:1.4rem; margin:1.8em 0 .5em; color:var(--ink);}
    .prosa h3{font-size:1.15rem; margin:1.5em 0 .4em; color:var(--ink);}
    .prosa p, .prosa ul, .prosa ol{margin:0 0 1em;}
    .prosa li{margin-bottom:.4em;}
    .prosa strong{color:var(--ink);}
    .prosa a{color:var(--primary); text-underline-offset:3px;}

    .ficha-datos{
      background:var(--surface); border:1px solid var(--line); border-radius:14px;
      padding:22px; display:flex; flex-direction:column; gap:16px;
    }
    @media (min-width:900px){ .ficha-datos{position:sticky; top:96px;} }
    .ficha-datos dl{margin:0; display:flex; flex-direction:column; gap:14px;}
    .ficha-datos dt{font-size:.75rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-faint);}
    .ficha-datos dd{margin:4px 0 0; color:var(--ink); font-size:.95rem; line-height:1.45;}
    .ficha-datos .btn{justify-content:center;}
  </style>
  @endpush

  <section class="hero hero-proyecto">
    <div class="wrap">
      <div class="hero-inner">
        <a class="volver" href="{{ route('proyectos.index') }}">← Todos los proyectos</a>
        <span class="eyebrow">{{ $proyecto->lineaEtiqueta() }}</span>
        <h1>{{ $proyecto->titulo }}</h1>
        <p class="lead">{{ $proyecto->resumen }}</p>
      </div>
    </div>
  </section>

  <section>
    <div class="wrap ficha">
      <article>
        @if ($proyecto->imagen)
          <div class="ficha-imagen"><img src="{{ asset($proyecto->imagen) }}" alt=""></div>
        @endif
        <div class="prosa">{!! $proyecto->descripcionHtml() !!}</div>
      </article>

      <aside class="ficha-datos" aria-label="Datos del proyecto">
        <dl>
          <div><dt>Estado</dt><dd>{{ $proyecto->estadoEtiqueta() }}</dd></div>
          <div><dt>Línea de trabajo</dt><dd>{{ $proyecto->lineaEtiqueta() }}</dd></div>
          @if ($proyecto->periodo())
            <div><dt>Fechas</dt><dd>{{ $proyecto->periodo() }}</dd></div>
          @endif
          @if ($proyecto->socios)
            <div><dt>Socios</dt><dd>{{ $proyecto->socios }}</dd></div>
          @endif
        </dl>
        @if ($enlace)
          <a class="btn btn-accent" href="{{ $enlace }}" @if ($enlaceExterno) target="_blank" rel="noopener" @endif>
            {{ $proyecto->enlace_texto ?: 'Más información' }} {{ $enlaceExterno ? '↗' : '→' }}
          </a>
        @endif
      </aside>
    </div>
  </section>
</x-catedra>
