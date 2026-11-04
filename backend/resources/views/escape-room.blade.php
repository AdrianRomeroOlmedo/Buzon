{{--
    Escape room «Ceuta Educadora 2030: Misión Verificación IA» (Día Internacional de la Ciudad Educadora, 30 de noviembre).
    La página solo pone el marco: el juego lo pinta public/juegos/escape-room/escape-room.js con los textos de
    public/juegos/escape-room/config.json. Para cambiar retos, pistas o claves basta con editar ese JSON.
--}}
<x-catedra titulo="Escape room: Misión Verificación IA" descripcion="Escape room digital de la Cátedra Ceuta-IATEB para aprender a detectar la desinformación generada por inteligencia artificial. Gratuito, anónimo y sin registro.">
  @push('estilos')
  <style>
    .hero-er .hero-inner{padding-block:48px 52px;}
    .sr-only{position:absolute; width:1px; height:1px; overflow:hidden; clip:rect(0 0 0 0); white-space:nowrap;}

    /* Terminal */
    .er-terminal{
      background:var(--surface); border:1px solid var(--line); border-radius:16px;
      overflow:hidden; box-shadow:0 18px 40px -28px rgba(21,34,56,.45); scroll-margin-top:96px;
    }
    .er-barra{
      display:flex; align-items:center; gap:12px; padding:10px 18px;
      background:var(--primary-deep); color:#fff; font-size:.8rem; font-weight:600;
    }
    .er-luces{display:flex; gap:6px; flex:none;}
    .er-luces i{width:10px; height:10px; border-radius:50%; background:rgba(255,255,255,.35);}
    .er-luces i:first-child{background:var(--accent);}
    .er-barra-titulo{flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; letter-spacing:.02em;}
    .er-barra-progreso{flex:none; opacity:.85;}

    .er-pantalla{padding:28px 22px 32px;}
    @media (min-width:640px){ .er-pantalla{padding:36px 40px 40px;} }
    .er-pantalla h2{font-size:clamp(1.5rem, 3.4vw, 2.1rem); margin-bottom:14px; outline:none;}
    .er-pantalla h3{font-size:1.2rem; margin:32px 0 12px;}
    .er-pantalla p{line-height:1.65; color:var(--ink-soft);}
    .er-prompt{font-family:ui-monospace, "Cascadia Mono", Consolas, monospace; font-size:.85rem; color:var(--primary) !important; margin:0 0 10px;}
    .er-meta{font-size:.9rem; font-weight:600; color:var(--ink) !important;}
    .er-aviso{font-size:.85rem; border-top:1px solid var(--line); padding-top:16px; margin-top:28px;}
    .er-acciones{display:flex; flex-wrap:wrap; gap:12px; margin-top:20px;}
    .er-acciones .btn{cursor:pointer;}

    /* Progreso */
    .er-progreso{list-style:none; margin:0 0 28px; padding:0; display:grid; grid-template-columns:repeat(3, 1fr); gap:8px;}
    .er-progreso li{display:flex; align-items:center; gap:8px; font-size:.8rem; font-weight:600; color:var(--ink-faint);
      padding-bottom:10px; border-bottom:3px solid var(--line);}
    .er-progreso li.actual{color:var(--ink); border-color:var(--accent);}
    .er-progreso li.hecho{color:var(--primary); border-color:var(--primary);}
    .er-progreso-num{flex:none; width:24px; height:24px; border-radius:50%; display:grid; place-items:center;
      background:var(--surface-2); font-size:.75rem;}
    .er-progreso li.hecho .er-progreso-num{background:var(--primary); color:#fff;}
    .er-progreso li.actual .er-progreso-num{background:var(--accent); color:#fff;}
    @media (max-width:639px){ .er-progreso-txt{display:none;} }

    /* Enigma: documento + panel */
    .er-enigma{display:grid; grid-template-columns:1fr; gap:28px;}
    @media (min-width:900px){ .er-enigma{grid-template-columns:1.05fr 1fr; gap:40px; align-items:start;} }
    .er-expediente{font-family:ui-monospace, "Cascadia Mono", Consolas, monospace; font-size:.8rem; color:var(--accent-deep) !important; margin:0 0 6px; font-weight:700;}

    .er-doc{
      position:relative; overflow:hidden; border-radius:12px; padding:22px 22px 18px;
      background:var(--surface-2); border:1px solid var(--line);
    }
    .er-doc p{color:var(--ink); position:relative;}
    .er-marca{
      position:absolute; inset:0; display:grid; place-items:center; pointer-events:none;
      font-family:"Public Sans", sans-serif; font-weight:800; font-size:clamp(2.2rem, 7vw, 3.4rem); letter-spacing:.12em;
      color:var(--accent); opacity:.13; transform:rotate(-18deg);
    }
    .er-doc-cabecera{font-size:.75rem !important; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-faint) !important; margin:0 0 10px;}
    .er-doc-titular{font-size:1.25rem; line-height:1.3; margin-bottom:12px; position:relative;}
    .er-doc-pie{font-size:.82rem !important; color:var(--ink-faint) !important; border-top:1px dashed var(--line); padding-top:10px;}
    .er-doc-aviso{font-size:.75rem !important; font-weight:700; color:var(--accent-deep) !important; margin:8px 0 0;}
    .er-doc mark{background:color-mix(in srgb, var(--accent) 30%, transparent); color:inherit; border-radius:3px; padding:0 2px;}
    .er-doc-bando{border-top:6px solid var(--ink-faint);}
    .er-doc-bando .er-doc-titular{text-transform:uppercase; font-size:1.05rem; letter-spacing:.02em;}
    .er-doc-alerta{background:color-mix(in srgb, #D92D20 10%, var(--surface)); border-color:#D92D20;}
    .er-doc-alerta .er-doc-titular{color:#D92D20;}
    .er-alarma .er-doc-alerta{animation:er-latido 1.4s ease-in-out infinite;}
    @keyframes er-latido{50%{box-shadow:0 0 0 6px color-mix(in srgb, #D92D20 25%, transparent);}}
    @media (prefers-reduced-motion:reduce){ .er-alarma .er-doc-alerta{animation:none;} }

    .er-lateral{background:var(--surface); border:1px solid var(--line); border-left:4px solid var(--primary); border-radius:10px; padding:14px 18px; margin-bottom:22px;}
    .er-lateral .kicker{margin:0 0 4px;}
    .er-lateral p{margin:0;}
    .er-busquedas{list-style:none; padding:0; margin:12px 0 0; display:flex; flex-direction:column; gap:6px;}
    .er-busquedas a{font-size:.9rem; font-weight:600; color:var(--primary);}

    /* Pregunta con opciones */
    .er-pregunta{font-weight:700; color:var(--ink) !important; line-height:1.5; margin:0 0 12px;}
    .er-opciones{list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:10px;}
    .er-opcion{
      width:100%; text-align:left; font:inherit; font-size:.95rem; line-height:1.5; color:var(--ink); cursor:pointer;
      display:grid; grid-template-columns:auto 1fr; gap:12px; align-items:start;
      background:var(--surface); border:2px solid var(--line); border-radius:12px; padding:12px 14px;
      transition:border-color .15s ease, background .15s ease;
    }
    .er-opcion:hover:not(:disabled){border-color:var(--primary);}
    .er-opcion:focus-visible{outline:3px solid var(--focus); outline-offset:2px;}
    .er-opcion-letra{width:26px; height:26px; border-radius:50%; display:grid; place-items:center;
      background:var(--surface-2); font-weight:800; font-size:.8rem; color:var(--ink-soft);}
    .er-opcion:disabled{cursor:default; opacity:.7; border-style:dashed;}
    .er-opcion:disabled .er-opcion-letra{background:#D92D20; color:#fff;}
    .er-motivo{margin:8px 0 0 !important; font-size:.9rem; color:var(--ink-soft) !important; padding-left:38px;}
    .er-clave{display:inline-block; font-family:ui-monospace, "Cascadia Mono", Consolas, monospace; font-weight:700;
      letter-spacing:.08em; background:var(--primary-deep); color:#fff; border-radius:6px; padding:2px 10px; margin-left:4px;}

    .er-ok, .er-error{border-radius:10px; padding:12px 16px; font-size:.95rem; line-height:1.55; margin-bottom:16px;}
    .er-ok{background:color-mix(in srgb, #12B76A 14%, var(--surface)); border:1px solid #12B76A; color:var(--ink);}
    .er-error{background:color-mix(in srgb, #D92D20 12%, var(--surface)); border:1px solid #D92D20; color:var(--ink);}
    .er-sirena{display:inline-block; font-weight:800; font-size:.8rem; letter-spacing:.08em; text-transform:uppercase;
      color:#fff !important; background:#D92D20; border-radius:999px; padding:4px 12px; margin:0 0 6px;}

    /* Final */
    .er-informe{display:grid; grid-template-columns:repeat(2, 1fr); gap:12px; margin:24px 0 8px;}
    @media (min-width:760px){ .er-informe{grid-template-columns:repeat(4, 1fr);} }
    .er-informe div{background:var(--surface-2); border-radius:10px; padding:14px 16px;}
    .er-informe dt{font-size:.78rem; color:var(--ink-faint); font-weight:600;}
    .er-informe dd{margin:4px 0 0; font-family:"Fraunces", serif; font-size:1.6rem; font-weight:600; color:var(--ink);}
    .er-nota{font-size:.95rem;}
    .er-decalogo{margin:0; padding:0; list-style:none; counter-reset:regla; display:grid; gap:10px;}
    @media (min-width:760px){ .er-decalogo{grid-template-columns:repeat(2, 1fr);} }
    .er-decalogo li{counter-increment:regla; display:grid; grid-template-columns:auto 1fr; gap:2px 12px;
      background:var(--surface-2); border-radius:10px; padding:14px 16px;}
    .er-decalogo li::before{content:counter(regla); grid-row:span 2; width:28px; height:28px; border-radius:50%;
      display:grid; place-items:center; background:var(--primary); color:#fff; font-weight:700; font-size:.85rem;}
    .er-decalogo strong{color:var(--ink);}
    .er-decalogo span{color:var(--ink-soft); font-size:.92rem; line-height:1.55;}
    .er-reflexion{margin:0; padding-left:1.2rem; display:flex; flex-direction:column; gap:8px; color:var(--ink-soft); line-height:1.55;}
    .er-certificado-form{margin-top:32px; border-top:1px solid var(--line); padding-top:8px;}
    .er-certificado-form .field{max-width:420px;}

    /* Certificado. Se diseña en «em» sobre un A4 apaisado: en pantalla se escala con el ancho
       de la vista previa (1,212cqw = 3,6mm/297mm) y al imprimir mide exactamente una hoja. */
    .er-cert-vista{container-type:inline-size; max-width:680px; margin:16px 0 4px;
      border-radius:10px; overflow:hidden; box-shadow:0 18px 40px -24px rgba(0,0,0,.5);}
    .er-cert{
      font-size:1.212cqw; aspect-ratio:297 / 210; display:grid; grid-template-columns:31% minmax(0, 1fr);
      background:#fff; color:#152238; font-family:"Public Sans", sans-serif; line-height:1.4;
      -webkit-print-color-adjust:exact; print-color-adjust:exact;
    }
    .er-cert p{margin:0; color:inherit !important;}
    .er-cert-lado{background:#2B509E; color:#fff; padding:2.6em 2.2em; display:flex; flex-direction:column; justify-content:space-between;}
    .er-cert-lado img{width:100%; height:auto; border-radius:.4em;}
    .er-cert-dia{font-size:.95em; line-height:1.45; opacity:.92;}
    .er-cert-dia strong{display:block; font-family:"Fraunces", serif; font-size:2.6em; line-height:1.05; font-weight:600; margin-bottom:.25em;}
    .er-cert-cuerpo{position:relative; padding:3.4em 3.8em 2.8em; display:flex; flex-direction:column;}
    .er-cert-principal{margin-block:auto;}
    .er-cert-cuerpo::after{content:""; position:absolute; right:0; top:0; width:9em; height:.9em; background:#F26522;}
    .er-cert-tipo{font-size:.85em; font-weight:800; letter-spacing:.32em; text-transform:uppercase; color:#F26522 !important;}
    .er-cert h2{font-family:"Fraunces", serif; font-size:2.6em; line-height:1.1; font-weight:600; color:#152238; margin:.3em 0 1.2em;}
    .er-cert-otorga{font-size:1em; color:#4B5768 !important;}
    .er-cert-nombre{font-family:"Fraunces", serif; font-size:4em; line-height:1.15; font-weight:600; color:#2B509E !important;
      border-bottom:.06em solid #F26522; padding:.1em 0 .2em; margin-bottom:.45em !important; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;}
    .er-cert-nombre.largo{font-size:3em;}
    .er-cert-texto{font-size:1.2em; line-height:1.5; color:#4B5768 !important; max-width:34em;}
    .er-cert-texto em{font-style:normal; font-weight:700; color:#152238;}
    .er-cert-competencias{list-style:none; margin:1.6em 0 0; padding:0; display:flex; flex-wrap:wrap; gap:.6em;}
    .er-cert-competencias li{display:flex; align-items:center; gap:.5em; font-size:1em; font-weight:700;
      background:#EEF1F8; border-radius:99em; padding:.45em 1em .45em .5em;}
    .er-cert-competencias li::before{content:"✓"; width:1.6em; height:1.6em; border-radius:50%; display:grid; place-items:center;
      background:#2B509E; color:#fff; font-size:.85em;}
    .er-cert-pie{margin-top:auto; display:flex; align-items:flex-end; justify-content:space-between; gap:2em;
      border-top:1px solid #D6DBE6; padding-top:1.2em;}
    .er-cert-pie p{font-size:.95em; font-weight:600;}
    .er-cert-pie span{display:block; font-size:.78em; font-weight:700; letter-spacing:.14em; text-transform:uppercase; color:#7C879A; margin-bottom:.2em;}
    .er-cert-sello{flex:none; width:7em; height:7em; border-radius:50%; border:.16em solid #2B509E; outline:.08em solid #2B509E; outline-offset:.25em;
      display:grid; place-items:center; text-align:center; font-size:.78em; font-weight:800; line-height:1.2; letter-spacing:.04em; color:#2B509E;}

    #er-certificado-impresion{display:none;}
    @media print{
      @page{size:A4 landscape; margin:0;}
      html, body{margin:0 !important; padding:0 !important; background:#fff !important;}
      body > *:not(#er-certificado-impresion){display:none !important;}
      #er-certificado-impresion{display:block !important;}
      #er-certificado-impresion .er-cert{width:297mm; height:209mm; font-size:3.6mm; aspect-ratio:auto; overflow:hidden; break-inside:avoid;}
    }
  </style>
  @endpush

  <section class="hero hero-er">
    <div class="wrap">
      <div class="hero-inner">
        <span class="eyebrow">30 de noviembre · Día Internacional de la Ciudad Educadora</span>
        <h1>Misión Verificación IA</h1>
        <p class="lead">Un escape room digital para aprender a detectar alucinaciones, sesgos y desmentidos falsos generados por inteligencia artificial. Gratuito, anónimo y sin registro.</p>
      </div>
    </div>
  </section>

  <section>
    <div class="wrap">
      <div id="escape-room" class="er-terminal" data-config="{{ asset('juegos/escape-room/config.json') }}" data-logo="{{ asset('catedra-ceuta-iateb.webp') }}">
        <noscript><p class="er-pantalla">Para jugar necesitas activar JavaScript en tu navegador.</p></noscript>
      </div>
    </div>
  </section>

  <script src="{{ asset('juegos/escape-room/escape-room.js') }}?v={{ config('app.version_estilos') }}" defer></script>
</x-catedra>
