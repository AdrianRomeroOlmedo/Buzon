@props([
    'titulo' => null,
    'descripcion' => 'Cátedra UGR - Ciudad Autónoma de Ceuta en Inteligencia Artificial y Tecnologías Emergentes para el Bienestar',
])
{{-- Plantilla de las páginas públicas de la cátedra (landing, proyectos). El buzón tiene la suya. --}}
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="description" content="{{ $descripcion }}">
<title>{{ $titulo ? $titulo.' · Cátedra Ceuta-IATEB' : 'Cátedra Ceuta-IATEB' }}</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('paloma2.svg') }}">
<link rel="stylesheet" href="{{ asset('styles.css') }}">
<style>
  .nav-brand img{height:52px; border-radius:6px;}
  .nav-brand span{font-size:1.2rem;}
  @media (max-width:639px){ .nav-brand img{height:44px;} .nav-brand span{display:none;} }

  .nav-links{display:flex; align-items:center; gap:22px;}
  .nav-links a:not(.btn){font-size:.9rem; font-weight:600; color:var(--ink-soft); text-decoration:none; white-space:nowrap;}
  .nav-links a:not(.btn):hover, .nav-links a[aria-current="page"]{color:var(--primary);}
  @media (max-width:899px){ .nav-links a:not(.btn){display:none;} }
</style>
@stack('estilos')
</head>
<body>
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
      <a class="btn btn-accent btn-sm" href="{{ route('sugerencias.create') }}">Buzón ciudadano</a>
    </nav>
  </div>
</header>

<main>
  {{ $slot }}
</main>

<footer>
  <div class="wrap">
    <div class="footer-row">
      <div class="footer-brand">
        <img src="{{ asset('logo-footer.webp') }}" alt="Cátedra Ceuta-IATEB" />
        <span class="footer-brand-text">Cátedra Ceuta-IATEB<br>Universidad de Granada · Ciudad Autónoma de Ceuta</span>
      </div>
      <div class="footer-links">
        <a href="{{ route('proyectos.index') }}">Proyectos</a>
        <a href="{{ route('sugerencias.create') }}">Buzón ciudadano</a>
        <a href="{{ route('privacidad') }}">Privacidad</a>
        <a href="https://catedras.ugr.es/iateb/" target="_blank" rel="noopener">catedras.ugr.es/iateb</a>
        <a href="https://www.ugr.es" target="_blank" rel="noopener">ugr.es</a>
        <a href="https://www.ceuta.es" target="_blank" rel="noopener">ceuta.es</a>
      </div>
    </div>
  </div>
</footer>
</body>
</html>
