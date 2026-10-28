{{-- Nav común de toda la web pública. Los estilos (.nav-brand, .nav-links) están en public/styles.css. --}}
@php
    $enBuzon = request()->routeIs('sugerencias.create');
@endphp
<header class="nav">
  <div class="wrap">
    <a class="nav-brand" href="{{ route('inicio') }}" style="text-decoration:none;">
      <img src="{{ asset('logo-nav.webp') }}" alt="Cátedra Ceuta-IATEB" />
      <span>Cátedra Ceuta-IATEB</span>
    </a>
    <nav class="nav-links" aria-label="Secciones">
      <a href="{{ route('inicio') }}#quienes-somos">Quiénes somos</a>
      <a href="{{ route('inicio') }}#lineas">Líneas de trabajo</a>
      <a href="{{ route('proyectos.index') }}" @if (request()->routeIs('proyectos.*')) aria-current="page" @endif>Proyectos</a>
      <a href="{{ route('recursos.index') }}" @if (request()->routeIs('recursos.*')) aria-current="page" @endif>Recursos</a>
      {{-- Dentro del buzón, el botón lleva directo al formulario. --}}
      <a class="btn btn-accent btn-sm" href="{{ $enBuzon ? '#formulario' : route('sugerencias.create') }}" @if ($enBuzon) aria-current="page" @endif>Buzón ciudadano</a>
    </nav>
  </div>
</header>
