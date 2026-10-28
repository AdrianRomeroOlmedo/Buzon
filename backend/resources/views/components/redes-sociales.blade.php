@php
    $instagramUrl = $redes['instagram']['url'];
    $facebookUrl = $redes['facebook']['url'];
    $linkedinUrl = $redes['linkedin']['url'];
    $linkedinEmbed = $redes['linkedin']['post_embed'];

    $instagramUsuario = $instagramUrl ? '@'.trim((string) parse_url($instagramUrl, PHP_URL_PATH), '/') : null;

    $facebookEmbed = $facebookUrl && $redes['facebook']['embed']
        ? 'https://www.facebook.com/plugins/page.php?'.http_build_query([
            'href' => $facebookUrl,
            'tabs' => 'timeline',
            'width' => 500,
            'height' => 600,
            'small_header' => 'true',
            'adapt_container_width' => 'true',
            'hide_cover' => 'false',
            'show_facepile' => 'false',
        ])
        : null;
@endphp

@once
<style>
  .redes{display:flex; flex-direction:column; gap:12px; position:relative; z-index:1;}
  .redes-titulo{margin:0; font-size:.78rem; font-weight:700; letter-spacing:.14em; text-transform:uppercase; color:var(--wing);}

  .red-card{
    background:var(--surface);
    color:var(--ink);
    border-radius:14px;
    box-shadow:0 22px 45px rgba(8,20,48,.28);
    overflow:hidden;
  }
  .red-head{display:flex; align-items:center; gap:10px; padding:14px 16px;}
  .red-head svg{flex:none; color:var(--primary);}
  .red-head strong{font-size:.92rem;}
  .red-head span{font-size:.82rem; color:var(--ink-faint); margin-left:auto;}
  .red-img{display:block; aspect-ratio:5 / 4; background:var(--surface-2);}
  .red-img img{width:100%; height:100%; object-fit:cover;}
  .red-cuerpo{padding:14px 16px 16px; display:flex; flex-direction:column; gap:10px;}
  .red-texto{
    margin:0; font-size:.9rem; line-height:1.5; color:var(--ink-soft);
    display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;
  }
  .red-pie{display:flex; align-items:center; justify-content:space-between; gap:12px; font-size:.82rem; color:var(--ink-faint);}
  .red-pie a, .red-mini a{color:var(--primary); font-weight:700; text-decoration:none;}
  .red-pie a:hover, .red-mini a:hover{text-decoration:underline;}

  .redes-fila{display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:12px;}
  .red-mini{
    background:rgba(255,255,255,.1);
    border:1px solid rgba(255,255,255,.22);
    border-radius:14px;
    padding:14px;
    color:#fff;
    display:flex; flex-direction:column; gap:10px;
  }
  .red-mini .red-head{padding:0;}
  .red-mini .red-head svg{color:#fff;}
  .red-mini a{color:#fff; font-size:.82rem;}
  .red-mini .btn{justify-content:center; background:#fff; color:var(--primary-deep); padding:8px 14px; font-size:.82rem;}
  .red-mini .btn:hover{background:#E6EDFA;}
  .redes-aviso{margin:0; font-size:.75rem; line-height:1.45; color:#B7CBEE;}

  .red-dialog{
    width:min(540px, calc(100vw - 32px));
    max-height:calc(100vh - 32px);
    padding:0; border:none; border-radius:14px;
    background:var(--surface); color:var(--ink);
    box-shadow:0 30px 60px rgba(8,20,48,.35);
  }
  .red-dialog::backdrop{background:rgba(8,20,48,.6);}
  .red-dialog-head{display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 18px; border-bottom:1px solid var(--line);}
  .red-dialog-head h2{font-size:1.1rem;}
  .red-dialog-head button{background:none; border:none; color:var(--ink-soft); font-size:1.5rem; line-height:1; cursor:pointer; padding:4px 8px;}
  .red-dialog-cuerpo{display:flex; justify-content:center; padding:12px; background:var(--surface-2);}
  .red-dialog-cuerpo iframe{width:100%; max-width:500px; height:min(600px, calc(100vh - 140px)); border:none; background:#fff; border-radius:8px;}
  .red-dialog-pie{margin:0; padding:12px 18px 16px; font-size:.82rem; color:var(--ink-faint); border-top:1px solid var(--line);}
  .red-dialog-pie a{color:var(--primary); font-weight:700;}
</style>
@endonce

<aside class="redes" aria-label="Redes sociales de la cátedra">
  <p class="redes-titulo">Síguenos</p>

  @if ($instagramUrl)
    <article class="red-card">
      <div class="red-head">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.8"/><circle cx="17.3" cy="6.7" r="1.1" fill="currentColor"/></svg>
        <strong>Instagram</strong>
        <span>{{ $instagramUsuario }}</span>
      </div>

      @if ($instagramPost)
        @if ($instagramImagen)
          <a class="red-img" href="{{ $instagramPost['enlace'] }}" target="_blank" rel="noopener">
            <img src="{{ route('redes.instagram.imagen', ['v' => $instagramPost['id']]) }}" alt="Última publicación de la cátedra en Instagram" loading="lazy">
          </a>
        @endif
        <div class="red-cuerpo">
          @if ($instagramPost['texto'] !== '')
            <p class="red-texto">{{ $instagramPost['texto'] }}</p>
          @endif
          <div class="red-pie">
            <span>{{ $instagramPost['fecha'] }}</span>
            <a href="{{ $instagramPost['enlace'] }}" target="_blank" rel="noopener">Ver en Instagram ↗</a>
          </div>
        </div>
      @else
        <div class="red-cuerpo">
          <p class="red-texto">Fotos y novedades de nuestras actividades, jornadas y proyectos.</p>
          <div class="red-pie">
            <span></span>
            <a href="{{ $instagramUrl }}" target="_blank" rel="noopener">Ver perfil ↗</a>
          </div>
        </div>
      @endif
    </article>
  @endif

  @if ($facebookUrl || $linkedinUrl)
    <div class="redes-fila">
      @if ($facebookUrl)
        <div class="red-mini">
          <div class="red-head">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-7.5h2.6l.4-3h-3V8.6c0-.9.3-1.5 1.5-1.5h1.6V4.4c-.3 0-1.2-.1-2.3-.1-2.3 0-3.9 1.4-3.9 4v2.2H7.8v3h2.6V21h3.1z"/></svg>
            <strong>Facebook</strong>
          </div>
          @if ($facebookEmbed)
            <button type="button" class="btn btn-sm" data-red-embed="{{ $facebookEmbed }}" data-red-titulo="Facebook de la cátedra" data-red-enlace="{{ $facebookUrl }}" data-red-nombre="Facebook">Últimas publicaciones</button>
          @endif
          <a href="{{ $facebookUrl }}" target="_blank" rel="noopener">Abrir en Facebook ↗</a>
        </div>
      @endif

      @if ($linkedinUrl)
        <div class="red-mini">
          <div class="red-head">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M5.3 8.6h3.1V19H5.3V8.6zM6.9 3.8a1.8 1.8 0 110 3.6 1.8 1.8 0 010-3.6zM10.4 8.6h3v1.4c.4-.8 1.5-1.7 3.1-1.7 3.3 0 3.9 2.2 3.9 5V19h-3.1v-5.1c0-1.2 0-2.8-1.7-2.8s-2 1.3-2 2.7V19h-3.1V8.6z"/></svg>
            <strong>LinkedIn</strong>
          </div>
          @if ($linkedinEmbed)
            <button type="button" class="btn btn-sm" data-red-embed="{{ $linkedinEmbed }}" data-red-titulo="LinkedIn de la cátedra" data-red-enlace="{{ $linkedinUrl }}" data-red-nombre="LinkedIn">Última publicación</button>
          @endif
          <a href="{{ $linkedinUrl }}" target="_blank" rel="noopener">Abrir en LinkedIn ↗</a>
        </div>
      @endif
    </div>

    @php
        $conEmbed = array_keys(array_filter(['Facebook' => $facebookEmbed, 'LinkedIn' => $linkedinUrl && $linkedinEmbed]));
    @endphp
    @if ($conEmbed)
      <p class="redes-aviso">Las publicaciones de {{ implode(' y ', $conEmbed) }} se cargan desde {{ count($conEmbed) > 1 ? 'sus webs' : 'su web' }} solo al abrirlas, y {{ count($conEmbed) > 1 ? 'esas webs pueden' : 'esa web puede' }} usar cookies.</p>
    @endif
  @endif
</aside>

@once
<dialog class="red-dialog" id="red-dialog" aria-labelledby="red-dialog-titulo">
  <div class="red-dialog-head">
    <h2 id="red-dialog-titulo"></h2>
    <button type="button" data-red-cerrar aria-label="Cerrar">×</button>
  </div>
  <div class="red-dialog-cuerpo"></div>
  <p class="red-dialog-pie">¿No ves nada? Puede que tu navegador bloquee el contenido de otras webs. <a href="#" target="_blank" rel="noopener" data-red-abrir></a></p>
</dialog>

<script>
  // Los iframes de terceros se crean al pulsar y se destruyen al cerrar el diálogo.
  (function () {
    const dialogo = document.getElementById('red-dialog');
    const cuerpo = dialogo.querySelector('.red-dialog-cuerpo');
    const titulo = document.getElementById('red-dialog-titulo');
    const abrir = dialogo.querySelector('[data-red-abrir]');

    document.querySelectorAll('[data-red-embed]').forEach(function (boton) {
      boton.addEventListener('click', function () {
        const iframe = document.createElement('iframe');
        iframe.src = boton.dataset.redEmbed;
        iframe.title = boton.dataset.redTitulo;
        iframe.allow = 'encrypted-media';
        titulo.textContent = boton.dataset.redTitulo;
        abrir.href = boton.dataset.redEnlace;
        abrir.textContent = 'Ábrelo en ' + boton.dataset.redNombre + ' ↗';
        cuerpo.replaceChildren(iframe);
        dialogo.showModal();
      });
    });

    dialogo.querySelector('[data-red-cerrar]').addEventListener('click', function () { dialogo.close(); });
    dialogo.addEventListener('click', function (e) { if (e.target === dialogo) dialogo.close(); });
    dialogo.addEventListener('close', function () { cuerpo.replaceChildren(); });
  })();
</script>
@endonce
