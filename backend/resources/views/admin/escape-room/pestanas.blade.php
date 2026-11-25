{{-- Pestañas comunes de las páginas del escape room en el panel. --}}
<nav class="er-pestanas" aria-label="Secciones del escape room">
    <a href="{{ route('admin.escape-room.partidas') }}" @class(['er-pestana', 'er-pestana--activa' => request()->routeIs('admin.escape-room.partidas')])
       @if (request()->routeIs('admin.escape-room.partidas')) aria-current="page" @endif>Partidas</a>
    <a href="{{ route('admin.escape-room.opiniones') }}" @class(['er-pestana', 'er-pestana--activa' => request()->routeIs('admin.escape-room.opiniones')])
       @if (request()->routeIs('admin.escape-room.opiniones')) aria-current="page" @endif>Opiniones</a>
</nav>

@once
    <style>
        .er-pestanas { display: flex; gap: .25rem; margin-bottom: 1.5rem; border-bottom: 1px solid #e5e7eb; }
        .er-pestana { padding: .6rem 1rem; font-size: .9rem; font-weight: 500; color: #6b7280; border-bottom: 2px solid transparent; margin-bottom: -1px; }
        .er-pestana:hover { color: #111827; }
        .er-pestana--activa { color: #3730a3; border-bottom-color: #4f46e5; }
    </style>
@endonce
