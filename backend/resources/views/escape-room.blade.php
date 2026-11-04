{{--
    Escape room «Ceuta Educadora 2030: Misión Verificación IA» (Día Internacional de la Ciudad Educadora, 30 de noviembre).
    La página solo pone el marco: el juego lo pinta public/juegos/escape-room/escape-room.js con los textos de
    public/juegos/escape-room/config.json. Para cambiar el suceso, las fases o las noticias basta con editar ese JSON.
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
    .er-progreso{list-style:none; margin:0 0 28px; padding:0; display:grid; grid-template-columns:repeat(var(--fases, 4), 1fr); gap:8px;}
    .er-progreso li{display:flex; align-items:center; gap:8px; font-size:.8rem; font-weight:600; color:var(--ink-faint);
      padding-bottom:10px; border-bottom:3px solid var(--line);}
    .er-progreso li.actual{color:var(--ink); border-color:var(--accent);}
    .er-progreso li.hecho{color:var(--primary); border-color:var(--primary);}
    .er-progreso-num{flex:none; width:24px; height:24px; border-radius:50%; display:grid; place-items:center;
      background:var(--surface-2); font-size:.75rem;}
    .er-progreso li.hecho .er-progreso-num{background:var(--primary); color:#fff;}
    .er-progreso li.actual .er-progreso-num{background:var(--accent); color:#fff;}
    @media (max-width:639px){ .er-progreso-txt{display:none;} }

    /* Fase: tres informaciones para elegir */
    .er-expediente{font-family:ui-monospace, "Cascadia Mono", Consolas, monospace; font-size:.8rem; color:var(--accent-deep) !important; margin:0 0 6px; font-weight:700;}
    .er-instruccion{font-size:.95rem; margin:0 0 22px;}
    .er-noticias{display:grid; grid-template-columns:1fr; gap:16px;}
    @media (min-width:900px){ .er-noticias{grid-template-columns:repeat(3, minmax(0, 1fr));} }
    .er-noticia{
      display:flex; flex-direction:column; gap:10px; padding:16px; cursor:pointer;
      background:var(--surface); border:2px solid var(--line); border-radius:14px;
      transition:border-color .15s ease, box-shadow .15s ease;
    }
    .er-noticia:hover{border-color:var(--primary);}
    .er-noticia.marcada{border-color:var(--accent); box-shadow:0 0 0 4px color-mix(in srgb, var(--accent) 22%, transparent);}
    .er-noticia p{margin:0; font-size:.93rem; line-height:1.55;}
    .er-noticia .er-titular{font-family:"Fraunces", serif; font-weight:600; font-size:1.08rem; line-height:1.3; color:var(--ink);}
    .er-hora{margin-left:auto; font-size:.75rem; font-weight:600; color:var(--ink-faint);}
    .er-medio{display:flex; gap:8px; font-size:.72rem !important; font-weight:700; letter-spacing:.08em; text-transform:uppercase;
      color:var(--ink-faint) !important; border-bottom:1px solid var(--line); padding-bottom:8px;}
    .er-post-cabecera{display:flex; align-items:center; gap:10px; font-size:.85rem;}
    .er-post-cabecera strong{display:block; color:var(--ink);}
    .er-post-cabecera small{display:block; font-size:.75rem; color:var(--ink-faint);}
    .er-avatar{flex:none; width:34px; height:34px; border-radius:50%; display:grid; place-items:center;
      background:var(--primary); color:#fff; font-weight:800;}
    .er-noticia-redes .er-titular, .er-noticia-whatsapp .er-titular{font-family:"Public Sans", sans-serif; font-weight:700; font-size:1rem;}
    .er-imagen{aspect-ratio:16 / 9; border-radius:10px; display:grid; place-items:center; text-align:center; padding:12px;
      background:repeating-linear-gradient(135deg, var(--surface-2) 0 12px, var(--line) 12px 13px);
      font-size:.8rem; font-weight:600; color:var(--ink-faint);}
    .er-noticia-whatsapp{background:color-mix(in srgb, #25D366 5%, var(--surface));}
    .er-reenviado{font-size:.8rem !important; font-style:italic; color:var(--ink-faint) !important;}
    .er-burbuja{display:flex; flex-direction:column; gap:8px; padding:12px 14px; border-radius:4px 14px 14px 14px;
      background:color-mix(in srgb, #25D366 16%, var(--surface));}
    .er-burbuja p{color:var(--ink);}
    .er-burbuja .er-hora{align-self:flex-end;}
    .er-elegir{margin-top:auto; font:inherit; font-size:.88rem; font-weight:700; cursor:pointer;
      padding:10px 14px; border-radius:999px; border:2px solid var(--line); background:transparent; color:var(--ink);}
    .er-elegir:focus-visible{outline:3px solid var(--focus); outline-offset:2px;}
    .er-noticia.marcada .er-elegir{background:var(--accent); border-color:var(--accent); color:#fff;}
    .er-acciones .btn:disabled{opacity:.45; cursor:not-allowed; transform:none;}
    .er-error{border-radius:10px; padding:12px 16px; margin:24px; background:color-mix(in srgb, #D92D20 12%, var(--surface)); border:1px solid #D92D20; color:var(--ink);}

    /* Final: las dos narrativas */
    .er-narrativas{display:grid; grid-template-columns:1fr; gap:16px; margin:28px 0 8px;}
    @media (min-width:760px){ .er-narrativas{grid-template-columns:1fr 1fr;} }
    .er-narrativa{border:1px solid var(--line); border-radius:14px; padding:18px 20px; background:var(--surface-2);}
    .er-narrativa-real{background:color-mix(in srgb, #12B76A 9%, var(--surface)); border-color:color-mix(in srgb, #12B76A 45%, transparent);}
    .er-pantalla .er-narrativa h3{margin:0 0 14px; font-size:1.1rem;}
    .er-narrativa ol{list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:14px;}
    .er-narrativa li{display:grid; grid-template-columns:1fr auto; gap:2px 10px; line-height:1.5; color:var(--ink);
      padding-left:12px; border-left:3px solid #12B76A;}
    .er-narrativa li.bulo{border-left-color:#D92D20;}
    .er-momento{grid-column:1 / -1; font-size:.72rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-faint);}
    .er-etiqueta{align-self:start; white-space:nowrap; font-size:.72rem; font-weight:800; border-radius:999px; padding:2px 8px; color:#fff; background:#12B76A;}
    .bulo .er-etiqueta{background:#D92D20;}
    .er-etiqueta.er-tu-eleccion{background:var(--primary);}

    .er-desglose{display:flex; flex-direction:column; gap:10px;}
    .er-desglose details{border:1px solid var(--line); border-radius:12px; background:var(--surface);}
    .er-desglose summary{cursor:pointer; padding:14px 16px; font-weight:700; color:var(--ink); line-height:1.4;}
    .er-desglose summary span{float:right; margin-left:12px; font-size:.8rem;}
    .er-desglose details.acierto summary span{color:#12B76A;}
    .er-desglose details.fallo summary span{color:#D92D20;}
    .er-desglose ul{list-style:none; margin:0; padding:0 16px 16px; display:flex; flex-direction:column; gap:14px;}
    .er-desglose li{padding-left:12px; border-left:3px solid #12B76A;}
    .er-desglose li.bulo{border-left-color:#D92D20;}
    .er-desglose li p{margin:0; font-size:.92rem;}
    .er-pantalla .er-desglose-titular{display:flex; flex-wrap:wrap; align-items:center; gap:6px; margin-bottom:4px; font-weight:700; color:var(--ink);}

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
        <p class="lead">Un suceso, varias fases y en cada una tres informaciones: solo una es verdadera. ¿Sabrás distinguir los bulos de lo que pasó de verdad? Gratuito, anónimo y sin registro.</p>
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
