<x-catedra titulo="Proyectos" descripcion="Proyectos de la Cátedra Ceuta-IATEB: inteligencia artificial y tecnologías emergentes para el bienestar en Ceuta.">
  @push('estilos')
  <style>
    .hero-proyectos .hero-inner{padding-block:52px 56px;}
    .pt-grid{display:grid; grid-template-columns:1fr; gap:20px; margin-top:28px;}
    @media (min-width:640px){ .pt-grid{grid-template-columns:1fr 1fr;} }
    @media (min-width:960px){ .pt-grid{grid-template-columns:1fr 1fr 1fr;} }
    .proyectos-vacio{color:var(--ink-soft); font-size:1.05rem;}
  </style>
  @endpush

  <section class="hero hero-proyectos">
    <div class="wrap">
      <div class="hero-inner">
        <span class="eyebrow">Proyectos</span>
        <h1>Lo que estamos construyendo</h1>
        <p class="lead">Investigación y herramientas de inteligencia artificial pensadas para responder a necesidades reales de la ciudadanía de Ceuta.</p>
      </div>
    </div>
  </section>

  <section>
    <div class="wrap">
      @if ($destacado)
        <x-proyecto-tarjeta :proyecto="$destacado" destacado />

        @if ($resto->isNotEmpty())
          <div class="pt-grid">
            @foreach ($resto as $proyecto)
              <x-proyecto-tarjeta :proyecto="$proyecto" />
            @endforeach
          </div>
        @endif
      @else
        <p class="proyectos-vacio">Muy pronto publicaremos aquí los proyectos de la cátedra.</p>
      @endif
    </div>
  </section>
</x-catedra>
