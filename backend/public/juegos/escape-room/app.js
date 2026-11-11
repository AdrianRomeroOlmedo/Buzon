/* =============================================================================
   LÓGICA DEL JUEGO · Operación Verdad
   No hace falta tocar este archivo para cambiar textos: todo el contenido está
   en content.js. Aquí está el funcionamiento de la cabina y de las pruebas.

   · #juego (pantalla principal): arranque, transmisión, los cuatro módulos,
     la activación y el final.
   · Panel de sistemas: los cuatro módulos con su estado, el reactor, el código
     de activación y los mandos (sonido, volumen, texto grande, reiniciar).
   · Canal de comunicación: Vero, el extraterrestre (alienSVG), y lo que dice.
   · Sonido: sintetizado con Web Audio (sin archivos), apagado por defecto.
   ========================================================================== */
(function () {
  'use strict';

  const C = window.CONTENIDO;
  const juego = document.getElementById('juego');
  const anuncio = document.getElementById('anuncio');
  const holograma = document.getElementById('holograma');
  const mensaje = document.getElementById('mensaje');

  if (!C) {
    juego.innerHTML = '<p class="aviso-carga">No se ha podido leer <strong>content.js</strong>. ' +
      'Revisa que no falte ninguna coma, comilla o llave en el último cambio.</p>';
    return;
  }

  const CLAVE_PROGRESO = 'operacion-verdad-progreso';
  const CLAVE_PREFERENCIAS = 'operacion-verdad-preferencias';
  const MOVIMIENTO_REDUCIDO = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Los cuatro módulos en orden; «pantalla» es el nombre interno de cada uno.
  const PRUEBAS = [
    { pantalla: 'p1', datos: C.pruebas.detectar },
    { pantalla: 'p2', datos: C.pruebas.verificar },
    { pantalla: 'p3', datos: C.pruebas.clasificar },
    { pantalla: 'p4', datos: C.pruebas.cuestionario },
  ];
  const CODIGO = PRUEBAS.map((p) => p.datos.fragmento).join('');

  // ---------------------------------------------------------------------------
  // Estado: lo que se guarda (estado) y lo que solo dura mientras se ve la
  // pantalla (temporal: la respuesta mostrada, la pista abierta…).
  // ---------------------------------------------------------------------------
  function estadoInicial() {
    return {
      pantalla: 'portada', paso: 0, logro: null, superadas: [],
      conexiones: {}, conexionesCorrectas: [], ordenEjemplos: null, ordenTipos: null,
      respuestasTest: {}, testCorrectas: [],
      fallos: 0, pistas: 0, inicio: null, fin: null, finTiempo: null,
    };
  }
  function temporalInicial() {
    return {
      respuesta: null, pista: false, reaccion: null, aviso: null,
      seleccion: null, erroneas: [], resultadoTest: {}, errorCodigo: false, secuencia: false,
    };
  }

  let estado = leer(CLAVE_PROGRESO, (guardado) => guardado.version === C.version ? Object.assign(estadoInicial(), guardado.estado) : null) || estadoInicial();
  let temporal = temporalInicial();
  const preferencias = Object.assign(
    { sonido: false, volumen: typeof C.ajustes.volumen === 'number' ? C.ajustes.volumen : 0.5, textoGrande: false },
    leer(CLAVE_PREFERENCIAS, (p) => p) || {}
  );

  function leer(clave, transformar) {
    try {
      const valor = JSON.parse(localStorage.getItem(clave));
      return valor ? transformar(valor) : null;
    } catch (e) { return null; } // sin almacenamiento: se empieza de cero
  }
  function guardar() {
    try { localStorage.setItem(CLAVE_PROGRESO, JSON.stringify({ version: C.version, estado })); } catch (e) { /* modo privado */ }
  }
  function guardarPreferencias() {
    try { localStorage.setItem(CLAVE_PREFERENCIAS, JSON.stringify(preferencias)); } catch (e) { /* modo privado */ }
  }

  // ---------------------------------------------------------------------------
  // Utilidades
  // ---------------------------------------------------------------------------
  function el(etiqueta, atributos, ...hijos) {
    const nodo = document.createElement(etiqueta);
    for (const [nombre, valor] of Object.entries(atributos || {})) {
      if (valor === false || valor === null || valor === undefined) continue;
      if (nombre === 'class') nodo.className = valor;
      else if (nombre.startsWith('on')) nodo.addEventListener(nombre.slice(2), valor);
      else nodo.setAttribute(nombre, valor === true ? '' : valor);
    }
    for (const hijo of hijos.flat()) {
      if (hijo === null || hijo === undefined || hijo === false) continue;
      nodo.append(hijo instanceof Node ? hijo : document.createTextNode(String(hijo)));
    }
    return nodo;
  }

  function svg(html) {
    const caja = document.createElement('span');
    caja.style.display = 'contents';
    caja.innerHTML = html;
    return caja.firstElementChild;
  }

  function escapar(texto) {
    return String(texto).replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
  }

  function azar(lista) {
    return Array.isArray(lista) ? lista[Math.floor(Math.random() * lista.length)] : lista;
  }

  function barajar(cantidad) {
    const orden = Array.from({ length: cantidad }, (_, i) => i);
    for (let i = orden.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [orden[i], orden[j]] = [orden[j], orden[i]];
    }
    return orden;
  }

  function anunciar(texto) {
    anuncio.textContent = '';
    setTimeout(() => { anuncio.textContent = texto; }, 50);
  }

  function normalizar(texto) {
    return String(texto).normalize('NFD').replace(/[̀-ͯ]/g, '').toUpperCase().replace(/[^A-Z0-9]/g, '');
  }

  // Iconos de línea (sin emojis). Se usan dentro de botones y publicaciones.
  const ICONOS = {
    ok: '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
    no: '<path d="M6 6l12 12M18 6L6 18"/>',
    pista: '<path d="M9 18h6M10 21h4M12 3a6 6 0 0 0-3.5 10.9c.6.5 1 1.2 1 2.1h5c0-.9.4-1.6 1-2.1A6 6 0 0 0 12 3z"/>',
    siguiente: '<path d="M5 12h14M13 6l6 6-6 6"/>',
    repetir: '<path d="M4 12a8 8 0 1 0 2.4-5.7M4 4v4.5h4.5"/>',
    reenviado: '<path d="M14 5l6 6-6 6M20 11H9a5 5 0 0 0-5 5v2"/>',
    responder: '<path d="M21 11.5a8.4 8.4 0 0 1-12.5 7.3L3 20l1.3-4.9A8.4 8.4 0 1 1 21 11.5z"/>',
    compartir: '<path d="M7 7h11l-3-3M17 17H6l3 3M18 7v5M6 17v-5"/>',
    megusta: '<path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z"/>',
    dobleCheck: '<path d="M2 12l4 4 8-9M10 15l1 1 8-9"/>',
    llave: '<path d="M14.5 9.5a4 4 0 1 0-3.6 3.98L9 15.5V18H6.5v2.5H3.5v-3l7-7"/><circle cx="15.5" cy="8.5" r="1"/>',
  };
  function icono(nombre, clase) {
    return svg(`<svg class="${clase || 'icono'}" viewBox="0 0 24 24" aria-hidden="true">${ICONOS[nombre]}</svg>`);
  }

  // ---------------------------------------------------------------------------
  // Sonido: ambiente de la nave y efectos, sintetizados con Web Audio.
  // ---------------------------------------------------------------------------
  const Sonido = (function () {
    let ctx = null;
    let maestro = null;
    let ambiente = null;
    let activo = false;

    function preparar() {
      if (ctx) return true;
      const Contexto = window.AudioContext || window.webkitAudioContext;
      if (!Contexto) return false;
      ctx = new Contexto();
      maestro = ctx.createGain();
      maestro.gain.value = preferencias.volumen;
      maestro.connect(ctx.destination);
      return true;
    }

    // Zumbido grave de la nave más un rumor filtrado de ventilación.
    function crearAmbiente() {
      const salida = ctx.createGain();
      salida.gain.value = 0;
      const graves = ctx.createGain();
      graves.gain.value = 0.4;
      const o1 = ctx.createOscillator();
      o1.type = 'sine';
      o1.frequency.value = 52;
      const o2 = ctx.createOscillator();
      o2.type = 'triangle';
      o2.frequency.value = 78;
      o2.detune.value = 7;
      o1.connect(graves);
      o2.connect(graves);
      graves.connect(salida);
      const datos = ctx.createBuffer(1, ctx.sampleRate * 2, ctx.sampleRate);
      const canal = datos.getChannelData(0);
      for (let i = 0; i < canal.length; i++) canal[i] = Math.random() * 2 - 1;
      const ruido = ctx.createBufferSource();
      ruido.buffer = datos;
      ruido.loop = true;
      const filtro = ctx.createBiquadFilter();
      filtro.type = 'lowpass';
      filtro.frequency.value = 320;
      const nivelRuido = ctx.createGain();
      nivelRuido.gain.value = 0.18;
      ruido.connect(filtro);
      filtro.connect(nivelRuido);
      nivelRuido.connect(salida);
      salida.connect(maestro);
      o1.start();
      o2.start();
      ruido.start();
      return { salida, o1, o2, filtro };
    }

    function tono(frecuencia, duracion, tipo, volumen, retraso, hasta) {
      if (!activo || !ctx) return;
      const t = ctx.currentTime + (retraso || 0);
      const o = ctx.createOscillator();
      const g = ctx.createGain();
      o.type = tipo || 'sine';
      o.frequency.setValueAtTime(frecuencia, t);
      if (hasta) o.frequency.exponentialRampToValueAtTime(hasta, t + duracion);
      g.gain.setValueAtTime(0.0001, t);
      g.gain.exponentialRampToValueAtTime(volumen || 0.15, t + 0.015);
      g.gain.exponentialRampToValueAtTime(0.0001, t + duracion);
      o.connect(g);
      g.connect(maestro);
      o.start(t);
      o.stop(t + duracion + 0.05);
    }

    return {
      activar(si) {
        activo = si;
        if (!si) {
          if (ambiente) ambiente.salida.gain.setTargetAtTime(0, ctx.currentTime, 0.15);
          return;
        }
        if (!preparar()) return;
        ctx.resume();
        if (!ambiente) ambiente = crearAmbiente();
        ambiente.salida.gain.setTargetAtTime(0.14, ctx.currentTime, 0.6);
        this.energia(estado.superadas.length);
      },
      volumen(valor) {
        if (maestro) maestro.gain.setTargetAtTime(valor, ctx.currentTime, 0.05);
      },
      // El zumbido sube de tono a medida que la nave recupera energía.
      energia(nivel) {
        if (!ambiente) return;
        ambiente.o1.frequency.setTargetAtTime(52 + nivel * 7, ctx.currentTime, 1.2);
        ambiente.o2.frequency.setTargetAtTime(78 + nivel * 10, ctx.currentTime, 1.2);
        ambiente.filtro.frequency.setTargetAtTime(320 + nivel * 120, ctx.currentTime, 1.2);
      },
      clic() { tono(1400, 0.04, 'square', 0.03); },
      acierto() { tono(660, 0.14, 'sine', 0.16); tono(990, 0.22, 'sine', 0.14, 0.11); },
      fallo() { tono(150, 0.35, 'sawtooth', 0.09, 0, 95); },
      desbloqueo() { [523, 659, 784, 1047].forEach((f, i) => tono(f, 0.28, 'triangle', 0.12, i * 0.09)); },
      paso() { tono(880, 0.08, 'square', 0.04); },
      arranque() { tono(55, 3.2, 'sawtooth', 0.16, 0, 440); tono(110, 3, 'sine', 0.14, 0.3, 880); },
    };
  })();

  // ---------------------------------------------------------------------------
  // Vero en el canal de comunicación: un alienígena de cráneo alargado, ojos
  // negros rasgados y manchas bioluminiscentes, con cuatro expresiones
  // (neutral, contento, preocupado, sorprendido).
  // ---------------------------------------------------------------------------
  const EXPRESIONES = { neutral: 'atento', contento: 'satisfecho', preocupado: 'preocupado', sorprendido: 'sorprendido' };

  function alienSVG(expresion) {
    const sombra = '#33503f';
    const linea = (d, ancho) => `<path d="${d}" stroke="${sombra}" stroke-width="${ancho || 2.5}" fill="none" stroke-linecap="round"/>`;
    // Ojo izquierdo; el derecho es su reflejo respecto al centro (x = 80).
    const ojo = (escala) => {
      const t = escala ? `transform="translate(51 78) scale(${escala}) translate(-51 -78)"` : '';
      return `<g ${t}>` +
        '<path d="M30 80 C34 60 58 52 72 70 C74 86 58 100 42 96 C34 94 29 88 30 80Z" fill="#04070a"/>' +
        '<ellipse cx="51" cy="80" rx="13" ry="9" fill="url(#iris-vero)" transform="rotate(-24 51 80)"/>' +
        '<ellipse cx="44" cy="71" rx="4.5" ry="2.6" fill="#fff" opacity=".8" transform="rotate(-24 44 71)"/>' +
        '<circle cx="59" cy="86" r="1.6" fill="#fff" opacity=".55"/></g>';
    };
    const ojos = (escala) => ojo(escala) + `<g transform="translate(160 0) scale(-1 1)">${ojo(escala)}</g>`;
    const espejo = (contenido) => contenido + `<g transform="translate(160 0) scale(-1 1)">${contenido}</g>`;
    const partes = {
      neutral: ojos() + linea('M68 127 Q80 130 92 127'),
      contento: ojos() +
        espejo('<path d="M27 93 Q50 74 75 87 L75 106 L27 106Z" fill="url(#piel-vero)"/>') +
        linea('M64 124 Q80 136 96 124', 3),
      preocupado: ojos() +
        espejo('<path d="M24 56 L62 56 Q42 68 24 82Z" fill="url(#piel-vero)"/>') +
        espejo(linea('M28 62 Q46 52 70 48', 4)) +
        linea('M66 131 Q80 122 94 131', 3),
      sorprendido: ojos(1.12) +
        espejo(linea('M30 54 Q50 38 72 50', 4)) +
        '<ellipse cx="80" cy="128" rx="5" ry="7" fill="#1c2e26"/>',
    };
    const etiqueta = `${escapar(C.alien.nombre)}, el extraterrestre, en el canal de comunicación; parece ${EXPRESIONES[expresion] || EXPRESIONES.neutral}`;
    return `<svg viewBox="0 0 160 190" role="img" aria-label="${etiqueta}">` +
      '<defs>' +
      '<radialGradient id="piel-vero" cx="45%" cy="32%" r="75%"><stop offset="0" stop-color="#b3d4c1"/><stop offset=".55" stop-color="#6e9a84"/><stop offset="1" stop-color="#365848"/></radialGradient>' +
      '<radialGradient id="iris-vero" cx="40%" cy="40%" r="60%"><stop offset="0" stop-color="#1a7f76"/><stop offset="1" stop-color="#04070a" stop-opacity="0"/></radialGradient>' +
      '</defs>' +
      // Traje y cuello
      '<path d="M18 190 C24 172 48 165 80 165 C112 165 136 172 142 190Z" fill="#161e2c" stroke="#78c8e6" stroke-width="1.2"/>' +
      '<path d="M54 168 Q80 181 106 168" stroke="#78c8e6" stroke-width="2.4" fill="none"/>' +
      '<circle cx="80" cy="182" r="3.5" fill="#e9a23b"/>' +
      '<path d="M64 138 C64 154 62 164 58 172 L102 172 C98 164 96 154 96 138Z" fill="#577f6b"/>' +
      linea('M67 154 L74 157 M67 161 L75 164 M93 154 L86 157 M93 161 L85 164', 2) +
      // Cabeza: cráneo alto y barbilla estrecha
      '<path d="M80 4 C124 4 146 38 144 74 C142 106 120 128 100 140 C92 145 86 150 80 150 C74 150 68 145 60 140 C40 128 18 106 16 74 C14 38 36 4 80 4Z" fill="url(#piel-vero)"/>' +
      `<g opacity=".55">${linea('M52 18 Q80 6 108 18')}${linea('M46 30 Q80 16 114 30')}${linea('M80 8 V44', 2)}</g>` +
      // Manchas bioluminiscentes
      '<g fill="#7fe3c7">' +
      '<circle cx="80" cy="52" r="3"/><circle cx="26" cy="62" r="2.6"/><circle cx="22" cy="74" r="2"/><circle cx="25" cy="86" r="1.6"/>' +
      '<circle cx="134" cy="62" r="2.6"/><circle cx="138" cy="74" r="2"/><circle cx="135" cy="86" r="1.6"/></g>' +
      // Nariz: dos rendijas
      linea('M76 107 Q74.5 112 77 116 M84 107 Q85.5 112 83 116', 2) +
      (partes[expresion] || partes.neutral) +
      '</svg>';
  }

  // Cada pantalla dice qué cuenta Vero. Si hay una reacción pendiente
  // (acierto, fallo, pista), se muestra esa en su lugar.
  let canal = { expresion: 'neutral', texto: '' };
  let canalPintado = null;
  function comunicar(expresion, texto) {
    canal = { expresion, texto };
  }
  function pintarCanal() {
    const r = temporal.reaccion || canal;
    const clave = r.expresion + '|' + r.texto;
    if (clave === canalPintado) return; // no repetir el aviso al lector de pantalla
    canalPintado = clave;
    holograma.innerHTML = alienSVG(r.expresion);
    mensaje.replaceChildren(el('span', { class: 'mensaje-autor' }, C.alien.nombre), r.texto);
  }

  // ---------------------------------------------------------------------------
  // Publicaciones interceptadas: cada formato con su aspecto real. Se montan
  // con <span> para poder ir dentro de un botón (prueba 1).
  // ---------------------------------------------------------------------------
  function publicacion(p) {
    const etiqueta = el('span', { class: 'simulacion' }, 'Simulación educativa');
    if (p.formato === 'red') {
      return el('span', { class: 'publicacion pub-red' }, etiqueta,
        el('span', { class: 'cabecera' },
          el('span', { class: 'avatar', 'aria-hidden': 'true' }, (p.autor || '?').charAt(0)),
          el('span', {}, el('span', { class: 'autor' }, p.autor), el('span', { class: 'usuario' }, p.usuario))
        ),
        el('span', { class: 'texto' }, p.texto),
        p.imagen ? el('span', { class: 'imagen', role: 'img', 'aria-label': p.imagen }, p.imagen) : null,
        el('span', { class: 'metricas' },
          el('span', {}, icono('responder', 'icono-metrica'), 'Responder'),
          el('span', {}, icono('compartir', 'icono-metrica'), 'Compartir'),
          el('span', {}, icono('megusta', 'icono-metrica'), p.metricas || '')
        )
      );
    }
    if (p.formato === 'whatsapp') {
      return el('span', { class: 'publicacion pub-whatsapp' }, etiqueta,
        el('span', { class: 'chat-cabecera' }, el('span', { class: 'avatar', 'aria-hidden': 'true' }, 'G'), p.chat || 'Grupo de clase'),
        el('span', { class: 'burbuja' },
          p.reenviado ? el('span', { class: 'reenviado' }, icono('reenviado', 'icono-metrica'), p.reenviado) : null,
          el('span', { class: 'texto' }, p.texto),
          el('span', { class: 'meta' }, p.hora || '', icono('dobleCheck', 'icono-metrica'))
        )
      );
    }
    if (p.formato === 'sms') {
      // El dominio del enlace se marca como enlace, igual que en un móvil.
      const partes = String(p.texto).split(/(\b[\w-]+(?:\.[\w-]+)+\.(?:top|com|net|info|xyz|es|org)\b)/);
      return el('span', { class: 'publicacion pub-sms' }, etiqueta,
        el('span', { class: 'sms-cabecera' }, el('span', { class: 'avatar', 'aria-hidden': 'true' }, (p.remitente || '?').charAt(0)), p.remitente),
        el('span', { class: 'hora' }, `Mensaje de texto · Hoy ${p.hora || ''}`),
        el('span', { class: 'burbuja' }, partes.map((t, i) => (i % 2 ? el('span', { class: 'enlace' }, t) : t)))
      );
    }
    return el('span', { class: 'publicacion pub-noticia' }, etiqueta,
      el('span', { class: 'mancheta' }, p.medio || 'Medio digital'),
      el('span', { class: 'cuerpo' },
        p.seccion ? el('span', { class: 'seccion' }, p.seccion) : null,
        el('span', { class: 'titular' }, p.titular),
        p.entradilla ? el('span', { class: 'entradilla' }, p.entradilla) : null,
        p.firma ? el('span', { class: 'firma' }, p.firma) : null
      )
    );
  }

  // ---------------------------------------------------------------------------
  // Piezas comunes de la pantalla principal
  // ---------------------------------------------------------------------------
  function cabeceraModulo(indice, derecha) {
    return el('div', { class: 'cabecera-modulo' },
      el('p', {}, `Módulo ${indice + 1} de ${PRUEBAS.length}: ${PRUEBAS[indice].datos.nombre}`),
      derecha ? el('p', { class: 'progreso-ronda' }, derecha) : null
    );
  }

  function titulo(texto) {
    return el('h2', { class: 'titulo-pantalla', tabindex: '-1', 'data-enfocar': '' }, texto);
  }

  function listaFuentes(fuentes) {
    if (!fuentes || !fuentes.length) return null;
    return el('div', { class: 'fuentes' },
      el('span', {}, fuentes.length > 1 ? 'Fuentes:' : 'Fuente:'),
      el('ul', {}, fuentes.map((f) => el('li', {},
        el('a', { href: f.url, target: '_blank', rel: 'noopener noreferrer' }, f.nombre, el('span', { class: 'solo-lector' }, ' (se abre en una pestaña nueva)'))
      )))
    );
  }

  function botonPista(texto) {
    if (!texto) return null;
    if (temporal.pista) {
      return el('p', { class: 'pista-caja', tabindex: '-1', 'data-foco': 'pista-texto' }, el('strong', {}, 'Pista: '), texto);
    }
    return el('button', {
      type: 'button', class: 'boton boton-pista', 'data-foco': 'pista',
      onclick: () => {
        temporal.pista = true;
        temporal.reaccion = { expresion: 'sorprendido', texto: C.alien.alPedirPista };
        estado.pistas++;
        Sonido.clic();
        guardar();
        pintar();
        enfocar('[data-foco="pista-texto"]');
      },
    }, icono('pista'), 'Pedir una pista');
  }

  // Explicación tras responder: siempre explica, se acierte o no.
  function bloqueRespuesta(acierto, explicacion, fuentes, textoSiguiente, alSiguiente) {
    return el('div', { class: 'respuesta ' + (acierto ? 'acierto' : 'fallo'), tabindex: '-1' },
      el('p', { class: 'respuesta-titulo' }, icono(acierto ? 'ok' : 'no'), acierto ? 'Correcto' : 'Incorrecto'),
      el('p', {}, explicacion),
      listaFuentes(fuentes),
      el('div', { class: 'acciones' },
        acierto
          ? el('button', { type: 'button', class: 'boton', 'data-foco': 'seguir', onclick: alSiguiente }, textoSiguiente, icono('siguiente'))
          : el('button', { type: 'button', class: 'boton', 'data-foco': 'reintentar', onclick: reintentar }, icono('repetir'), 'Volver a intentarlo')
      )
    );
  }

  function reaccionar(acierto) {
    temporal.reaccion = acierto
      ? { expresion: 'contento', texto: azar(C.alien.alAcertar) }
      : { expresion: 'preocupado', texto: azar(C.alien.alFallar) };
    if (acierto) Sonido.acierto(); else Sonido.fallo();
  }

  function responder(acierto, datos) {
    temporal.respuesta = Object.assign({ acierto }, datos);
    reaccionar(acierto);
    if (!acierto) estado.fallos++;
    guardar();
    pintar();
    enfocar('[data-foco="seguir"], [data-foco="reintentar"]');
  }

  function reintentar() {
    temporal = temporalInicial();
    Sonido.clic();
    pintar();
    const primero = juego.querySelector('.opcion-publicacion, .boton-veredicto');
    if (primero) primero.focus();
  }

  function siguienteRonda(total, indicePrueba) {
    if (estado.paso + 1 < total) {
      conmutar(() => { estado.paso++; temporal = temporalInicial(); guardar(); });
    } else {
      superarPrueba(indicePrueba);
    }
  }

  function superarPrueba(indice) {
    conmutar(() => {
      if (!estado.superadas.includes(indice)) estado.superadas.push(indice);
      estado.pantalla = 'logro';
      estado.logro = indice;
      estado.paso = 0;
      temporal = temporalInicial();
      guardar();
      moduloRecien = indice;
    });
    Sonido.desbloqueo();
    anunciar(`Módulo ${indice + 1} restaurado. Reactor al ${(estado.superadas.length + (estado.superadas.includes(indice) ? 0 : 1)) * 25} por ciento.`);
  }

  function ir(pantalla) {
    conmutar(() => {
      estado.pantalla = pantalla;
      estado.paso = 0;
      temporal = temporalInicial();
      guardar();
    });
  }

  // Cambio de pantalla con el efecto de monitor que se apaga y se enciende.
  function conmutar(cambio) {
    Sonido.clic();
    if (MOVIMIENTO_REDUCIDO) { cambio(); pintar(); return; }
    juego.classList.add('conmutando');
    setTimeout(() => {
      cambio();
      juego.classList.remove('conmutando');
      pintar();
    }, 170);
  }

  function textoSiguiente(paso, total) {
    return paso + 1 < total ? 'Siguiente señal' : 'Restaurar el módulo';
  }

  // ---------------------------------------------------------------------------
  // Pantallas
  // ---------------------------------------------------------------------------
  function pantallaPortada() {
    const p = C.portada;
    comunicar('preocupado', C.alien.saludo);
    return el('section', { class: 'pantalla arranque' },
      el('h1', { tabindex: '-1', 'data-enfocar': '' }, p.titulo),
      el('p', { class: 'subtitulo' }, p.subtitulo),
      el('ul', { class: 'diagnostico', 'aria-label': 'Diagnóstico de la nave' },
        p.diagnostico.map((d) => el('li', {}, el('span', {}, d.sistema), el('span', { class: d.correcto ? 'bien' : 'mal' }, d.estado)))
      ),
      el('div', { class: 'acciones' },
        el('button', {
          type: 'button', class: 'boton', 'data-foco': 'empezar',
          onclick: () => {
            if (!estado.inicio) estado.inicio = Date.now();
            const t = C.ajustes && C.ajustes.temporizador;
            if (t && t.activo && !estado.finTiempo) estado.finTiempo = Date.now() + t.minutos * 60000;
            ir('intro');
          },
        }, p.boton, icono('siguiente'))
      ),
      el('p', { class: 'nota' }, p.nota)
    );
  }

  function pantallaIntro() {
    const intro = C.introduccion;
    const visibles = Math.min(estado.paso + 1, intro.dialogos.length);
    const ultimo = visibles === intro.dialogos.length;
    comunicar(ultimo ? 'neutral' : 'preocupado', intro.estadoCanal);
    return el('section', { class: 'pantalla' },
      el('div', { class: 'cabecera-modulo' }, el('p', {}, intro.titulo), el('p', { class: 'progreso-ronda' }, `${visibles} de ${intro.dialogos.length}`)),
      titulo(`Mensaje de ${C.alien.nombre}`),
      el('ol', { class: 'registro' },
        intro.dialogos.slice(0, visibles).map((texto, i) => el('li', { tabindex: '-1', 'data-foco': `dialogo-${i}` },
          el('span', { class: 'hora' }, `${C.alien.nombre}, T+00:${String(i * 7).padStart(2, '0')}`), texto))
      ),
      el('div', { class: 'acciones' },
        ultimo
          ? el('button', { type: 'button', class: 'boton', 'data-foco': 'aceptar', onclick: () => ir('p1') }, intro.boton, icono('siguiente'))
          : el('button', {
            type: 'button', class: 'boton boton-secundario', 'data-foco': 'continuar',
            onclick: () => { estado.paso++; Sonido.paso(); guardar(); pintar(); enfocar('[data-foco="continuar"], [data-foco="aceptar"]'); },
          }, 'Recibir el siguiente mensaje')
      )
    );
  }

  // Prueba 1: dos publicaciones, una verdadera.
  function pantallaDetectar() {
    const prueba = C.pruebas.detectar;
    const ronda = prueba.rondas[estado.paso];
    const r = temporal.respuesta;
    comunicar('neutral', prueba.mensaje);
    return el('section', { class: 'pantalla' },
      cabeceraModulo(0, `Ronda ${estado.paso + 1} de ${prueba.rondas.length} · ${ronda.tema}`),
      titulo(prueba.titulo),
      el('p', { class: 'instrucciones' }, prueba.instrucciones),
      el('div', { class: 'pareja' },
        ronda.tarjetas.map((t, i) => {
          const elegida = r && r.indice === i;
          const letra = String.fromCharCode(65 + i);
          return el('button', {
            type: 'button', class: 'opcion-publicacion' + (elegida ? (r.acierto ? ' acierto' : ' fallo') : ''),
            disabled: !!r, 'data-foco': `tarjeta-${i}`,
            onclick: () => responder(!!t.verdadera, { indice: i }),
          },
          el('span', { class: 'senal' }, `Señal ${letra}`),
          publicacion(t),
          elegida ? el('span', { class: 'veredicto-sello ' + (t.verdadera ? 'verdadera' : 'bulo') }, icono(t.verdadera ? 'ok' : 'no'), t.verdadera ? 'Verdadera' : 'Bulo') : null
          );
        })
      ),
      r ? bloqueRespuesta(r.acierto, ronda.explicacion, ronda.fuentes, textoSiguiente(estado.paso, prueba.rondas.length),
        () => siguienteRonda(prueba.rondas.length, 0)) : botonPista(ronda.pista)
    );
  }

  // Prueba 2: una publicación y un veredicto.
  function pantallaVerificar() {
    const prueba = C.pruebas.verificar;
    const ronda = prueba.rondas[estado.paso];
    const r = temporal.respuesta;
    comunicar('neutral', prueba.mensaje);
    const boton = (esVerdad) => el('button', {
      type: 'button',
      class: `boton-veredicto ${esVerdad ? 'verdadera' : 'bulo'}` + (r && r.dijoVerdad === esVerdad ? ' elegido' : ''),
      disabled: !!r, 'data-foco': esVerdad ? 'verdad' : 'bulo',
      onclick: () => responder(ronda.esVerdad === esVerdad, { dijoVerdad: esVerdad }),
    }, icono(esVerdad ? 'ok' : 'no'), esVerdad ? prueba.botonVerdad : prueba.botonBulo);

    return el('section', { class: 'pantalla' },
      cabeceraModulo(1, `Señal ${estado.paso + 1} de ${prueba.rondas.length} · ${ronda.tema}`),
      titulo(prueba.titulo),
      el('p', { class: 'instrucciones' }, prueba.instrucciones),
      el('div', { class: 'verificacion' },
        publicacion(ronda.publicacion),
        r ? el('span', { class: 'veredicto-sello ' + (ronda.esVerdad ? 'verdadera' : 'bulo') }, icono(ronda.esVerdad ? 'ok' : 'no'), ronda.esVerdad ? 'Es verdadera' : 'Es un bulo') : null
      ),
      el('div', { class: 'botones-veredicto', role: 'group', 'aria-label': 'Veredicto' }, boton(true), boton(false)),
      r ? bloqueRespuesta(r.acierto, ronda.explicacion, ronda.fuentes, textoSiguiente(estado.paso, prueba.rondas.length),
        () => siguienteRonda(prueba.rondas.length, 1)) : botonPista(ronda.pista)
    );
  }

  // ---------------------------------------------------------------------------
  // Prueba 3: unir cada ejemplo con su tipo mediante líneas. Se puede
  // arrastrar desde un ejemplo (o desde su conector, en pantallas táctiles)
  // hasta un tipo, o tocar un elemento de cada columna; también con teclado.
  // ---------------------------------------------------------------------------
  let arrastre = null;
  let ignorarClic = false;

  function pantallaClasificar() {
    const prueba = C.pruebas.clasificar;
    comunicar('neutral', prueba.mensaje);
    if (!estado.ordenEjemplos || estado.ordenEjemplos.length !== prueba.ejemplos.length) {
      estado.ordenEjemplos = barajar(prueba.ejemplos.length);
      estado.ordenTipos = barajar(prueba.tipos.length);
      guardar();
    }
    const tipo = (id) => prueba.tipos.find((t) => t.id === id) || {};
    const completas = prueba.ejemplos.every((_, i) => estado.conexiones[i] !== undefined);
    const fijado = (i) => estado.conexionesCorrectas.includes(i);
    const erronea = (i) => temporal.erroneas.includes(i);
    const sel = temporal.seleccion;

    const ejemplos = estado.ordenEjemplos.map((i, posicion) => {
      const e = prueba.ejemplos[i];
      const unido = estado.conexiones[i] !== undefined;
      return el('button', {
        type: 'button',
        class: 'nodo' + (fijado(i) ? ' correcto' : erronea(i) ? ' incorrecto' : unido ? ' unido' : '') + (sel && sel.lado === 'ejemplo' && sel.valor === i ? ' elegido' : ''),
        'data-lado': 'ejemplo', 'data-valor': String(i), 'data-foco': `ejemplo-${i}`,
        disabled: fijado(i) || temporal.erroneas.length > 0,
        'aria-pressed': sel && sel.lado === 'ejemplo' && sel.valor === i ? 'true' : 'false',
        'aria-label': `Ejemplo ${posicion + 1}: ${e.texto}` + (unido ? `. Unido con ${tipo(estado.conexiones[i]).nombre}` : '. Sin unir'),
      },
      el('span', {}, e.texto),
      fijado(i) ? el('span', { class: 'nodo-explicacion' }, e.explicacion) : null,
      el('span', { class: 'puerto', 'aria-hidden': 'true' })
      );
    });

    const tipos = estado.ordenTipos.map((j) => {
      const t = prueba.tipos[j];
      const unidos = Object.keys(estado.conexiones).filter((k) => estado.conexiones[k] === t.id);
      return el('button', {
        type: 'button',
        class: 'nodo' + (unidos.some((k) => fijado(Number(k))) ? ' correcto' : unidos.length ? ' unido' : '') + (sel && sel.lado === 'tipo' && sel.valor === t.id ? ' elegido' : ''),
        'data-lado': 'tipo', 'data-valor': t.id, 'data-foco': `tipo-${t.id}`,
        disabled: unidos.some((k) => fijado(Number(k))) || temporal.erroneas.length > 0,
        'aria-pressed': sel && sel.lado === 'tipo' && sel.valor === t.id ? 'true' : 'false',
      },
      el('span', { class: 'nodo-tipo' }, t.nombre),
      el('span', { class: 'nodo-definicion' }, t.definicion),
      el('span', { class: 'puerto', 'aria-hidden': 'true' })
      );
    });

    const zona = el('div', { class: 'conexiones', 'data-conexiones': '' },
      svg('<svg class="conexiones-lineas" aria-hidden="true"></svg>'),
      el('div', { class: 'columna-conexion columna-ejemplos' }, el('h3', {}, 'Ejemplos'), ejemplos),
      el('div', { class: 'columna-conexion columna-tipos' }, el('h3', {}, 'Tipos de desinformación'), tipos)
    );
    zona.addEventListener('click', clicConexion);
    zona.addEventListener('pointerdown', iniciarArrastre);

    const correctas = estado.conexionesCorrectas.length;
    return el('section', { class: 'pantalla' },
      cabeceraModulo(2, `${correctas} de ${prueba.ejemplos.length} verificadas`),
      titulo(prueba.titulo),
      el('p', { class: 'instrucciones' }, prueba.instrucciones),
      zona,
      temporal.aviso ? el('p', { class: 'aviso-prueba', role: 'status' }, temporal.aviso) : null,
      el('div', { class: 'acciones' },
        el('button', { type: 'button', class: 'boton', disabled: !completas || temporal.erroneas.length > 0, 'data-foco': 'comprobar', onclick: comprobarConexiones }, prueba.botonComprobar),
        completas ? null : el('span', { class: 'nota' }, `Faltan ${prueba.ejemplos.length - Object.keys(estado.conexiones).length} por unir.`)
      ),
      botonPista(prueba.pista),
      el('p', { class: 'nota' }, 'Clasificación basada en la tipología de Claire Wardle (First Draft) y del Consejo de Europa.'),
      listaFuentes(prueba.fuentes)
    );
  }

  function conectar(ejemplo, idTipo) {
    if (estado.conexionesCorrectas.includes(ejemplo)) return;
    // Cada tipo admite un solo ejemplo: si ya tenía uno, se suelta.
    for (const k of Object.keys(estado.conexiones)) {
      if (estado.conexiones[k] === idTipo && !estado.conexionesCorrectas.includes(Number(k))) delete estado.conexiones[k];
    }
    estado.conexiones[ejemplo] = idTipo;
    temporal.seleccion = null;
    temporal.aviso = null;
    Sonido.paso();
    guardar();
    pintar();
    const tipo = C.pruebas.clasificar.tipos.find((t) => t.id === idTipo);
    anunciar(`Unido con «${tipo.nombre}».`);
  }

  // Tocar o pulsar (también con teclado): un elemento de cada columna.
  function clicConexion(evento) {
    const nodo = evento.target.closest('.nodo');
    if (!nodo || nodo.disabled) return;
    if (ignorarClic) { ignorarClic = false; return; }
    const lado = nodo.dataset.lado;
    const valor = lado === 'ejemplo' ? Number(nodo.dataset.valor) : nodo.dataset.valor;
    const sel = temporal.seleccion;
    if (sel && sel.lado !== lado) {
      if (lado === 'tipo') conectar(sel.valor, valor); else conectar(valor, sel.valor);
      return;
    }
    temporal.seleccion = sel && sel.valor === valor ? null : { lado, valor };
    Sonido.clic();
    pintar();
    if (temporal.seleccion) anunciar(lado === 'ejemplo' ? 'Ejemplo seleccionado. Elige ahora su tipo.' : 'Tipo seleccionado. Elige ahora el ejemplo.');
  }

  // Arrastrar: con ratón desde cualquier punto del elemento; con el dedo,
  // desde su conector (para no impedir el desplazamiento de la página).
  function iniciarArrastre(evento) {
    const nodo = evento.target.closest('.nodo');
    if (!nodo || nodo.disabled || evento.button > 0) return;
    if (evento.pointerType === 'touch' && !evento.target.closest('.puerto')) return;
    arrastre = { nodo, lado: nodo.dataset.lado, x: evento.clientX, y: evento.clientY, movido: false, id: evento.pointerId };
    try { nodo.setPointerCapture(evento.pointerId); } catch (e) { /* puntero ya liberado */ }
    nodo.addEventListener('pointermove', moverArrastre);
    nodo.addEventListener('pointerup', soltarArrastre);
    nodo.addEventListener('pointercancel', cancelarArrastre);
  }
  function objetivoBajo(x, y, ladoOrigen) {
    const bajo = document.elementFromPoint(x, y);
    const nodo = bajo && bajo.closest('.nodo');
    return nodo && !nodo.disabled && nodo.dataset.lado !== ladoOrigen ? nodo : null;
  }
  function moverArrastre(evento) {
    if (!arrastre) return;
    if (!arrastre.movido && Math.hypot(evento.clientX - arrastre.x, evento.clientY - arrastre.y) < 8) return;
    arrastre.movido = true;
    juego.querySelectorAll('.nodo.objetivo').forEach((n) => n.classList.remove('objetivo'));
    const objetivo = objetivoBajo(evento.clientX, evento.clientY, arrastre.lado);
    if (objetivo) objetivo.classList.add('objetivo');
    arrastre.ultimo = { desde: arrastre.nodo, x: evento.clientX, y: evento.clientY };
    dibujarLineas(arrastre.ultimo);
  }
  function soltarArrastre(evento) {
    const datos = arrastre;
    cancelarArrastre();
    if (!datos || !datos.movido) return;
    ignorarClic = true; // el navegador lanza un clic tras soltar: no cuenta como selección
    setTimeout(() => { ignorarClic = false; }, 0);
    const objetivo = objetivoBajo(evento.clientX, evento.clientY, datos.lado);
    if (!objetivo) { dibujarLineas(); return; }
    const a = datos.lado === 'ejemplo' ? datos.nodo : objetivo;
    const b = datos.lado === 'ejemplo' ? objetivo : datos.nodo;
    conectar(Number(a.dataset.valor), b.dataset.valor);
  }
  function cancelarArrastre() {
    if (!arrastre) return;
    const { nodo } = arrastre;
    nodo.removeEventListener('pointermove', moverArrastre);
    nodo.removeEventListener('pointerup', soltarArrastre);
    nodo.removeEventListener('pointercancel', cancelarArrastre);
    arrastre = null;
    juego.querySelectorAll('.nodo.objetivo').forEach((n) => n.classList.remove('objetivo'));
  }

  // Dibuja una curva entre el conector de cada ejemplo y el de su tipo.
  function dibujarLineas(provisional) {
    const zona = juego.querySelector('[data-conexiones]');
    if (!zona) return;
    const lienzo = zona.querySelector('.conexiones-lineas');
    const caja = zona.getBoundingClientRect();
    // Si la pantalla está a mitad de su animación (escalada), se corrige la escala.
    const escalaX = caja.width / (zona.offsetWidth || 1) || 1;
    const escalaY = caja.height / (zona.offsetHeight || 1) || 1;
    const punto = (nodo) => {
      const p = nodo.querySelector('.puerto').getBoundingClientRect();
      return { x: (p.left + p.width / 2 - caja.left) / escalaX, y: (p.top + p.height / 2 - caja.top) / escalaY };
    };
    const curva = (a, b) => {
      const dx = Math.max(30, Math.abs(b.x - a.x) * 0.5);
      return `M${a.x} ${a.y} C${a.x + dx} ${a.y}, ${b.x - dx} ${b.y}, ${b.x} ${b.y}`;
    };
    let trazos = '';
    for (const [k, idTipo] of Object.entries(estado.conexiones)) {
      const origen = zona.querySelector(`.nodo[data-lado="ejemplo"][data-valor="${k}"]`);
      const destino = zona.querySelector(`.nodo[data-lado="tipo"][data-valor="${idTipo}"]`);
      if (!origen || !destino) continue;
      const clase = estado.conexionesCorrectas.includes(Number(k)) ? 'linea correcta' : temporal.erroneas.includes(Number(k)) ? 'linea incorrecta' : 'linea';
      trazos += `<path class="${clase}" d="${curva(punto(origen), punto(destino))}"/>`;
    }
    if (provisional) {
      const a = punto(provisional.desde);
      const b = { x: (provisional.x - caja.left) / escalaX, y: (provisional.y - caja.top) / escalaY };
      trazos += `<path class="linea provisional" d="${provisional.desde.dataset.lado === 'ejemplo' ? curva(a, b) : curva(b, a)}"/>`;
    }
    lienzo.innerHTML = trazos;
  }

  function comprobarConexiones() {
    const prueba = C.pruebas.clasificar;
    const erroneas = [];
    prueba.ejemplos.forEach((e, i) => {
      if (estado.conexionesCorrectas.includes(i)) return;
      if (estado.conexiones[i] === e.tipo) estado.conexionesCorrectas.push(i);
      else erroneas.push(i);
    });
    if (!erroneas.length) {
      reaccionar(true);
      guardar();
      superarPrueba(2);
      return;
    }
    estado.fallos++;
    temporal.erroneas = erroneas;
    reaccionar(false);
    temporal.aviso = `${estado.conexionesCorrectas.length} de ${prueba.ejemplos.length} conexiones correctas. Las incorrectas se marcan en rojo y se soltarán para que las vuelvas a unir.`;
    guardar();
    pintar();
    anunciar(temporal.aviso);
    // Tras unos segundos se sueltan las conexiones incorrectas.
    setTimeout(() => {
      erroneas.forEach((i) => { delete estado.conexiones[i]; });
      temporal.erroneas = [];
      guardar();
      if (estado.pantalla === 'p3') pintar();
    }, MOVIMIENTO_REDUCIDO ? 2500 : 1800);
  }

  // Prueba 4: cuestionario con corrección. Las acertadas quedan fijadas y las
  // falladas se pueden volver a responder.
  function pantallaCuestionario() {
    const prueba = C.pruebas.cuestionario;
    comunicar('neutral', prueba.mensaje);
    const total = prueba.preguntas.length;
    const fijada = (q) => estado.testCorrectas.includes(q);
    const pendientes = prueba.preguntas.map((_, q) => q).filter((q) => !fijada(q));
    const listas = pendientes.every((q) => estado.respuestasTest[q] !== undefined);
    const todas = estado.testCorrectas.length === total;

    const preguntas = prueba.preguntas.map((pregunta, q) => {
      const resultado = fijada(q) ? true : temporal.resultadoTest[q];
      return el('fieldset', { class: 'pregunta' + (resultado === true ? ' acierto' : resultado === false ? ' fallo' : '') },
        el('legend', {}, el('span', { class: 'numero' }, `${q + 1}.`), pregunta.texto),
        el('div', { class: 'opciones-pregunta' },
          pregunta.opciones.map((opcion, j) => {
            const entrada = el('input', {
              type: 'radio', name: `pregunta-${q}`, value: String(j), 'data-foco': `p${q}-${j}`,
              disabled: fijada(q), checked: estado.respuestasTest[q] === j,
            });
            entrada.addEventListener('change', () => {
              estado.respuestasTest[q] = j;
              delete temporal.resultadoTest[q];
              Sonido.clic();
              guardar();
              pintar();
            });
            return el('label', { class: 'opcion-pregunta' + (fijada(q) && j === pregunta.correcta ? ' correcta-marcada' : '') }, entrada, el('span', {}, opcion));
          })
        ),
        resultado === true
          ? el('div', { class: 'correccion' }, el('strong', {}, 'Correcta'), el('span', {}, pregunta.explicacion),
            pregunta.fuente ? listaFuentes([pregunta.fuente]) : null)
          : resultado === false
            ? el('div', { class: 'correccion', role: 'status' }, el('strong', {}, 'Incorrecta'), el('span', {}, 'Revisa la pregunta y elige otra respuesta.'))
            : null
      );
    });

    return el('section', { class: 'pantalla' },
      cabeceraModulo(3, `${estado.testCorrectas.length} de ${total} correctas`),
      titulo(prueba.titulo),
      el('p', { class: 'instrucciones' }, prueba.instrucciones),
      el('form', { class: 'cuestionario', onsubmit: (evento) => { evento.preventDefault(); corregirCuestionario(); } },
        preguntas,
        todas
          ? el('div', { class: 'respuesta acierto', tabindex: '-1' },
            el('p', { class: 'respuesta-titulo' }, icono('ok'), 'Cuestionario superado'),
            el('p', {}, 'Todas las respuestas son correctas. El filtro de desinformación está calibrado.'),
            el('div', { class: 'acciones' }, el('button', { type: 'button', class: 'boton', 'data-foco': 'seguir', onclick: () => superarPrueba(3) }, 'Restaurar el módulo', icono('siguiente'))))
          : el('div', { class: 'acciones' },
            el('button', { type: 'submit', class: 'boton', disabled: !listas, 'data-foco': 'corregir' }, prueba.botonCorregir),
            listas ? null : el('span', { class: 'nota' }, `Faltan ${pendientes.filter((q) => estado.respuestasTest[q] === undefined).length} por responder.`))
      )
    );
  }

  function corregirCuestionario() {
    const prueba = C.pruebas.cuestionario;
    let fallos = 0;
    prueba.preguntas.forEach((pregunta, q) => {
      if (estado.testCorrectas.includes(q)) return;
      const bien = estado.respuestasTest[q] === pregunta.correcta;
      temporal.resultadoTest[q] = bien;
      if (bien) estado.testCorrectas.push(q);
      else fallos++;
    });
    if (fallos) estado.fallos++;
    reaccionar(!fallos);
    guardar();
    pintar();
    anunciar(fallos ? `${fallos} respuestas incorrectas. Revísalas y vuelve a corregir.` : 'Todas las respuestas son correctas.');
    enfocar(fallos ? '.pregunta.fallo input:not(:disabled)' : '[data-foco="seguir"]');
  }

  // Tras cada módulo: el fragmento del código se descifra en pantalla.
  let moduloRecien = null;
  function pantallaLogro() {
    const indice = estado.logro;
    const ultima = indice === PRUEBAS.length - 1;
    const fragmento = PRUEBAS[indice].datos.fragmento;
    comunicar('contento', C.alien.alSuperarPrueba);
    const caja = el('p', { class: 'fragmento-grande', 'aria-label': `Fragmento ${fragmento}` }, fragmento);
    if (moduloRecien === indice && !MOVIMIENTO_REDUCIDO) descifrar(caja, fragmento);
    return el('section', { class: 'pantalla desbloqueo' },
      cabeceraModulo(indice, 'Restaurado'),
      titulo('Fragmento recuperado'),
      el('p', { class: 'instrucciones' }, `El reactor sube al ${estado.superadas.length * 25} %. Guarda este fragmento: forma parte del código de activación.`),
      caja,
      el('div', { class: 'acciones' },
        el('button', {
          type: 'button', class: 'boton', 'data-foco': 'siguiente-modulo',
          onclick: () => ir(ultima ? 'activacion' : PRUEBAS[indice + 1].pantalla),
        }, ultima ? 'Ir a la activación' : `Módulo ${indice + 2}: ${PRUEBAS[indice + 1].datos.nombre}`, icono('siguiente'))
      )
    );
  }

  function descifrar(nodo, final) {
    const signos = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let vuelta = 0;
    const reloj = setInterval(() => {
      vuelta++;
      nodo.textContent = final.split('').map((letra, i) => (vuelta > 6 + i * 3 ? letra : signos[Math.floor(Math.random() * signos.length)])).join('');
      if (vuelta > 6 + final.length * 3) { clearInterval(reloj); nodo.textContent = final; }
    }, 55);
  }

  function pantallaActivacion() {
    const a = C.activacion;
    comunicar('contento', a.mensaje);
    if (temporal.secuencia) {
      return el('section', { class: 'pantalla' },
        el('div', { class: 'cabecera-modulo' }, el('p', {}, 'Secuencia de arranque'), el('p', { class: 'progreso-ronda' }, 'En curso')),
        titulo('Arrancando sistemas'),
        el('ol', { class: 'secuencia', 'aria-live': 'polite' }, a.secuencia.map((paso) => el('li', {}, paso)))
      );
    }
    const entrada = el('input', {
      id: 'codigo-activacion', type: 'text', autocomplete: 'off', autocapitalize: 'characters', spellcheck: 'false',
      'aria-describedby': temporal.errorCodigo ? 'codigo-error' : null,
    });
    return el('section', { class: 'pantalla' },
      el('div', { class: 'cabecera-modulo' }, el('p', {}, 'Panel de activación'), el('p', { class: 'progreso-ronda' }, 'Reactor al 100 %')),
      titulo(a.titulo),
      el('p', { class: 'instrucciones' }, a.texto),
      el('p', { class: 'ranuras' }, PRUEBAS.map((p, i) => el('span', { class: 'fragmento conseguido', 'aria-label': `Módulo ${i + 1}: ${p.datos.fragmento}` }, p.datos.fragmento))),
      el('form', {
        class: 'campo-codigo',
        onsubmit: (evento) => {
          evento.preventDefault();
          if (normalizar(entrada.value) === normalizar(CODIGO)) { lanzarSecuencia(); return; }
          estado.fallos++;
          temporal.errorCodigo = true;
          reaccionar(false);
          guardar();
          pintar();
          anunciar(a.error);
          const campo = document.getElementById('codigo-activacion');
          if (campo) campo.focus();
        },
      },
      el('label', { for: 'codigo-activacion' }, 'Código de activación'),
      el('div', { class: 'fila' }, entrada, el('button', { type: 'submit', class: 'boton' }, icono('llave'), a.boton))
      ),
      temporal.errorCodigo ? el('p', { class: 'error', id: 'codigo-error' }, a.error) : null
    );
  }

  // Secuencia de arranque: los sistemas se encienden uno a uno y la nave despega.
  function lanzarSecuencia() {
    temporal.secuencia = true;
    temporal.reaccion = null;
    pintar();
    const pasos = juego.querySelectorAll('.secuencia li');
    const ritmo = MOVIMIENTO_REDUCIDO ? 150 : 650;
    pasos.forEach((li, i) => setTimeout(() => { li.classList.add('listo'); Sonido.paso(); }, ritmo * (i + 1)));
    setTimeout(() => {
      Sonido.arranque();
      estado.fin = estado.fin || Date.now();
      ir('final');
    }, ritmo * (pasos.length + 1));
  }

  function pantallaFinal() {
    const f = C.final;
    comunicar('contento', f.mensajeVero);
    const minutos = estado.inicio && estado.fin ? Math.max(1, Math.round((estado.fin - estado.inicio) / 60000)) : null;
    return el('section', { class: 'pantalla' },
      el('div', { class: 'cabecera-modulo' }, el('p', {}, 'Propulsión activada'), el('p', { class: 'progreso-ronda' }, 'Saliendo de órbita')),
      el('div', { class: 'final-contenido' },
        titulo(f.titulo),
        el('p', { class: 'instrucciones' }, f.mensaje),
        el('ul', { class: 'aprendido' }, f.aprendido.map((t) => el('li', {}, t))),
        el('dl', { class: 'informe' },
          el('div', {}, el('dt', {}, 'Tiempo'), el('dd', {}, minutos ? `${minutos} min` : '—')),
          el('div', {}, el('dt', {}, 'Errores'), el('dd', {}, estado.fallos)),
          el('div', {}, el('dt', {}, 'Pistas'), el('dd', {}, estado.pistas))
        ),
        el('p', { class: 'aviso-simulacion' }, f.aviso),
        el('h3', {}, 'Si dudas de una información, consulta a los verificadores'),
        el('ul', { class: 'verificadores' }, f.verificadores.map((v) => el('li', {},
          el('a', { href: v.url, target: '_blank', rel: 'noopener noreferrer' }, v.nombre, el('span', { class: 'solo-lector' }, ' (se abre en una pestaña nueva)'))
        ))),
        el('div', { class: 'creditos' },
          el('p', {}, f.creditos),
          el('p', {}, 'Imágenes:'),
          el('ul', {}, f.creditosImagenes.map((c) => el('li', {}, el('a', { href: c.url, target: '_blank', rel: 'noopener noreferrer' }, c.texto))))
        ),
        el('div', { class: 'acciones' }, el('button', { type: 'button', class: 'boton boton-secundario', onclick: reiniciar }, icono('repetir'), f.botonReiniciar))
      )
    );
  }

  // ---------------------------------------------------------------------------
  // Panel de sistemas: módulos, reactor, código y temporizador
  // ---------------------------------------------------------------------------
  let energiaMostrada = null;
  function actualizarSistemas() {
    const superadas = estado.superadas.length;
    const actual = PRUEBAS.findIndex((p) => p.pantalla === estado.pantalla);

    document.getElementById('modulos').replaceChildren(...PRUEBAS.map((p, i) => {
      const hecho = estado.superadas.includes(i);
      const enCurso = i === actual;
      const situacion = hecho ? 'Restaurado' : enCurso ? 'En curso' : 'Pendiente';
      return el('li', { class: 'modulo' + (hecho ? ' completado' : '') + (enCurso ? ' actual' : '') + (moduloRecien === i ? ' recien' : ''), 'aria-current': enCurso ? 'step' : false },
        el('span', { class: 'modulo-luz', 'aria-hidden': 'true' }),
        el('span', { class: 'modulo-nombre' }, el('span', { class: 'modulo-numero' }, `${i + 1}.`), ' ', el('span', { class: 'modulo-texto' }, p.datos.nombre)),
        el('span', { class: 'modulo-estado' }, situacion),
        el('span', { class: 'fragmento' + (hecho ? ' conseguido' : ''), 'aria-label': hecho ? `fragmento ${p.datos.fragmento}` : 'fragmento oculto' }, hecho ? p.datos.fragmento : '··')
      );
    }));
    moduloRecien = null;

    const porcentaje = superadas * 25;
    const reactor = document.getElementById('reactor');
    reactor.setAttribute('aria-valuenow', porcentaje);
    reactor.setAttribute('aria-valuetext', `${porcentaje} % de energía`);
    reactor.querySelectorAll('.reactor-celda').forEach((celda, i) => {
      celda.classList.toggle('llena', i < superadas);
      if (energiaMostrada !== null && i < superadas && i >= energiaMostrada) {
        celda.classList.remove('encendiendo');
        void celda.offsetWidth; // reinicia la animación
        celda.classList.add('encendiendo');
      }
    });
    document.getElementById('reactor-lectura').textContent = `${porcentaje} %`;
    if (energiaMostrada !== superadas) Sonido.energia(superadas);
    energiaMostrada = superadas;

    document.getElementById('codigo').replaceChildren(...PRUEBAS.map((p, i) => {
      const hecho = estado.superadas.includes(i);
      return el('span', { class: 'fragmento' + (hecho ? ' conseguido' : ''), 'aria-label': hecho ? `Fragmento ${i + 1}: ${p.datos.fragmento}` : `Fragmento ${i + 1}: oculto` }, hecho ? p.datos.fragmento : '··');
    }));

    document.body.dataset.energia = String(superadas);
    document.body.classList.toggle('despegando', estado.pantalla === 'final');
    const hud = document.getElementById('hud-estado');
    if (hud) hud.textContent = estado.pantalla === 'final' ? 'PROPULSIÓN ACTIVA' : `REACTOR ${Math.max(3, porcentaje)} %`;
    actualizarTemporizador();
  }

  let avisoTiempoDado = false;
  function actualizarTemporizador() {
    const caja = document.getElementById('temporizador');
    const t = C.ajustes && C.ajustes.temporizador;
    if (!t || !t.activo || !estado.finTiempo) { caja.hidden = true; return; }
    caja.hidden = false;
    // Al terminar la misión, el reloj se queda parado en el tiempo que sobraba.
    const restante = Math.max(0, estado.finTiempo - (estado.fin || Date.now()));
    const min = Math.floor(restante / 60000);
    const seg = Math.floor((restante % 60000) / 1000);
    caja.textContent = restante > 0 ? `Tiempo restante ${min}:${String(seg).padStart(2, '0')}` : 'Tiempo agotado';
    if (restante === 0 && !avisoTiempoDado && !estado.fin) {
      avisoTiempoDado = true;
      anunciar('Se ha acabado el tiempo. Puedes seguir jugando hasta despegar.');
    }
  }
  setInterval(actualizarTemporizador, 1000);

  // ---------------------------------------------------------------------------
  // Pintado y foco
  // ---------------------------------------------------------------------------
  const PANTALLAS = {
    portada: pantallaPortada, intro: pantallaIntro,
    p1: pantallaDetectar, p2: pantallaVerificar, p3: pantallaClasificar, p4: pantallaCuestionario,
    logro: pantallaLogro, activacion: pantallaActivacion, final: pantallaFinal,
  };
  let ultimaVista = null;

  function enfocar(selector) {
    const destino = juego.querySelector(selector);
    if (destino) destino.focus();
    return !!destino;
  }

  function pintar() {
    // Si el foco estaba en un control que se vuelve a dibujar, pasa a su copia nueva.
    const conFoco = document.activeElement && document.activeElement.getAttribute('data-foco');
    const vista = `${estado.pantalla}:${estado.paso}:${temporal.secuencia}`;
    try {
      const pantalla = (PANTALLAS[estado.pantalla] || pantallaPortada)();
      if (vista !== ultimaVista) pantalla.classList.add('entrar');
      juego.replaceChildren(pantalla);
    } catch (error) {
      juego.innerHTML = '<p class="aviso-carga">Hay un problema con el contenido de <strong>content.js</strong> ' +
        '(¿falta algún campo en el módulo actual?). Pulsa «Reiniciar» o revisa el último cambio.</p>';
      console.error(error);
    }
    pintarCanal();
    actualizarSistemas();
    if (estado.pantalla === 'p3') requestAnimationFrame(() => dibujarLineas());

    if (vista !== ultimaVista) {
      const primeraVez = ultimaVista === null;
      ultimaVista = vista;
      if (!primeraVez) {
        const cabecera = juego.querySelector('[data-enfocar]');
        if (cabecera) cabecera.focus({ preventScroll: true });
        // En pantalla grande se desplaza el monitor; en móvil, la página.
        juego.scrollTop = 0;
        if (getComputedStyle(document.body).overflow !== 'hidden') juego.scrollIntoView({ behavior: MOVIMIENTO_REDUCIDO ? 'auto' : 'smooth', block: 'start' });
      }
    } else if (conFoco) {
      const copia = juego.querySelector(`[data-foco="${conFoco}"]`);
      if (copia && !copia.disabled) copia.focus({ preventScroll: true });
    }
  }

  function reiniciar() {
    if (!confirm('¿Seguro que quieres reiniciar la misión? Se perderá el progreso.')) return;
    estado = estadoInicial();
    temporal = temporalInicial();
    avisoTiempoDado = false;
    guardar();
    conmutar(() => {});
  }

  // Las líneas se recalculan si cambia el tamaño o, en móvil, al desplazarse
  // (la columna de tipos se queda fija mientras pasan los ejemplos).
  let redibujoPendiente = false;
  function redibujarLineas() {
    if (estado.pantalla !== 'p3' || redibujoPendiente) return;
    redibujoPendiente = true;
    requestAnimationFrame(() => { redibujoPendiente = false; dibujarLineas(arrastre && arrastre.ultimo); });
  }
  window.addEventListener('resize', redibujarLineas);
  window.addEventListener('scroll', redibujarLineas, { passive: true });
  juego.addEventListener('scroll', redibujarLineas, { passive: true });

  // ---------------------------------------------------------------------------
  // Mandos del panel de sistemas
  // ---------------------------------------------------------------------------
  const botonSonido = document.getElementById('boton-sonido');
  const textoSonido = document.getElementById('boton-sonido-texto');
  const volumen = document.getElementById('volumen');
  const botonTexto = document.getElementById('boton-texto');

  function aplicarSonido() {
    botonSonido.setAttribute('aria-pressed', preferencias.sonido ? 'true' : 'false');
    textoSonido.textContent = preferencias.sonido ? 'Silenciar' : 'Activar sonido';
  }
  botonSonido.addEventListener('click', () => {
    preferencias.sonido = !preferencias.sonido;
    Sonido.activar(preferencias.sonido);
    aplicarSonido();
    guardarPreferencias();
  });
  volumen.value = String(Math.round(preferencias.volumen * 100));
  volumen.addEventListener('input', () => {
    preferencias.volumen = Number(volumen.value) / 100;
    Sonido.volumen(preferencias.volumen);
    guardarPreferencias();
  });
  // El navegador no deja sonar nada sin una interacción previa: aunque el
  // jugador lo dejara activado, se reactiva con su primer clic.
  if (preferencias.sonido) {
    document.addEventListener('pointerdown', () => Sonido.activar(true), { once: true });
  }
  aplicarSonido();

  function aplicarTextoGrande() {
    document.documentElement.classList.toggle('texto-grande', preferencias.textoGrande);
    botonTexto.setAttribute('aria-pressed', preferencias.textoGrande ? 'true' : 'false');
  }
  botonTexto.addEventListener('click', () => {
    preferencias.textoGrande = !preferencias.textoGrande;
    aplicarTextoGrande();
    guardarPreferencias();
    if (estado.pantalla === 'p3') requestAnimationFrame(() => dibujarLineas());
  });
  aplicarTextoGrande();

  document.getElementById('boton-reiniciar').addEventListener('click', reiniciar);

  pintar();
})();
