@props([
    'titulo' => null,
    'descripcion' => 'Cátedra UGR - Ciudad Autónoma de Ceuta en Inteligencia Artificial y Tecnologías Emergentes para el Bienestar',
])
{{-- Plantilla de las páginas públicas de la cátedra (landing, proyectos, recursos).
     El buzón y privacidad tienen su propia plantilla, pero usan el mismo nav y footer. --}}
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="description" content="{{ $descripcion }}">
<title>{{ $titulo ? $titulo.' · Cátedra Ceuta-IATEB' : 'Cátedra Ceuta-IATEB' }}</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('paloma2.svg') }}">
<link rel="stylesheet" href="{{ asset('styles.css') }}?v={{ config('app.version_estilos') }}">
@stack('estilos')
</head>
<body>
<x-catedra-nav />

<main>
  {{ $slot }}
</main>

<x-catedra-footer />
</body>
</html>
