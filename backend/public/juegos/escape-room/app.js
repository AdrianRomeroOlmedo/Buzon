/* =============================================================================
   LÓGICA DEL JUEGO · Misión Combustible de Verdad
   No hace falta tocar este archivo para cambiar textos: todo el contenido está
   en content.js. Aquí solo está el funcionamiento de las pantallas y pruebas.

   La escena es la cabina de la nave vista en primera persona:
   · #juego (pantalla principal): donde se juega cada prueba.
   · #holograma y #mensaje (canal de comunicación): Vero, el extraterrestre,
     en videollamada (dibujado en SVG por alienSVG).
   · Instrumentos: depósito de combustible, código de despegue y estado.
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

  const CLAVE_PROGRESO = 'escape-room-combustible-verdad';
  const CLAVE_TEXTO = 'escape-room-texto-grande';

  // Las cuatro pruebas en orden; «pantalla» es el nombre interno de cada una.
  const PRUEBAS = [
    { pantalla: 'p1', datos: C.pruebas.cualEsVerdad },
    { pantalla: 'p2', datos: C.pruebas.verdadOBulo },
    { pantalla: 'p3', datos: C.pruebas.emparejar },
    { pantalla: 'p4', datos: C.pruebas.infografia },
  ];
  const CODIGO = PRUEBAS.map((p) => p.datos.fragmento).join('');

  // ---------------------------------------------------------------------------
  // Estado: lo que se guarda (estado) y lo que solo dura mientras se ve la
  // pantalla (temporal: la respuesta mostrada, la pista abierta…).
  // ---------------------------------------------------------------------------
  function estadoInicial() {
    return {
      pantalla: 'portada', paso: 0, test: false, logro: null,
      superadas: [], parejas: {}, correctas: [], ordenNoticias: null,
      fallos: 0, pistas: 0, inicio: null, fin: null, finTiempo: null,
    };
  }
  function temporalInicial() {
    return { respuesta: null, pista: false, reaccion: null, seleccion: null, fallidas: [], aviso: null, errorCodigo: false };
  }

  let estado = cargar();
  let temporal = temporalInicial();
  let canal = { expresion: 'neutral', texto: '' }; // lo que dice Vero en la pantalla actual
  let ultimaVista = null;
  let combustibleMostrado = null;
  let infografiaDisponible = null; // null: aún no se sabe si existe assets/infografia.png

  function cargar() {
    try {
      const guardado = JSON.parse(localStorage.getItem(CLAVE_PROGRESO));
      if (guardado && guardado.version === C.version) return Object.assign(estadoInicial(), guardado.estado);
    } catch (e) { /* sin almacenamiento: se empieza de cero */ }
    return estadoInicial();
  }
  function guardar() {
    try {
      localStorage.setItem(CLAVE_PROGRESO, JSON.stringify({ version: C.version, estado }));
    } catch (e) { /* modo privado o almacenamiento bloqueado */ }
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

  function dosCifras(n) {
    return String(n).padStart(2, '0');
  }

  // ---------------------------------------------------------------------------
  // Vero en el canal de comunicación: un alienígena de cráneo alargado, ojos
  // negros rasgados y manchas bioluminiscentes, con cuatro expresiones
  // (neutral, contento, preocupado, sorprendido).
  // ---------------------------------------------------------------------------
  const EXPRESIONES = { neutral: 'atento', contento: 'satisfecho', preocupado: 'preocupado', sorprendido: 'sorprendido' };

  function alienSVG(expresion) {
    const sombra = '#3d6e5a';
    const linea = (d, ancho) => `<path d="${d}" stroke="${sombra}" stroke-width="${ancho || 2.5}" fill="none" stroke-linecap="round"/>`;
    // Ojo izquierdo; el derecho es su reflejo respecto al centro (x = 80).
    const ojo = (escala) => {
      const t = escala ? `transform="translate(51 78) scale(${escala}) translate(-51 -78)"` : '';
      return `<g ${t}>` +
        '<path d="M30 80 C34 60 58 52 72 70 C74 86 58 100 42 96 C34 94 29 88 30 80Z" fill="#05080c"/>' +
        '<ellipse cx="51" cy="80" rx="13" ry="9" fill="url(#iris-vero)" transform="rotate(-24 51 80)"/>' +
        '<ellipse cx="44" cy="71" rx="4.5" ry="2.6" fill="#fff" opacity=".85" transform="rotate(-24 44 71)"/>' +
        '<circle cx="59" cy="86" r="1.6" fill="#fff" opacity=".6"/></g>';
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
        `<ellipse cx="80" cy="128" rx="5" ry="7" fill="#20352c"/>`,
    };
    const etiqueta = `${escapar(C.alien.nombre)}, el extraterrestre, en el canal de comunicación; parece ${EXPRESIONES[expresion] || EXPRESIONES.neutral}`;
    return `<svg viewBox="0 0 160 190" role="img" aria-label="${etiqueta}">` +
      '<defs>' +
      '<radialGradient id="piel-vero" cx="45%" cy="32%" r="75%"><stop offset="0" stop-color="#bfe8cd"/><stop offset=".55" stop-color="#78b394"/><stop offset="1" stop-color="#3f705c"/></radialGradient>' +
      '<radialGradient id="iris-vero" cx="40%" cy="40%" r="60%"><stop offset="0" stop-color="#1c8f84"/><stop offset="1" stop-color="#05080c" stop-opacity="0"/></radialGradient>' +
      '</defs>' +
      // Traje y cuello
      '<path d="M18 190 C24 172 48 165 80 165 C112 165 136 172 142 190Z" fill="#1c2638" stroke="#4fd8ff" stroke-width="1.5"/>' +
      '<path d="M54 168 Q80 181 106 168" stroke="#4fd8ff" stroke-width="3" fill="none"/>' +
      '<circle cx="80" cy="182" r="4" fill="#ffb547"/>' +
      '<path d="M64 138 C64 154 62 164 58 172 L102 172 C98 164 96 154 96 138Z" fill="#5f957a"/>' +
      linea('M67 154 L74 157 M67 161 L75 164 M93 154 L86 157 M93 161 L85 164', 2) +
      // Cabeza: cráneo alto y barbilla estrecha
      '<path d="M80 4 C124 4 146 38 144 74 C142 106 120 128 100 140 C92 145 86 150 80 150 C74 150 68 145 60 140 C40 128 18 106 16 74 C14 38 36 4 80 4Z" fill="url(#piel-vero)"/>' +
      `<g opacity=".6">${linea('M52 18 Q80 6 108 18')}${linea('M46 30 Q80 16 114 30')}${linea('M80 8 V44', 2)}</g>` +
      // Manchas bioluminiscentes
      '<g fill="#7ff7dc" class="manchas-vero">' +
      '<circle cx="80" cy="52" r="3"/><circle cx="26" cy="62" r="2.6"/><circle cx="22" cy="74" r="2"/><circle cx="25" cy="86" r="1.6"/>' +
      '<circle cx="134" cy="62" r="2.6"/><circle cx="138" cy="74" r="2"/><circle cx="135" cy="86" r="1.6"/></g>' +
      // Nariz: dos rendijas
      linea('M76 107 Q74.5 112 77 116 M84 107 Q85.5 112 83 116', 2) +
      (partes[expresion] || partes.neutral) +
      '</svg>';
  }

  // Cada pantalla dice qué cuenta Vero. Si hay una reacción pendiente
  // (acierto, fallo, pista), se muestra esa en su lugar.
  function comunicar(expresion, texto) {
    canal = { expresion, texto };
  }

  let canalPintado = null;
  function pintarCanal() {
    const r = temporal.reaccion || canal;
    const clave = r.expresion + '|' + r.texto;
    if (clave === canalPintado) return; // no repetir el aviso al lector de pantalla
    canalPintado = clave;
    holograma.innerHTML = alienSVG(r.expresion);
    mensaje.replaceChildren(el('span', { class: 'mensaje-autor' }, C.alien.nombre), r.texto);
  }

  // ---------------------------------------------------------------------------
  // Piezas comunes
  // ---------------------------------------------------------------------------

  // Mensaje interceptado: imita un tuit, un WhatsApp, un SMS o un titular de prensa.
  function tarjeta(t) {
    const tipo = t.tipo || 'titular';
    let partes;
    if (tipo === 'tuit') {
      partes = [
        el('span', { class: 'tarjeta-cabecera' },
          el('span', { class: 'avatar', 'aria-hidden': 'true' }, (t.autor || '?').charAt(0).toUpperCase()),
          el('span', {}, el('span', { class: 'tarjeta-autor' }, t.autor), el('span', { class: 'tarjeta-usuario' }, t.usuario))
        ),
        el('span', { class: 'tarjeta-texto' }, t.texto),
      ];
    } else if (tipo === 'whatsapp' || tipo === 'sms') {
      partes = [
        el('span', { class: 'tarjeta-reenviado' }, (tipo === 'whatsapp' ? '↪ ' : '✉ ') + t.autor),
        el('span', { class: 'tarjeta-burbuja tarjeta-texto' }, t.texto),
      ];
    } else {
      partes = [
        el('span', { class: 'tarjeta-medio' }, t.autor),
        el('span', { class: 'tarjeta-texto' }, t.texto),
      ];
    }
    const nombreTipo = { tuit: 'Publicación en redes', whatsapp: 'Mensaje de WhatsApp', sms: 'SMS', titular: 'Titular de prensa' }[tipo];
    return el('span', { class: `tarjeta tarjeta-${tipo}` }, el('span', { class: 'solo-lector' }, nombreTipo + '. '), partes);
  }

  // Rótulo superior de la pantalla principal: «MÓDULO 01 · …» y la ronda.
  function cabecera(izquierda, derecha) {
    return el('div', { class: 'modulo' },
      el('p', { class: 'rotulo' }, izquierda),
      derecha ? el('p', { class: 'rotulo' }, derecha) : null
    );
  }

  function titulo(texto) {
    return el('h2', { class: 'titulo-pantalla', tabindex: '-1', 'data-enfocar': '' }, texto);
  }

  function botonPista(texto) {
    return [
      temporal.pista ? el('p', { class: 'pista-caja', tabindex: '-1', 'data-foco': 'pista-texto' }, el('strong', {}, 'Pista: '), texto) : null,
      temporal.pista ? null : el('button', {
        type: 'button', class: 'boton boton-pista', 'data-foco': 'pista',
        onclick: () => {
          temporal.pista = true;
          temporal.reaccion = { expresion: 'sorprendido', texto: C.alien.alPedirPista };
          estado.pistas++;
          guardar();
          pintar();
          enfocar('[data-foco="pista-texto"]');
        },
      }, 'Solicitar pista'),
    ];
  }

  // Bloque de explicación tras responder: siempre explica, se acierte o no.
  function bloqueRespuesta(acierto, explicacion, textoSiguiente, alSiguiente) {
    return el('div', { class: 'respuesta ' + (acierto ? 'acierto' : 'fallo'), tabindex: '-1' },
      el('p', { class: 'respuesta-titulo' }, acierto ? '✓ Correcto' : '✕ Incorrecto'),
      el('p', {}, explicacion),
      el('div', { class: 'acciones' },
        acierto
          ? el('button', { type: 'button', class: 'boton', 'data-foco': 'seguir', onclick: alSiguiente }, textoSiguiente)
          : el('button', { type: 'button', class: 'boton', 'data-foco': 'reintentar', onclick: reintentar }, '↻ Volver a intentarlo')
      )
    );
  }

  function responder(acierto, datos) {
    temporal.respuesta = Object.assign({ acierto }, datos);
    temporal.reaccion = acierto
      ? { expresion: 'contento', texto: azar(C.alien.alAcertar) }
      : { expresion: 'preocupado', texto: azar(C.alien.alFallar) };
    if (!acierto) estado.fallos++;
    guardar();
    pintar();
    enfocar('[data-foco="seguir"], [data-foco="reintentar"]');
  }

  function reintentar() {
    temporal = temporalInicial();
    pintar();
    const primero = juego.querySelector('.tarjeta-boton, .boton-veredicto, .opcion');
    if (primero) primero.focus();
  }

  // Avanza a la siguiente ronda o, si era la última, da la prueba por superada.
  function siguienteRonda(total, indicePrueba) {
    temporal = temporalInicial();
    if (estado.paso + 1 < total) {
      estado.paso++;
      guardar();
      pintar();
    } else {
      superarPrueba(indicePrueba);
    }
  }

  function superarPrueba(indice) {
    if (!estado.superadas.includes(indice)) estado.superadas.push(indice);
    estado.pantalla = 'logro';
    estado.logro = indice;
    estado.paso = 0;
    temporal = temporalInicial();
    guardar();
    pintar();
    anunciar(`Módulo superado. Depósito al ${estado.superadas.length * 25} por ciento.`);
  }

  function ir(pantalla) {
    estado.pantalla = pantalla;
    estado.paso = 0;
    estado.test = false;
    temporal = temporalInicial();
    guardar();
    pintar();
  }

  function textoSiguiente(paso, total) {
    return paso + 1 < total ? 'Siguiente →' : 'Completar módulo →';
  }

  function moduloDe(indice) {
    return `Módulo ${dosCifras(indice + 1)}`;
  }

  // ---------------------------------------------------------------------------
  // Pantallas
  // ---------------------------------------------------------------------------

  // Arranque: diagnóstico de la nave averiada.
  function pantallaPortada() {
    const p = C.portada;
    comunicar('preocupado', C.alien.saludo);
    return el('section', { class: 'pantalla arranque' },
      cabecera('Diagnóstico del sistema', p.antetitulo),
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
        }, p.boton)
      ),
      el('p', { class: 'nota' }, p.nota)
    );
  }

  // Introducción: la transmisión de Vero, línea a línea.
  function pantallaIntro() {
    const intro = C.introduccion;
    const visibles = Math.min(estado.paso + 1, intro.dialogos.length);
    const ultimo = visibles === intro.dialogos.length;
    comunicar(ultimo ? 'neutral' : 'preocupado', intro.estadoCanal);
    return el('section', { class: 'pantalla' },
      cabecera('Transmisión entrante', `${visibles} / ${intro.dialogos.length}`),
      titulo(intro.titulo),
      el('ol', { class: 'registro' },
        intro.dialogos.slice(0, visibles).map((texto, i) => el('li', { tabindex: '-1', 'data-foco': `dialogo-${i}` },
          el('span', { class: 'hora' }, `${C.alien.nombre} · T+00:${dosCifras(i * 7)}`), texto))
      ),
      el('div', { class: 'acciones' },
        ultimo
          ? el('button', { type: 'button', class: 'boton', 'data-foco': 'ayudar', onclick: () => ir('p1') }, intro.boton)
          : el('button', {
            type: 'button', class: 'boton', 'data-foco': 'continuar',
            onclick: () => { estado.paso++; guardar(); pintar(); enfocar('[data-foco="continuar"], [data-foco="ayudar"]'); },
          }, 'Continuar →')
      )
    );
  }

  // Prueba 1: dos mensajes, uno verdadero.
  function pantallaCualEsVerdad() {
    const prueba = C.pruebas.cualEsVerdad;
    const ronda = prueba.rondas[estado.paso];
    const r = temporal.respuesta;
    comunicar('neutral', prueba.mensaje);
    return el('section', { class: 'pantalla' },
      cabecera(`${moduloDe(0)} · ${ronda.tema || ''}`, `Ronda ${estado.paso + 1} / ${prueba.rondas.length}`),
      titulo(prueba.titulo),
      el('p', { class: 'instrucciones' }, prueba.instrucciones),
      el('div', { class: 'parejas-tarjetas' },
        ronda.tarjetas.map((t, i) => {
          const elegida = r && r.indice === i;
          return el('div', {},
            el('p', { class: 'interceptado', 'aria-hidden': 'true' }, `Señal ${String.fromCharCode(65 + i)}`),
            el('button', {
              type: 'button', class: 'tarjeta-boton' + (elegida ? (r.acierto ? ' acierto' : ' fallo') : ''),
              disabled: !!r, 'data-foco': `tarjeta-${i}`,
              'aria-label': `Señal ${String.fromCharCode(65 + i)}: ${t.texto}`,
              onclick: () => responder(!!t.verdadera, { indice: i }),
            },
            tarjeta(t),
            elegida ? el('span', { class: 'sello ' + (t.verdadera ? 'sello-verdad' : 'sello-bulo') }, t.verdadera ? '✓ Verdad' : '✕ Bulo') : null
            )
          );
        })
      ),
      r ? bloqueRespuesta(r.acierto, ronda.explicacion, textoSiguiente(estado.paso, prueba.rondas.length),
        () => siguienteRonda(prueba.rondas.length, 0)) : botonPista(ronda.pista)
    );
  }

  // Prueba 2: una información y dos botones, «Verdad» y «Bulo».
  function pantallaVerdadOBulo() {
    const prueba = C.pruebas.verdadOBulo;
    const ronda = prueba.rondas[estado.paso];
    const r = temporal.respuesta;
    comunicar('neutral', prueba.mensaje);
    const boton = (esVerdad) => el('button', {
      type: 'button',
      class: `boton boton-veredicto ${esVerdad ? 'boton-verdad' : 'boton-bulo'}` + (r && r.dijoVerdad === esVerdad ? ' elegido' : ''),
      disabled: !!r, 'data-foco': esVerdad ? 'verdad' : 'bulo',
      onclick: () => responder(ronda.esVerdad === esVerdad, { dijoVerdad: esVerdad }),
    }, esVerdad ? '✓ ' + prueba.botonVerdad : '✕ ' + prueba.botonBulo);

    return el('section', { class: 'pantalla' },
      cabecera(`${moduloDe(1)} · ${ronda.tema || ''}`, `Ronda ${estado.paso + 1} / ${prueba.rondas.length}`),
      titulo(prueba.titulo),
      el('p', { class: 'instrucciones' }, prueba.instrucciones),
      el('div', {}, el('p', { class: 'interceptado' }, 'Señal interceptada'), tarjeta(ronda)),
      r ? el('p', {}, el('span', { class: 'sello ' + (ronda.esVerdad ? 'sello-verdad' : 'sello-bulo') },
        ronda.esVerdad ? '✓ Es verdad' : '✕ Es un bulo')) : null,
      el('div', { class: 'botones-veredicto' }, boton(true), boton(false)),
      r ? bloqueRespuesta(r.acierto, ronda.explicacion, textoSiguiente(estado.paso, prueba.rondas.length),
        () => siguienteRonda(prueba.rondas.length, 1)) : botonPista(ronda.pista)
    );
  }

  // Prueba 3: emparejar noticias con técnicas de desinformación.
  function pantallaEmparejar() {
    const prueba = C.pruebas.emparejar;
    comunicar('neutral', prueba.mensaje);
    if (!estado.ordenNoticias || estado.ordenNoticias.length !== prueba.noticias.length) {
      estado.ordenNoticias = barajar(prueba.noticias.length);
      guardar();
    }
    const nombreTecnica = (id) => (prueba.tecnicas.find((t) => t.id === id) || {}).nombre;
    const numero = (indice) => estado.ordenNoticias.indexOf(indice) + 1;
    const noticiaEn = (idTecnica) => {
      const clave = Object.keys(estado.parejas).find((k) => estado.parejas[k] === idTecnica);
      return clave === undefined ? null : Number(clave);
    };
    const completas = prueba.noticias.every((_, i) => estado.parejas[i] !== undefined);

    function asignar(indice, idTecnica) {
      const ocupada = noticiaEn(idTecnica);
      if (ocupada !== null && estado.correctas.includes(ocupada)) return;
      if (ocupada !== null) delete estado.parejas[ocupada];
      estado.parejas[indice] = idTecnica;
      temporal.seleccion = null;
      temporal.aviso = null;
      temporal.fallidas = temporal.fallidas.filter((f) => f !== indice);
      guardar();
      pintar();
      anunciar(`Noticia ${numero(indice)} emparejada con «${nombreTecnica(idTecnica)}».`);
    }

    function comprobar() {
      const fallidas = [];
      prueba.noticias.forEach((n, i) => {
        if (estado.correctas.includes(i)) return;
        if (estado.parejas[i] === n.tecnica) estado.correctas.push(i);
        else { delete estado.parejas[i]; fallidas.push(i); }
      });
      if (fallidas.length === 0) {
        superarPrueba(2);
        return;
      }
      estado.fallos++;
      temporal.fallidas = fallidas;
      temporal.reaccion = { expresion: 'preocupado', texto: azar(C.alien.alFallar) };
      temporal.aviso = `${estado.correctas.length} de ${prueba.noticias.length} correctas. Las que no encajaban se han soltado (marcadas con ✕): vuelve a emparejarlas.`;
      guardar();
      pintar();
      anunciar(temporal.aviso);
    }

    const noticias = estado.ordenNoticias.map((i) => {
      const n = prueba.noticias[i];
      const correcta = estado.correctas.includes(i);
      const fallida = temporal.fallidas.includes(i) && estado.parejas[i] === undefined;
      const asignada = estado.parejas[i];
      const situacion = correcta ? '✓ ' + nombreTecnica(n.tecnica)
        : fallida ? '✕ No encajaba: prueba con otra técnica'
          : asignada ? '→ ' + nombreTecnica(asignada)
            : 'Sin emparejar';
      return el('li', {},
        el('button', {
          type: 'button',
          class: 'item noticia-item' + (correcta ? ' correcta' : fallida ? ' incorrecta' : ''),
          'aria-pressed': temporal.seleccion === i ? 'true' : 'false',
          disabled: correcta, draggable: correcta ? false : 'true', 'data-foco': `noticia-${i}`,
          onclick: () => {
            temporal.seleccion = temporal.seleccion === i ? null : i;
            temporal.aviso = null;
            pintar();
            if (temporal.seleccion !== null) {
              anunciar(`Noticia ${numero(i)} seleccionada. Ahora elige su técnica.`);
              // En pantallas estrechas las técnicas quedan debajo: se baja hasta ellas.
              const columna = juego.querySelector('.columna-tecnicas');
              if (columna && columna.getBoundingClientRect().top > window.innerHeight * 0.6) {
                columna.scrollIntoView({ behavior: 'smooth', block: 'start' });
              }
            }
          },
          ondragstart: (evento) => { evento.dataTransfer.setData('text/plain', String(i)); evento.dataTransfer.effectAllowed = 'move'; },
        },
        el('span', { class: 'item-num', 'aria-hidden': 'true' }, dosCifras(numero(i))),
        el('span', {}, el('span', { class: 'solo-lector' }, `Noticia ${numero(i)}: `), n.texto),
        el('span', { class: 'item-estado' }, situacion),
        correcta ? el('span', { class: 'item-explicacion' }, n.explicacion) : null
        )
      );
    });

    const tecnicas = prueba.tecnicas.map((t) => {
      const indice = noticiaEn(t.id);
      const bloqueada = indice !== null && estado.correctas.includes(indice);
      const marcar = (sobre) => (evento) => { evento.currentTarget.classList.toggle('sobre', sobre); };
      return el('li', {},
        el('button', {
          type: 'button', class: 'item tecnica-item' + (bloqueada ? ' correcta' : ''),
          disabled: bloqueada, 'data-foco': `tecnica-${t.id}`,
          onclick: () => {
            if (temporal.seleccion === null) {
              temporal.aviso = 'Primero elige una noticia y después su técnica.';
              pintar();
              anunciar(temporal.aviso);
              return;
            }
            asignar(temporal.seleccion, t.id);
          },
          ondragover: (evento) => { evento.preventDefault(); evento.dataTransfer.dropEffect = 'move'; },
          ondragenter: marcar(true),
          ondragleave: marcar(false),
          ondrop: (evento) => {
            evento.preventDefault();
            const i = Number(evento.dataTransfer.getData('text/plain'));
            if (!Number.isNaN(i)) asignar(i, t.id);
          },
        },
        el('span', {}, t.nombre),
        el('span', { class: 'item-estado' }, indice === null ? 'Libre' : `${bloqueada ? '✓ ' : ''}Noticia ${dosCifras(numero(indice))}`)
        )
      );
    });

    return el('section', { class: 'pantalla' },
      cabecera(`${moduloDe(2)} · Análisis de técnicas`, `${estado.correctas.length} / ${prueba.noticias.length} verificadas`),
      titulo(prueba.titulo),
      el('p', { class: 'instrucciones' }, prueba.instrucciones),
      el('div', { class: 'emparejar' },
        el('div', { class: 'columna' }, el('h3', { class: 'rotulo' }, 'Noticias interceptadas'), el('ul', { class: 'lista-emparejar' }, noticias)),
        el('div', { class: 'columna columna-tecnicas' }, el('h3', { class: 'rotulo' }, 'Técnicas de desinformación'), el('ul', { class: 'lista-emparejar' }, tecnicas))
      ),
      temporal.aviso ? el('p', { class: 'pista-caja', role: 'status' }, temporal.aviso) : null,
      el('div', { class: 'acciones' },
        el('button', { type: 'button', class: 'boton', disabled: !completas, 'data-foco': 'comprobar', onclick: comprobar }, prueba.botonComprobar),
        completas ? null : el('span', { class: 'instrucciones' }, 'Empareja las cinco noticias para poder comprobar.')
      ),
      botonPista(prueba.pista)
    );
  }

  // Infografía: se dibuja con HTML a partir de los «pasos» y, si existe la
  // imagen de assets/, se sustituye por ella en cuanto termina de cargar.
  function infografia(prueba) {
    const imagen = () => el('img', { class: 'infografia-imagen', src: prueba.imagen, alt: prueba.textoAlternativo });
    if (infografiaDisponible === true) return imagen();
    const contenedor = el('div', {},
      el('figure', { class: 'infografia', style: 'margin:0' },
        el('h3', {}, prueba.tituloInfografia),
        el('ol', {}, prueba.pasos.map((p) => el('li', {}, el('strong', {}, p.titulo), el('span', {}, p.texto))))
      )
    );
    if (infografiaDisponible === null && prueba.imagen) {
      const sonda = new Image();
      sonda.onload = () => { infografiaDisponible = true; contenedor.replaceChildren(imagen()); };
      sonda.onerror = () => { infografiaDisponible = false; };
      sonda.src = prueba.imagen;
    }
    return contenedor;
  }

  // Prueba 4: infografía y test de cuatro preguntas.
  function pantallaInfografia() {
    const prueba = C.pruebas.infografia;
    if (!estado.test) {
      comunicar('neutral', prueba.mensaje);
      return el('section', { class: 'pantalla' },
        cabecera(`${moduloDe(3)} · Protocolo de verificación`, 'Lectura'),
        titulo(prueba.titulo),
        el('p', { class: 'instrucciones' }, prueba.instrucciones),
        infografia(prueba),
        el('div', { class: 'acciones' },
          el('button', {
            type: 'button', class: 'boton', 'data-foco': 'ir-test',
            onclick: () => { estado.test = true; estado.paso = 0; temporal = temporalInicial(); guardar(); pintar(); },
          }, prueba.botonTest)
        )
      );
    }
    const pregunta = prueba.preguntas[estado.paso];
    const r = temporal.respuesta;
    comunicar('neutral', prueba.mensajeTest);
    return el('section', { class: 'pantalla' },
      cabecera(`${moduloDe(3)} · Protocolo de verificación`, `Pregunta ${estado.paso + 1} / ${prueba.preguntas.length}`),
      titulo(prueba.titulo),
      el('details', { class: 'ver-infografia' }, el('summary', {}, 'Ver la infografía'), infografia(prueba)),
      el('p', { class: 'pregunta', id: 'pregunta-actual' }, pregunta.texto),
      el('ul', { class: 'opciones', 'aria-labelledby': 'pregunta-actual' },
        pregunta.opciones.map((opcion, i) => {
          const elegida = r && r.indice === i;
          return el('li', {}, el('button', {
            type: 'button', class: 'opcion' + (elegida ? (r.acierto ? ' acierto' : ' fallo') : ''),
            disabled: !!r, 'data-foco': `opcion-${i}`,
            onclick: () => responder(i === pregunta.correcta, { indice: i }),
          },
          el('span', { class: 'opcion-letra', 'aria-hidden': 'true' }, elegida ? (r.acierto ? '✓' : '✕') : 'ABCD'.charAt(i)),
          el('span', {}, opcion)
          ));
        })
      ),
      r ? bloqueRespuesta(r.acierto, pregunta.explicacion, textoSiguiente(estado.paso, prueba.preguntas.length),
        () => siguienteRonda(prueba.preguntas.length, 3)) : botonPista(pregunta.pista)
    );
  }

  // Tras cada prueba: fragmento del código y combustible.
  function pantallaLogro() {
    const indice = estado.logro;
    const ultima = indice === PRUEBAS.length - 1;
    comunicar('contento', C.alien.alSuperarPrueba);
    return el('section', { class: 'pantalla logro' },
      cabecera(`${moduloDe(indice)} · Completado`, `Depósito ${estado.superadas.length * 25} %`),
      titulo(`Combustible al ${estado.superadas.length * 25} %`),
      el('p', { class: 'instrucciones' }, 'Fragmento del código de despegue recuperado:'),
      el('p', {}, el('span', { class: 'fragmento conseguido' }, PRUEBAS[indice].datos.fragmento)),
      el('div', { class: 'acciones' },
        el('button', {
          type: 'button', class: 'boton', 'data-foco': 'siguiente-prueba',
          onclick: () => ir(ultima ? 'despegue' : PRUEBAS[indice + 1].pantalla),
        }, ultima ? 'Ir al código de despegue →' : `${moduloDe(indice + 1)} →`)
      )
    );
  }

  function pantallaDespegue() {
    const d = C.despegue;
    comunicar('contento', d.mensaje);
    const entrada = el('input', {
      id: 'codigo', type: 'text', autocomplete: 'off', autocapitalize: 'characters', spellcheck: 'false',
      'aria-describedby': temporal.errorCodigo ? 'codigo-error' : null,
    });
    return el('section', { class: 'pantalla' },
      cabecera('Secuencia de despegue', 'Depósito 100 %'),
      titulo(d.titulo),
      el('p', { class: 'instrucciones' }, d.texto),
      el('p', {}, 'Fragmentos, en orden: ',
        PRUEBAS.map((p, i) => [i ? ' + ' : '', el('span', { class: 'fragmento conseguido' }, p.datos.fragmento)])),
      el('form', {
        class: 'campo-codigo',
        onsubmit: (evento) => {
          evento.preventDefault();
          if (normalizar(entrada.value) === normalizar(CODIGO)) {
            estado.fin = estado.fin || Date.now();
            ir('final');
            return;
          }
          estado.fallos++;
          temporal.errorCodigo = true;
          temporal.reaccion = { expresion: 'preocupado', texto: azar(C.alien.alFallar) };
          guardar();
          pintar();
          anunciar(d.error);
          const campo = document.getElementById('codigo');
          if (campo) campo.focus();
        },
      },
      el('label', { for: 'codigo' }, 'Código completo'),
      entrada,
      el('button', { type: 'submit', class: 'boton' }, d.boton)
      ),
      temporal.errorCodigo ? el('p', { class: 'error', id: 'codigo-error' }, '✕ ' + d.error) : null
    );
  }

  // Final: el despegue se ve por el parabrisas (la Tierra se aleja y las
  // estrellas se estiran) y después aparece el resumen en la pantalla.
  function pantallaFinal() {
    const f = C.final;
    comunicar('contento', f.mensajeVero);
    const minutos = estado.inicio && estado.fin ? Math.max(1, Math.round((estado.fin - estado.inicio) / 60000)) : null;
    return el('section', { class: 'pantalla' },
      cabecera('Despegue', 'En vuelo'),
      el('p', { class: 'cuenta-atras', 'aria-hidden': 'true' }, '3 · 2 · 1 · Ignición'),
      el('div', { class: 'final-contenido' },
        titulo(f.titulo),
        el('p', { class: 'instrucciones' }, f.mensaje),
        el('ul', { class: 'lista-aprendido' }, f.aprendido.map((t) => el('li', {}, t))),
        el('dl', { class: 'estadisticas' },
          el('div', {}, el('dt', {}, 'Tiempo'), el('dd', {}, minutos ? `${minutos} min` : '—')),
          el('div', {}, el('dt', {}, 'Errores'), el('dd', {}, estado.fallos)),
          el('div', {}, el('dt', {}, 'Pistas'), el('dd', {}, estado.pistas))
        ),
        el('p', { class: 'aviso-simulado' }, f.aviso),
        el('h3', {}, 'Si dudas de una información, consulta a los verificadores'),
        el('ul', { class: 'verificadores' }, f.verificadores.map((v) => el('li', {},
          el('a', { href: v.url, target: '_blank', rel: 'noopener noreferrer' }, v.nombre, el('span', { class: 'solo-lector' }, ' (se abre en una pestaña nueva)'))
        ))),
        el('p', { class: 'creditos' }, f.creditos),
        el('div', { class: 'acciones' },
          el('button', { type: 'button', class: 'boton boton-secundario', onclick: reiniciar }, f.botonReiniciar)
        )
      )
    );
  }

  // ---------------------------------------------------------------------------
  // Instrumentos: depósito, código, estado y temporizador
  // ---------------------------------------------------------------------------
  function actualizarInstrumentos() {
    const porcentaje = estado.superadas.length * 25;
    const deposito = document.getElementById('deposito');
    deposito.style.setProperty('--nivel', porcentaje + '%');
    document.getElementById('deposito-porcentaje').textContent = porcentaje + ' %';
    deposito.setAttribute('aria-valuenow', porcentaje);
    deposito.setAttribute('aria-valuetext', `${porcentaje} % del depósito lleno`);
    if (combustibleMostrado !== null && porcentaje > combustibleMostrado) {
      deposito.classList.remove('llenando');
      void deposito.offsetWidth; // reinicia la animación
      deposito.classList.add('llenando');
    }
    combustibleMostrado = porcentaje;

    document.getElementById('codigo-fragmentos').replaceChildren(...PRUEBAS.map((p, i) => {
      const conseguido = estado.superadas.includes(i);
      return el('span', {
        class: 'fragmento' + (conseguido ? ' conseguido' : ''),
        'aria-label': conseguido ? `Fragmento ${i + 1}: ${p.datos.fragmento}` : `Fragmento ${i + 1}: sin recuperar`,
      }, conseguido ? p.datos.fragmento : '•'.repeat(p.datos.fragmento.length));
    }));

    // Estado de la nave: piloto rojo, ámbar o verde y su texto.
    const enVuelo = estado.pantalla === 'final';
    const situacion = enVuelo ? ['listo', 'En vuelo']
      : porcentaje === 100 ? ['listo', 'Listo para despegar']
        : porcentaje > 0 ? ['estable', 'Estable']
          : ['critico', 'Crítico'];
    document.body.dataset.estado = situacion[0];
    document.getElementById('estado-nave').textContent = situacion[1];
    document.body.classList.toggle('despegando', enVuelo);

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
    caja.textContent = restante > 0 ? `T-${min}:${dosCifras(seg)}` : 'Tiempo agotado';
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
    p1: pantallaCualEsVerdad, p2: pantallaVerdadOBulo, p3: pantallaEmparejar, p4: pantallaInfografia,
    logro: pantallaLogro, despegue: pantallaDespegue, final: pantallaFinal,
  };

  function enfocar(selector) {
    const destino = juego.querySelector(selector);
    if (destino) destino.focus();
    return !!destino;
  }

  function pintar() {
    // Si el foco estaba en un control que se vuelve a dibujar, se devuelve a su copia nueva.
    const conFoco = document.activeElement && document.activeElement.getAttribute('data-foco');
    try {
      const pantalla = PANTALLAS[estado.pantalla] || pantallaPortada;
      juego.replaceChildren(pantalla());
    } catch (error) {
      juego.innerHTML = '<p class="aviso-carga">Hay un problema con el contenido de <strong>content.js</strong> ' +
        '(¿falta algún campo en la prueba actual?). Pulsa «Reiniciar» o revisa el último cambio.</p>';
      console.error(error);
    }
    pintarCanal();
    actualizarInstrumentos();

    const vista = `${estado.pantalla}:${estado.paso}:${estado.test}`;
    if (vista !== ultimaVista) {
      const primeraVez = ultimaVista === null;
      ultimaVista = vista;
      if (!primeraVez) {
        const cabeceraPantalla = juego.querySelector('[data-enfocar]');
        if (cabeceraPantalla) cabeceraPantalla.focus({ preventScroll: true });
        // En pantalla grande el juego se desplaza dentro del monitor; en móvil, la página entera.
        juego.scrollTop = 0;
        if (getComputedStyle(document.body).overflow !== 'hidden') juego.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
    pintar();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  // ---------------------------------------------------------------------------
  // Mandos de la cabina y arranque
  // ---------------------------------------------------------------------------
  const botonTexto = document.getElementById('boton-texto');
  function aplicarTextoGrande(activo) {
    document.documentElement.classList.toggle('texto-grande', activo);
    botonTexto.setAttribute('aria-pressed', activo ? 'true' : 'false');
  }
  try { aplicarTextoGrande(localStorage.getItem(CLAVE_TEXTO) === '1'); } catch (e) { /* sin almacenamiento */ }
  botonTexto.addEventListener('click', () => {
    const activo = botonTexto.getAttribute('aria-pressed') !== 'true';
    aplicarTextoGrande(activo);
    try { localStorage.setItem(CLAVE_TEXTO, activo ? '1' : '0'); } catch (e) { /* sin almacenamiento */ }
  });
  document.getElementById('boton-reiniciar').addEventListener('click', reiniciar);

  pintar();
})();
