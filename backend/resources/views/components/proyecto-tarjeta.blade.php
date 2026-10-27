@props(['proyecto', 'destacado' => false])

@once
@push('estilos')
<style>
  .pt{
    display:flex; flex-direction:column;
    background:var(--surface); border:1px solid var(--line); border-radius:14px;
    overflow:hidden; text-decoration:none; color:inherit;
    transition:border-color .15s ease, transform .15s ease;
  }
  .pt:hover{border-color:var(--primary); transform:translateY(-2px);}
  .pt:focus-visible{outline:3px solid var(--focus); outline-offset:2px;}
  .pt-media{aspect-ratio:16 / 9; background:var(--pt-color); position:relative; overflow:hidden;}
  .pt-media img{width:100%; height:100%; object-fit:cover;}
  .pt-media-vacia::after{
    content:""; position:absolute; inset:0;
    background:url("{{ asset('paloma2.svg') }}") no-repeat right -8% bottom -30% / 55%;
    opacity:.18; filter:brightness(0) invert(1);
  }
  .pt-cuerpo{padding:18px 20px 20px; display:flex; flex-direction:column; gap:8px; flex:1;}
  .pt-meta{font-size:.8rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--ink-faint);}
  .pt h3{font-size:1.1rem; color:var(--ink);}
  .pt p{margin:0; color:var(--ink-soft); font-size:.94rem; line-height:1.55;}
  .pt-mas{margin-top:auto; padding-top:8px; font-weight:700; font-size:.9rem; color:var(--primary);}
  .pt-estado{display:inline-block; font-size:.75rem; font-weight:700; padding:4px 10px; border-radius:999px; background:var(--surface-2); color:var(--primary);}
  .pt-estado.en-marcha{background:#E3F4EC; color:#0F6E56;}

  .pt.pt-destacado{display:grid; grid-template-columns:1fr;}
  @media (min-width:760px){
    .pt.pt-destacado{grid-template-columns:1.15fr 1fr;}
    .pt.pt-destacado .pt-media{aspect-ratio:auto; min-height:100%;}
  }
  .pt.pt-destacado .pt-cuerpo{padding:28px; gap:12px; justify-content:center;}
  .pt.pt-destacado h3{font-size:1.6rem;}
  .pt.pt-destacado p{font-size:1rem;}

  @media (prefers-color-scheme: dark){
    :root:not([data-theme="light"]) .pt-estado.en-marcha{background:#123A30; color:#7FD6B4;}
  }
</style>
@endpush
@endonce

<a {{ $attributes->class(['pt', 'pt-destacado' => $destacado]) }} href="{{ route('proyectos.show', $proyecto) }}" style="--pt-color: {{ $proyecto->lineaColor() }};">
  <div @class(['pt-media', 'pt-media-vacia' => ! $proyecto->imagen])>
    @if ($proyecto->imagen)
      <img src="{{ asset($proyecto->imagen) }}" alt="" loading="lazy">
    @endif
  </div>
  <div class="pt-cuerpo">
    @if ($destacado)
      <div><span @class(['pt-estado', $proyecto->estado])>{{ $proyecto->estadoEtiqueta() }}</span></div>
    @endif
    <span class="pt-meta">{{ $proyecto->lineaEtiqueta() }}@if ($proyecto->periodo()) · {{ $proyecto->periodo() }}@endif</span>
    <h3>{{ $proyecto->titulo }}</h3>
    <p>{{ $proyecto->resumen }}</p>
    <span class="pt-mas">Ver proyecto →</span>
  </div>
</a>
