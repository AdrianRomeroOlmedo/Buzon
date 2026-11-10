/* =============================================================================
   LÓGICA DEL JUEGO · Misión Combustible de Verdad
   No hace falta tocar este archivo para cambiar textos: todo el contenido está
   en content.js. Aquí solo está el funcionamiento de las pantallas y pruebas.
   ========================================================================== */
(function () {
  'use strict';

  const C = window.CONTENIDO;
  const juego = document.getElementById('juego');
  const anuncio = document.getElementById('anuncio');

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

  // ---------------------------------------------------------------------------
  // Dibujos en SVG: el alien (con sus expresiones) y la nave
  // ---------------------------------------------------------------------------
  const EXPRESIONES = { neutral: 'tranquilo', contento: 'contento', preocupado: 'preocupado', sorprendido: 'sorprendido' };

  function alienSVG(expresion, grande) {
    const tinta = '#1b1530';
    const ojoAbierto = (x, pupila) =>
      `<ellipse cx="${x}" cy="92" rx="13" ry="15" fill="#fff"/>` +
      `<circle cx="${x + 2}" cy="${95 + pupila}" r="7" fill="${tinta}"/>` +
      `<circle cx="${x + 4}" cy="${92 + pupila}" r="2.5" fill="#fff"/>`;
    const partes = {
      neutral: {
        ojos: ojoAbierto(60, 0) + ojoAbierto(100, 0),
        boca: `<path d="M68 121 Q80 129 92 121" stroke="${tinta}" stroke-width="4" fill="none" stroke-linecap="round"/>`,
        brazos: 'abajo',
      },
      contento: {
        ojos: `<path d="M48 95 Q60 80 72 95" stroke="${tinta}" stroke-width="5" fill="none" stroke-linecap="round"/>` +
              `<path d="M88 95 Q100 80 112 95" stroke="${tinta}" stroke-width="5" fill="none" stroke-linecap="round"/>`,
        boca: `<path d="M62 115 Q80 142 98 115 Z" fill="${tinta}"/><path d="M71 127 Q80 134 89 127 Q80 124 71 127Z" fill="#ff7aa8"/>`,
        brazos: 'arriba',
      },
      preocupado: {
        ojos: ojoAbierto(60, 3) + ojoAbierto(100, 3) +
              `<path d="M46 80 L70 71" stroke="${tinta}" stroke-width="4" stroke-linecap="round"/>` +
              `<path d="M90 71 L114 80" stroke="${tinta}" stroke-width="4" stroke-linecap="round"/>`,
        boca: `<path d="M66 128 Q73 120 80 126 Q87 132 94 124" stroke="${tinta}" stroke-width="4" fill="none" stroke-linecap="round"/>`,
        brazos: 'abajo',
      },
      sorprendido: {
        ojos: `<ellipse cx="60" cy="90" rx="15" ry="18" fill="#fff"/><circle cx="60" cy="91" r="5" fill="${tinta}"/>` +
              `<ellipse cx="100" cy="90" rx="15" ry="18" fill="#fff"/><circle cx="100" cy="91" r="5" fill="${tinta}"/>` +
              `<path d="M47 66 Q60 58 73 66" stroke="${tinta}" stroke-width="4" fill="none" stroke-linecap="round"/>` +
              `<path d="M87 66 Q100 58 113 66" stroke="${tinta}" stroke-width="4" fill="none" stroke-linecap="round"/>`,
        boca: `<ellipse cx="80" cy="127" rx="7" ry="9" fill="${tinta}"/>`,
        brazos: 'arriba',
      },
    };
    const p = partes[expresion] || partes.neutral;
    const brazos = p.brazos === 'arriba'
      ? '<ellipse cx="18" cy="86" rx="10" ry="18" fill="#9d6cf0" transform="rotate(-30 18 86)"/>' +
        '<ellipse cx="142" cy="86" rx="10" ry="18" fill="#9d6cf0" transform="rotate(30 142 86)"/>'
      : '<ellipse cx="20" cy="122" rx="10" ry="18" fill="#9d6cf0" transform="rotate(25 20 122)"/>' +
        '<ellipse cx="140" cy="122" rx="10" ry="18" fill="#9d6cf0" transform="rotate(-25 140 122)"/>';
    const etiqueta = `${escapar(C.alien.nombre)}, el extraterrestre, está ${EXPRESIONES[expresion] || EXPRESIONES.neutral}`;
    return `<svg class="alien${grande ? ' grande' : ''}" viewBox="0 0 160 180" role="img" aria-label="${etiqueta}">` +
      '<g class="alien-cuerpo">' +
      '<path d="M80 42 C77 30 87 22 84 12" stroke="var(--alien)" stroke-width="5" fill="none" stroke-linecap="round"/>' +
      '<path d="M84 1 L87.5 8 L95 9 L89.5 14 L91 21.5 L84 17.7 L77 21.5 L78.5 14 L73 9 L80.5 8 Z" fill="#ffcf3f"/>' +
      brazos +
      '<ellipse cx="58" cy="168" rx="16" ry="9" fill="#9d6cf0"/><ellipse cx="102" cy="168" rx="16" ry="9" fill="#9d6cf0"/>' +
      '<path d="M80 38 C130 38 146 80 146 112 C146 150 118 166 80 166 C42 166 14 150 14 112 C14 80 30 38 80 38Z" fill="var(--alien)"/>' +
      '<ellipse cx="80" cy="140" rx="36" ry="20" fill="#d9c4ff"/>' +
      '<circle cx="40" cy="74" r="5" fill="#9d6cf0"/><circle cx="121" cy="66" r="4" fill="#9d6cf0"/><circle cx="130" cy="86" r="3" fill="#9d6cf0"/>' +
      '<ellipse cx="42" cy="112" rx="8" ry="5" fill="#ff9ec4" opacity=".85"/><ellipse cx="118" cy="112" rx="8" ry="5" fill="#ff9ec4" opacity=".85"/>' +
      p.ojos + p.boca +
      '</g></svg>';
  }

  function naveSVG(modo) {
    const averiada = modo === 'averiada';
    const despegando = modo === 'despegando';
    const luz = (x, encendida) => `<circle cx="${x}" cy="130" r="6" fill="${encendida ? '#ffcf3f' : '#6d7393'}"/>`;
    const etiqueta = averiada ? 'La nave averiada, echando humo' : despegando ? 'La nave despegando' : 'La nave';
    return `<svg class="nave" viewBox="0 0 220 220" role="img" aria-label="${etiqueta}">` +
      (despegando ? '<g class="llama"><path d="M84 150 Q110 225 136 150Z" fill="#ffcf3f"/><path d="M95 150 Q110 200 125 150Z" fill="#ff7a59"/></g>' : '') +
      (averiada ? '<g class="humo"><circle cx="160" cy="100" r="10" fill="#9aa0bf"/><circle cx="168" cy="96" r="12" fill="#b4b9d4"/><circle cx="156" cy="92" r="9" fill="#9aa0bf"/></g>' : '') +
      (despegando ? '' : '<path d="M62 140 L44 186 M158 140 L176 186" stroke="#8a93c9" stroke-width="7" stroke-linecap="round"/>') +
      '<path d="M66 116 C66 58 154 58 154 116Z" fill="#9fe3ff" opacity=".9"/>' +
      // Pequeño piloto dentro de la cúpula
      '<circle cx="110" cy="100" r="17" fill="var(--alien)"/><circle cx="104" cy="98" r="4" fill="#1b1530"/><circle cx="116" cy="98" r="4" fill="#1b1530"/>' +
      '<path d="M66 116 C66 58 154 58 154 116Z" fill="none" stroke="#fff" stroke-width="3" opacity=".6"/>' +
      '<ellipse cx="110" cy="134" rx="98" ry="27" fill="#aab1d6"/><ellipse cx="110" cy="124" rx="98" ry="22" fill="#e6e9f8"/>' +
      luz(40, !averiada) + luz(75, true) + luz(110, !averiada) + luz(145, true) + luz(180, !averiada) +
      (averiada ? '<path d="M150 110 l9 7 -7 5 9 7" stroke="#4b4f6b" stroke-width="3" fill="none" stroke-linecap="round"/>' : '') +
      '</svg>';
  }

  function svg(html) {
    const caja = el('span', { style: 'display:contents' });
    caja.innerHTML = html;
    return caja;
  }

  // El alien con su bocadillo. Si hay una reacción pendiente (acierto, fallo,
  // pista), se muestra esa; si no, el texto por defecto de la pantalla.
  function escena(expresion, texto) {
    const r = temporal.reaccion;
    return el('div', { class: 'escena' },
      svg(alienSVG(r ? r.expresion : expresion)),
      el('p', { class: 'bocadillo' }, r ? r.texto : texto)
    );
  }

  // ---------------------------------------------------------------------------
  // Piezas comunes
  // ---------------------------------------------------------------------------

  // Tarjeta que imita un tuit, un WhatsApp, un SMS o un titular de prensa.
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

  function contador(texto) {
    return el('p', { class: 'contador-ronda' }, texto);
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
          enfocar('[data-foco="pista-texto"]') || anunciar('Pista: ' + texto);
        },
      }, '💡 Pedir una pista'),
    ];
  }

  // Bloque de explicación tras responder: siempre explica, se acierte o no.
  function bloqueRespuesta(acierto, explicacion, textoSiguiente, alSiguiente) {
    return el('div', { class: 'respuesta ' + (acierto ? 'acierto' : 'fallo'), tabindex: '-1', 'data-respuesta': '' },
      el('p', { class: 'respuesta-titulo' }, acierto ? '✓ ¡Correcto!' : '✕ No es correcto'),
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
    anunciar(acierto ? 'Correcto.' : 'No es correcto.');
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
    anunciar(`Prueba superada. Depósito al ${estado.superadas.length * 25} por ciento.`);
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
    return paso + 1 < total ? 'Siguiente →' : 'Completar la prueba →';
  }

  // ---------------------------------------------------------------------------
  // Pantallas
  // ---------------------------------------------------------------------------
  function pantallaPortada() {
    const p = C.portada;
    return el('section', { class: 'pantalla portada' },
      el('p', { class: 'antetitulo' }, p.antetitulo),
      el('h1', { tabindex: '-1', 'data-enfocar': '' }, p.titulo),
      el('p', { class: 'subtitulo' }, p.subtitulo),
      el('div', { class: 'portada-escena' }, svg(alienSVG('preocupado', true)), svg(naveSVG('averiada'))),
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

  function pantallaIntro() {
    const dialogos = C.introduccion.dialogos;
    const visibles = Math.min(estado.paso + 1, dialogos.length);
    const ultimo = visibles === dialogos.length;
    return el('section', { class: 'pantalla' },
      titulo(`${C.alien.nombre} necesita tu ayuda`),
      el('div', { class: 'dialogos' },
        dialogos.slice(0, visibles).map((texto, i) => el('div', { class: 'escena' },
          svg(alienSVG(i === 0 ? 'sorprendido' : i === dialogos.length - 1 ? 'contento' : 'preocupado')),
          el('p', { class: 'bocadillo', 'data-foco': `dialogo-${i}`, tabindex: '-1' }, texto)
        ))
      ),
      el('div', { class: 'acciones' },
        ultimo
          ? el('button', { type: 'button', class: 'boton', 'data-foco': 'ayudar', onclick: () => ir('p1') }, C.introduccion.boton)
          : el('button', {
            type: 'button', class: 'boton', 'data-foco': 'continuar',
            onclick: () => { estado.paso++; guardar(); pintar(); enfocar('[data-foco="continuar"], [data-foco="ayudar"]'); },
          }, 'Continuar →')
      )
    );
  }

  // Prueba 1: dos tarjetas, una verdadera.
  function pantallaCualEsVerdad() {
    const prueba = C.pruebas.cualEsVerdad;
    const ronda = prueba.rondas[estado.paso];
    const r = temporal.respuesta;
    return el('section', { class: 'pantalla' },
      contador(`Ronda ${estado.paso + 1} de ${prueba.rondas.length}${ronda.tema ? ' · ' + ronda.tema : ''}`),
      titulo(prueba.titulo),
      escena('neutral', prueba.instrucciones),
      el('div', { class: 'parejas-tarjetas' },
        ronda.tarjetas.map((t, i) => {
          const elegida = r && r.indice === i;
          return el('button', {
            type: 'button', class: 'tarjeta-boton' + (elegida ? (r.acierto ? ' acierto' : ' fallo') : ''),
            disabled: !!r, 'data-foco': `tarjeta-${i}`,
            'aria-label': `Opción ${i + 1}: ${t.texto}`,
            onclick: () => responder(!!t.verdadera, { indice: i }),
          },
          tarjeta(t),
          elegida ? el('span', { class: 'sello ' + (t.verdadera ? 'sello-verdad' : 'sello-bulo') }, t.verdadera ? '✓ Verdad' : '✕ Bulo') : null
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
    const boton = (esVerdad) => el('button', {
      type: 'button',
      class: `boton boton-veredicto ${esVerdad ? 'boton-verdad' : 'boton-bulo'}` + (r && r.dijoVerdad === esVerdad ? ' elegido' : ''),
      disabled: !!r, 'data-foco': esVerdad ? 'verdad' : 'bulo',
      onclick: () => responder(ronda.esVerdad === esVerdad, { dijoVerdad: esVerdad }),
    }, esVerdad ? '✓ ' + prueba.botonVerdad : '✕ ' + prueba.botonBulo);

    return el('section', { class: 'pantalla' },
      contador(`Ronda ${estado.paso + 1} de ${prueba.rondas.length}${ronda.tema ? ' · ' + ronda.tema : ''}`),
      titulo(prueba.titulo),
      escena('neutral', prueba.instrucciones),
      tarjeta(ronda),
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
        el('span', { class: 'item-num', 'aria-hidden': 'true' }, numero(i)),
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
              temporal.aviso = 'Primero toca una noticia y después su técnica.';
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
        el('span', { class: 'item-estado' }, indice === null ? 'Libre' : `${bloqueada ? '✓ ' : ''}Noticia ${numero(indice)}`)
        )
      );
    });

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
      temporal.aviso = `Tienes ${estado.correctas.length} de ${prueba.noticias.length} bien. Las que no encajaban se han soltado (marcadas con ✕): vuelve a emparejarlas.`;
      guardar();
      pintar();
      anunciar(temporal.aviso);
    }

    return el('section', { class: 'pantalla' },
      titulo(prueba.titulo),
      escena('neutral', prueba.instrucciones),
      el('div', { class: 'emparejar' },
        el('div', { class: 'columna' }, el('h3', {}, 'Noticias'), el('ul', { class: 'lista-emparejar' }, noticias)),
        el('div', { class: 'columna columna-tecnicas' }, el('h3', {}, 'Técnicas de desinformación'), el('ul', { class: 'lista-emparejar' }, tecnicas))
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
      return el('section', { class: 'pantalla' },
        titulo(prueba.titulo),
        escena('neutral', prueba.instrucciones),
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
    return el('section', { class: 'pantalla' },
      contador(`Pregunta ${estado.paso + 1} de ${prueba.preguntas.length}`),
      titulo(prueba.titulo),
      escena('neutral', 'Si lo necesitas, puedes volver a mirar la infografía.'),
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
    return el('section', { class: 'pantalla logro' },
      titulo(`¡Depósito al ${estado.superadas.length * 25} %!`),
      el('div', { class: 'escena' }, svg(alienSVG('contento', true)), el('p', { class: 'bocadillo' }, C.alien.alSuperarPrueba)),
      el('p', {}, 'Fragmento del código de despegue:'),
      el('p', {}, el('span', { class: 'fragmento conseguido' }, PRUEBAS[indice].datos.fragmento)),
      el('div', { class: 'acciones' },
        el('button', {
          type: 'button', class: 'boton', 'data-foco': 'siguiente-prueba',
          onclick: () => ir(ultima ? 'despegue' : PRUEBAS[indice + 1].pantalla),
        }, ultima ? 'Ir al código de despegue →' : 'Siguiente prueba →')
      )
    );
  }

  function pantallaDespegue() {
    const d = C.despegue;
    const entrada = el('input', {
      id: 'codigo', type: 'text', autocomplete: 'off', autocapitalize: 'characters', spellcheck: 'false',
      'aria-describedby': temporal.errorCodigo ? 'codigo-error' : null,
    });
    return el('section', { class: 'pantalla' },
      titulo(d.titulo),
      escena('contento', d.texto),
      el('p', {}, 'Tus fragmentos, en orden: ',
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
      el('button', { type: 'submit', class: 'boton' }, '🚀 ' + d.boton)
      ),
      temporal.errorCodigo ? el('p', { class: 'error', id: 'codigo-error' }, '✕ ' + d.error) : null
    );
  }

  function pantallaFinal() {
    const f = C.final;
    const minutos = estado.inicio && estado.fin ? Math.max(1, Math.round((estado.fin - estado.inicio) / 60000)) : null;
    return el('section', { class: 'pantalla' },
      el('div', { class: 'despegue', 'aria-hidden': 'true' }, svg(naveSVG('despegando'))),
      el('div', { class: 'final-contenido' },
        titulo(f.titulo),
        el('p', { class: 'instrucciones' }, f.mensaje),
        el('ul', { class: 'lista-aprendido' }, f.aprendido.map((t) => el('li', {}, t))),
        el('dl', { class: 'estadisticas' },
          el('div', {}, el('dt', {}, 'Tiempo'), el('dd', {}, minutos ? `${minutos} min` : '—')),
          el('div', {}, el('dt', {}, 'Intentos fallidos'), el('dd', {}, estado.fallos)),
          el('div', {}, el('dt', {}, 'Pistas usadas'), el('dd', {}, estado.pistas))
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
  // Cabecera: depósito, código y temporizador
  // ---------------------------------------------------------------------------
  function actualizarCabecera() {
    const porcentaje = estado.superadas.length * 25;
    const deposito = document.getElementById('deposito');
    document.getElementById('deposito-relleno').style.width = porcentaje + '%';
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
        'aria-label': conseguido ? `Fragmento ${i + 1}: ${p.datos.fragmento}` : `Fragmento ${i + 1}: sin conseguir`,
      }, conseguido ? p.datos.fragmento : '•'.repeat(p.datos.fragmento.length));
    }));
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
    caja.textContent = restante > 0 ? `⏱ ${min}:${String(seg).padStart(2, '0')}` : '⏱ ¡Tiempo!';
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
    actualizarCabecera();

    const vista = `${estado.pantalla}:${estado.paso}:${estado.test}`;
    if (vista !== ultimaVista) {
      const primeraVez = ultimaVista === null;
      ultimaVista = vista;
      if (!primeraVez) {
        const cabecera = juego.querySelector('[data-enfocar]');
        if (cabecera) cabecera.focus({ preventScroll: true });
        window.scrollTo({ top: 0, behavior: 'smooth' });
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
  }

  // ---------------------------------------------------------------------------
  // Botones de la cabecera y arranque
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
