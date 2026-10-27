<x-catedra titulo="Recursos" descripcion="Infografías divulgativas de la Cátedra Ceuta-IATEB sobre inteligencia artificial, educación, emprendimiento, investigación y ciberseguridad.">
  @push('estilos')
  <style>
    .hero-recursos .hero-inner{padding-block:52px 56px;}

    .filtros{display:flex; flex-wrap:wrap; gap:8px; margin-bottom:28px;}
    .filtros a{
      font-size:.88rem; font-weight:600; text-decoration:none; color:var(--ink-soft);
      border:1px solid var(--line); border-radius:999px; padding:8px 16px; background:var(--surface);
    }
    .filtros a:hover{border-color:var(--primary); color:var(--primary);}
    .filtros a[aria-current="true"]{background:var(--primary); border-color:var(--primary); color:#fff;}

    .rc-grid{display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:20px;}
    @media (min-width:760px){ .rc-grid{grid-template-columns:repeat(3, minmax(0, 1fr));} }
    @media (min-width:1000px){ .rc-grid{grid-template-columns:repeat(4, minmax(0, 1fr));} }

    .rc{display:flex; flex-direction:column; gap:10px; text-decoration:none; color:inherit;}
    .rc-mini{
      aspect-ratio:3 / 4; border-radius:12px; overflow:hidden;
      border:1px solid var(--line); background:var(--surface-2);
      transition:border-color .15s ease, transform .15s ease;
    }
    .rc-mini img{width:100%; height:100%; object-fit:cover; object-position:top;}
    .rc:hover .rc-mini{border-color:var(--primary); transform:translateY(-2px);}
    .rc:focus-visible{outline:3px solid var(--focus); outline-offset:4px; border-radius:12px;}
    .rc h3{font-size:1rem; line-height:1.3; color:var(--ink);}
    .rc-meta{font-size:.78rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--ink-faint);}
    .recursos-vacio{color:var(--ink-soft); font-size:1.05rem;}
  </style>
  @endpush

  <section class="hero hero-recursos">
    <div class="wrap">
      <div class="hero-inner">
        <span class="eyebrow">Recursos</span>
        <h1>Infografías de la cátedra</h1>
        <p class="lead">Materiales divulgativos sobre inteligencia artificial y tecnologías emergentes, para consultar, descargar y compartir citando su autoría.</p>
      </div>
    </div>
  </section>

  <section id="galeria">
    <div class="wrap">
      @if ($temas)
        <nav class="filtros" aria-label="Filtrar por tema">
          <a href="{{ route('recursos.index') }}#galeria" @if (! $temaActivo) aria-current="true" @endif>Todas</a>
          @foreach ($temas as $clave => $etiqueta)
            <a href="{{ route('recursos.index', ['tema' => $clave]) }}#galeria" @if ($temaActivo === $clave) aria-current="true" @endif>{{ $etiqueta }}</a>
          @endforeach
        </nav>
      @endif

      @if ($recursos->isNotEmpty())
        <div class="rc-grid">
          @foreach ($recursos as $recurso)
            <a class="rc" href="{{ route('recursos.show', $recurso) }}">
              <div class="rc-mini">
                <img src="{{ asset($recurso->miniatura) }}" alt="" loading="lazy" width="480" height="640">
              </div>
              <span class="rc-meta">{{ $recurso->temaEtiqueta() }}</span>
              <h3>{{ $recurso->titulo }}</h3>
            </a>
          @endforeach
        </div>
      @else
        <p class="recursos-vacio">Muy pronto publicaremos aquí los recursos de la cátedra.</p>
      @endif
    </div>
  </section>
</x-catedra>
