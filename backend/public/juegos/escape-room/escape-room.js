// Escape room «Ceuta Educadora 2030: Misión Verificación IA».
// Todo ocurre en el navegador: los textos y las claves salen de config.json y el progreso
// solo se guarda en el localStorage de quien juega. No se envía nada al servidor.
(function () {
  'use strict';

  const raiz = document.getElementById('escape-room');
  if (!raiz) return;

  const CLAVE_PROGRESO = 'escape-room-ceuta-2030';
  let config = null;
  let estado = null;

  // ---------- Utilidades ----------

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

  // Devuelve el párrafo con las expresiones de «resaltar» envueltas en <mark>.
  function textoResaltado(texto, expresiones) {
    if (!expresiones || !expresiones.length) return [texto];
    const escapadas = expresiones.map((e) => e.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
    const patron = new RegExp('(' + escapadas.join('|') + ')', 'gi');
    return texto.split(patron).filter(Boolean).map((trozo) =>
      expresiones.some((e) => e.toLowerCase() === trozo.toLowerCase()) ? el('mark', {}, trozo) : trozo
    );
  }

  function estadoInicial() {
    return { pantalla: 'intro', indice: 0, resueltos: [], fallos: {}, caidas: 0, verificado: false, inicio: null, fin: null };
  }

  function cargarProgreso() {
    try {
      const guardado = JSON.parse(localStorage.getItem(CLAVE_PROGRESO));
      if (guardado && guardado.version === config.version) return Object.assign(estadoInicial(), guardado.estado);
    } catch (e) { /* sin almacenamiento disponible: se empieza de cero */ }
    return estadoInicial();
  }

  function guardarProgreso() {
    try {
      localStorage.setItem(CLAVE_PROGRESO, JSON.stringify({ version: config.version, estado }));
    } catch (e) { /* modo privado o almacenamiento bloqueado */ }
  }

  function cambiar(cambios) {
    Object.assign(estado, cambios);
    guardarProgreso();
    pintar();
  }

  function reiniciar() {
    if (!confirm('¿Seguro que quieres empezar la misión desde el principio?')) return;
    estado = estadoInicial();
    guardarProgreso();
    pintar();
  }

  // Solo se mueve el foco al cambiar de pantalla o de expediente, no al pedir una pista.
  let ultimaVista = null;
  function enfocarTitulo() {
    const vista = estado.pantalla + ':' + estado.indice + ':' + estado.resueltos.length + ':' + estado.verificado;
    if (ultimaVista === null || vista === ultimaVista) {
      ultimaVista = vista;
      // Tras descartar una opción, el botón pulsado ya no existe: el foco pasa a la siguiente disponible.
      if (document.activeElement === document.body) {
        const siguiente = raiz.querySelector('.er-opcion:not(:disabled)');
        if (siguiente) siguiente.focus();
      }
      return;
    }
    ultimaVista = vista;
    const titulo = raiz.querySelector('[data-enfocar]');
    if (titulo) titulo.focus();
    raiz.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // ---------- Piezas comunes ----------

  function barraTerminal() {
    const total = config.enigmas.length;
    return el('div', { class: 'er-barra' },
      el('span', { class: 'er-luces', 'aria-hidden': 'true' }, el('i'), el('i'), el('i')),
      el('span', { class: 'er-barra-titulo' }, config.terminal),
      estado.pantalla === 'enigma'
        ? el('span', { class: 'er-barra-progreso' }, `Expediente ${estado.indice + 1} de ${total}`)
        : null
    );
  }

  function progreso() {
    return el('ol', { class: 'er-progreso', 'aria-label': 'Progreso de la misión' },
      config.enigmas.map((enigma, i) => {
        const hecho = estado.resueltos.includes(enigma.id);
        const actual = estado.pantalla === 'enigma' && i === estado.indice;
        return el('li', { class: (hecho ? 'hecho ' : '') + (actual ? 'actual' : ''), 'aria-current': actual ? 'step' : false },
          el('span', { class: 'er-progreso-num' }, hecho ? '✓' : i + 1),
          el('span', { class: 'er-progreso-txt' }, enigma.titulo)
        );
      })
    );
  }

  function documento(enigma, resuelto) {
    const d = enigma.documento;
    const resaltar = resuelto ? enigma.resaltar : null;
    return el('article', { class: `er-doc er-doc-${d.formato}`, 'aria-label': `Documento del expediente ${enigma.codigo}` },
      el('span', { class: 'er-marca', 'aria-hidden': 'true' }, 'SIMULACIÓN'),
      el('p', { class: 'er-doc-cabecera' }, d.cabecera),
      el('h3', { class: 'er-doc-titular' }, d.titular),
      d.cuerpo.map((parrafo) => el('p', {}, textoResaltado(parrafo, resaltar))),
      d.pie ? el('p', { class: 'er-doc-pie' }, d.pie) : null,
      el('p', { class: 'er-doc-aviso' }, 'Contenido ficticio creado para esta actividad educativa.')
    );
  }

  // ---------- Pantallas ----------

  function pantallaIntro() {
    const empezado = estado.inicio !== null;
    return el('div', { class: 'er-pantalla er-intro' },
      el('p', { class: 'er-prompt' }, '> conexión segura establecida'),
      el('h2', { tabindex: '-1', 'data-enfocar': true }, config.titulo),
      config.intro.parrafos.map((p) => el('p', {}, p)),
      el('p', { class: 'er-meta' }, config.intro.duracion),
      el('div', { class: 'er-acciones' },
        el('button', {
          type: 'button',
          class: 'btn btn-accent',
          onclick: () => cambiar({ pantalla: 'enigma', inicio: estado.inicio || Date.now() }),
        }, empezado ? 'Continuar la auditoría' : config.intro.boton),
        empezado ? el('button', { type: 'button', class: 'btn btn-ghost', onclick: reiniciar }, 'Empezar de cero') : null
      ),
      el('p', { class: 'er-aviso' }, config.aviso)
    );
  }

  function pantallaEnigma() {
    const enigma = config.enigmas[estado.indice];
    const resuelto = estado.resueltos.includes(enigma.id);
    const esTrampa = enigma.tipo === 'trampa';
    const bloqueadoPorTrampa = esTrampa && !estado.verificado;

    const panel = el('div', { class: 'er-panel' },
      el('p', { class: 'er-expediente' }, `${enigma.codigo} · ${enigma.minutos} min`),
      el('h2', { tabindex: '-1', 'data-enfocar': true }, enigma.titulo)
    );

    if (bloqueadoPorTrampa) {
      panel.append(bloqueTrampa(enigma));
    } else {
      if (esTrampa) panel.append(el('div', { class: 'er-ok', role: 'status' }, enigma.trampa.verificacion));
      if (enigma.lecturaLateral) panel.append(bloqueLecturaLateral(enigma.lecturaLateral));
      panel.append(resuelto ? bloqueResuelto(enigma) : bloqueOpciones(enigma));
    }

    return el('div', { class: 'er-pantalla' },
      progreso(),
      el('div', { class: 'er-enigma' + (bloqueadoPorTrampa ? ' er-alarma' : '') },
        documento(enigma, resuelto),
        panel
      )
    );
  }

  function bloqueLecturaLateral(lectura) {
    return el('div', { class: 'er-lateral' },
      el('p', { class: 'kicker' }, 'Tu misión'),
      el('p', {}, lectura.texto),
      lectura.busquedas && lectura.busquedas.length
        ? el('ul', { class: 'er-busquedas' },
            lectura.busquedas.map((termino) => el('li', {},
              el('a', {
                href: 'https://duckduckgo.com/?q=' + encodeURIComponent('"' + termino + '"'),
                target: '_blank',
                rel: 'noopener noreferrer',
              }, `Buscar «${termino}»`, el('span', { class: 'sr-only' }, ' (se abre en una pestaña nueva)'))
            ))
          )
        : null
    );
  }

  function bloqueTrampa(enigma) {
    const t = enigma.trampa;
    const error = estado.caidas > 0
      ? el('div', { class: 'er-error', role: 'alert' }, t.error, ' ', el('strong', {}, 'Vuelve a mirar el mensaje antes de decidir.'))
      : null;
    return el('div', { class: 'er-trampa' },
      el('p', { class: 'er-sirena', role: 'alert' }, t.aviso),
      el('p', {}, 'Acaba de llegar este mensaje al terminal. ¿Qué haces?'),
      error,
      el('div', { class: 'er-acciones' },
        el('button', { type: 'button', class: 'btn btn-accent', onclick: () => cambiar({ caidas: estado.caidas + 1 }) }, t.botonCompartir),
        el('button', { type: 'button', class: 'btn btn-ghost', onclick: () => cambiar({ verificado: true }) }, t.botonPausar)
      )
    );
  }

  // Cada opción incorrecta se queda marcada con su explicación, que hace de pista para la siguiente.
  function bloqueOpciones(enigma) {
    const descartadas = estado.fallos[enigma.id] || [];
    return el('div', { class: 'er-opciones-bloque' },
      el('p', { class: 'er-pregunta', id: 'er-pregunta' }, enigma.pregunta),
      el('ul', { class: 'er-opciones', 'aria-labelledby': 'er-pregunta' },
        enigma.opciones.map((opcion, i) => {
          const descartada = descartadas.includes(i);
          return el('li', {},
            el('button', {
              type: 'button',
              class: 'er-opcion',
              disabled: descartada,
              onclick: () => opcion.correcta
                ? cambiar({ resueltos: estado.resueltos.concat(enigma.id) })
                : cambiar({ fallos: Object.assign({}, estado.fallos, { [enigma.id]: descartadas.concat(i) }) }),
            },
              el('span', { class: 'er-opcion-letra', 'aria-hidden': 'true' }, descartada ? '✕' : 'ABCD'[i]),
              el('span', {}, opcion.texto, descartada ? el('span', { class: 'sr-only' }, ' (descartada)') : null)
            ),
            descartada ? el('p', { class: 'er-motivo', role: 'status' }, opcion.motivo) : null
          );
        })
      )
    );
  }

  function bloqueResuelto(enigma) {
    const ultimo = estado.indice === config.enigmas.length - 1;
    return el('div', { class: 'er-resuelto' },
      el('div', { class: 'er-ok', role: 'status' }, el('strong', {}, '¡Diagnóstico correcto! '), 'Clave de desbloqueo:', el('span', { class: 'er-clave' }, enigma.clave)),
      el('p', {}, enigma.explicacion),
      el('button', {
        type: 'button',
        class: 'btn btn-accent',
        onclick: () => ultimo
          ? cambiar({ pantalla: 'final', fin: estado.fin || Date.now() })
          : cambiar({ indice: estado.indice + 1 }),
      }, ultimo ? 'Restablecer el canal oficial' : 'Siguiente expediente')
    );
  }

  function pantallaFinal() {
    const f = config.final;
    const minutos = estado.inicio && estado.fin ? Math.max(1, Math.round((estado.fin - estado.inicio) / 60000)) : null;
    const totalFallos = Object.values(estado.fallos).reduce((total, lista) => total + lista.length, 0);
    const nombre = el('input', { id: 'er-nombre', type: 'text', autocomplete: 'off', maxlength: '40', placeholder: 'Opcional' });
    const vista = el('div', { class: 'er-cert-vista', 'aria-hidden': 'true' }, certificado(''));
    nombre.addEventListener('input', () => vista.replaceChildren(certificado(nombre.value)));

    return el('div', { class: 'er-pantalla er-final' },
      progreso(),
      el('p', { class: 'er-prompt' }, '> canal oficial: SEGURO'),
      el('h2', { tabindex: '-1', 'data-enfocar': true }, f.titulo),
      el('p', { class: 'lead' }, f.mensaje),

      el('dl', { class: 'er-informe', 'aria-label': 'Tu informe de auditoría' },
        el('div', {}, el('dt', {}, 'Expedientes'), el('dd', {}, `${estado.resueltos.length} de ${config.enigmas.length}`)),
        minutos ? el('div', {}, el('dt', {}, 'Tiempo'), el('dd', {}, `${minutos} min`)) : null,
        el('div', {}, el('dt', {}, 'Respuestas descartadas'), el('dd', {}, totalFallos)),
        el('div', {}, el('dt', {}, 'Veces que compartiste el desmentido falso'), el('dd', {}, estado.caidas))
      ),
      estado.caidas > 0
        ? el('p', { class: 'er-nota' }, 'Caer en la trampa es lo habitual: la prisa por corregir un bulo es justo lo que aprovecha la desinformación de segunda generación. Que lo hayas vivido aquí es la mejor vacuna.')
        : el('p', { class: 'er-nota' }, 'No caíste en el desmentido falso. Aun así, recuerda que nadie es inmune: solemos pensar que solo los demás se dejan engañar (el efecto del tercer personaje).'),

      el('h3', {}, 'Decálogo para el bienestar digital'),
      el('ol', { class: 'er-decalogo' },
        f.decalogo.map((regla) => el('li', {}, el('strong', {}, regla.titulo), el('span', {}, regla.texto)))
      ),

      el('h3', {}, 'Para reflexionar'),
      el('ul', { class: 'er-reflexion' }, f.reflexion.map((p) => el('li', {}, p))),

      el('div', { class: 'er-certificado-form' },
        el('h3', {}, 'Tu certificado'),
        el('p', {}, 'Escribe el nombre que quieres que aparezca. No se guarda ni se envía a ningún sitio.'),
        el('div', { class: 'field' }, el('label', { for: 'er-nombre' }, 'Nombre'), nombre),
        vista,
        el('div', { class: 'er-acciones' },
          el('button', { type: 'button', class: 'btn btn-accent', onclick: () => imprimirCertificado(nombre.value) }, 'Descargar certificado (PDF)'),
          el('button', { type: 'button', class: 'btn btn-ghost', onclick: reiniciar }, 'Volver a jugar')
        )
      )
    );
  }

  // Mismo diseño para la vista previa y para la hoja impresa (A4 apaisado, ver estilos .er-cert).
  function certificado(nombre) {
    const fecha = new Date(estado.fin || Date.now()).toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });
    return el('div', { class: 'er-cert' },
      el('div', { class: 'er-cert-lado' },
        el('img', { src: raiz.dataset.logo, alt: 'Cátedra Ceuta-IATEB' }),
        el('p', { class: 'er-cert-dia' }, el('strong', {}, '30 nov'), 'Día Internacional de la Ciudad Educadora')
      ),
      el('div', { class: 'er-cert-cuerpo' },
        el('div', { class: 'er-cert-principal' },
          el('p', { class: 'er-cert-tipo' }, 'Certificado cívico'),
          el('h2', {}, 'Auditor/a Cívico/a Digital'),
          el('p', { class: 'er-cert-otorga' }, 'Se otorga a'),
          el('p', { class: 'er-cert-nombre' + (nombre.trim().length > 22 ? ' largo' : '') }, nombre.trim() || 'Auditor/a Cívico/a'),
          el('p', { class: 'er-cert-texto' }, 'por completar la misión ', el('em', {}, config.titulo),
            ' y demostrar su compromiso con la verificación de la información y el bienestar digital de la ciudadanía.'),
          el('ul', { class: 'er-cert-competencias' }, config.enigmas.map((enigma) => el('li', {}, enigma.competencia)))
        ),
        el('div', { class: 'er-cert-pie' },
          el('p', {}, el('span', {}, 'Fecha'), `Ceuta, ${fecha}`),
          el('p', {}, el('span', {}, 'Expide'), 'Cátedra Ceuta-IATEB · UGR – Ciudad Autónoma de Ceuta'),
          el('div', { class: 'er-cert-sello' }, 'CEUTA', el('br'), 'EDUCADORA', el('br'), '2030')
        )
      )
    );
  }

  // Se imprime una copia colgada directamente de <body>; los estilos de impresión ocultan todo lo demás.
  function imprimirCertificado(nombre) {
    let hoja = document.getElementById('er-certificado-impresion');
    if (!hoja) {
      hoja = el('div', { id: 'er-certificado-impresion', 'aria-hidden': 'true' });
      document.body.append(hoja);
    }
    hoja.replaceChildren(certificado(nombre));
    window.print();
  }

  function pintar() {
    const pantallas = { intro: pantallaIntro, enigma: pantallaEnigma, final: pantallaFinal };
    raiz.replaceChildren(barraTerminal(), pantallas[estado.pantalla]());
    enfocarTitulo();
  }

  // ---------- Arranque ----------

  fetch(raiz.dataset.config, { cache: 'no-cache' })
    .then((respuesta) => {
      if (!respuesta.ok) throw new Error('HTTP ' + respuesta.status);
      return respuesta.json();
    })
    .then((datos) => {
      config = datos;
      estado = cargarProgreso();
      if (estado.indice >= config.enigmas.length) estado = estadoInicial();
      pintar();
    })
    .catch(() => {
      raiz.replaceChildren(el('p', { class: 'er-error', role: 'alert' }, 'No se ha podido cargar el escape room. Recarga la página para intentarlo de nuevo.'));
    });
})();
