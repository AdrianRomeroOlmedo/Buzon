// Escape room «Ceuta Educadora 2030: Misión Verificación IA».
// Se cuenta un suceso en varias fases; en cada una hay tres informaciones (una real y dos bulos)
// y quien juega elige la que cree verdadera. Al final se compara la historia que ha construido
// con lo que pasó de verdad. Todo ocurre en el navegador: los textos salen de config.json y el
// progreso solo se guarda en el localStorage de quien juega. No se envía nada al servidor.
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

  // Orden aleatorio de las tres noticias de cada fase, para que la real no esté siempre en el mismo sitio.
  function barajar(cantidad) {
    const orden = Array.from({ length: cantidad }, (_, i) => i);
    for (let i = orden.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [orden[i], orden[j]] = [orden[j], orden[i]];
    }
    return orden;
  }

  function estadoInicial() {
    return { pantalla: 'intro', indice: 0, orden: {}, elecciones: {}, seleccion: null, inicio: null, fin: null };
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

  function empezar() {
    const orden = {};
    config.fases.forEach((fase) => { orden[fase.id] = barajar(fase.noticias.length); });
    cambiar({ pantalla: 'fase', indice: 0, orden, elecciones: {}, seleccion: null, inicio: Date.now(), fin: null });
  }

  function reiniciar() {
    if (!confirm('¿Seguro que quieres empezar desde el principio?')) return;
    estado = estadoInicial();
    guardarProgreso();
    pintar();
  }

  function noticiaElegida(fase) {
    const i = estado.elecciones[fase.id];
    return i === undefined ? null : fase.noticias[i];
  }

  function noticiaReal(fase) {
    return fase.noticias.find((noticia) => noticia.real);
  }

  function aciertos() {
    return config.fases.filter((fase) => (noticiaElegida(fase) || {}).real).length;
  }

  // Solo se mueve el foco al cambiar de pantalla o de fase, no al seleccionar una noticia.
  let ultimaVista = null;
  function enfocarTitulo() {
    const vista = estado.pantalla + ':' + estado.indice;
    if (ultimaVista === null || vista === ultimaVista) {
      ultimaVista = vista;
      return;
    }
    ultimaVista = vista;
    const titulo = raiz.querySelector('[data-enfocar]');
    if (titulo) titulo.focus();
    raiz.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // ---------- Piezas comunes ----------

  function barraTerminal() {
    return el('div', { class: 'er-barra' },
      el('span', { class: 'er-luces', 'aria-hidden': 'true' }, el('i'), el('i'), el('i')),
      el('span', { class: 'er-barra-titulo' }, config.terminal),
      estado.pantalla === 'fase'
        ? el('span', { class: 'er-barra-progreso' }, `Fase ${estado.indice + 1} de ${config.fases.length}`)
        : null
    );
  }

  function progreso() {
    return el('ol', { class: 'er-progreso', 'aria-label': 'Fases del suceso', style: `--fases:${config.fases.length}` },
      config.fases.map((fase, i) => {
        const hecho = estado.elecciones[fase.id] !== undefined;
        const actual = estado.pantalla === 'fase' && i === estado.indice;
        return el('li', { class: (hecho ? 'hecho ' : '') + (actual ? 'actual' : ''), 'aria-current': actual ? 'step' : false },
          el('span', { class: 'er-progreso-num' }, hecho ? '✓' : i + 1),
          el('span', { class: 'er-progreso-txt' }, fase.momento)
        );
      })
    );
  }

  // Contenido de una noticia según su formato: noticia de un medio, publicación en redes o mensaje reenviado.
  function contenidoNoticia(noticia) {
    if (noticia.formato === 'redes') {
      return [
        el('div', { class: 'er-post-cabecera' },
          el('span', { class: 'er-avatar', 'aria-hidden': 'true' }, noticia.fuente.replace('@', '').charAt(0).toUpperCase()),
          el('span', {}, el('strong', {}, noticia.fuente), noticia.detalle ? el('small', {}, noticia.detalle) : null),
          el('span', { class: 'er-hora' }, noticia.hora)
        ),
        el('p', { class: 'er-titular' }, noticia.titular),
        noticia.imagen ? el('div', { class: 'er-imagen', role: 'img', 'aria-label': noticia.imagen }, noticia.imagen) : null,
        el('p', {}, noticia.texto),
      ];
    }
    if (noticia.formato === 'whatsapp') {
      return [
        el('p', { class: 'er-reenviado' }, '↪ ', noticia.fuente),
        el('div', { class: 'er-burbuja' },
          el('p', { class: 'er-titular' }, noticia.titular),
          el('p', {}, noticia.texto),
          el('span', { class: 'er-hora' }, noticia.hora)
        ),
      ];
    }
    return [
      el('p', { class: 'er-medio' }, el('span', {}, noticia.fuente), el('span', { class: 'er-hora' }, noticia.hora)),
      el('p', { class: 'er-titular' }, noticia.titular),
      el('p', {}, noticia.texto),
    ];
  }

  // ---------- Pantallas ----------

  function pantallaIntro() {
    const s = config.suceso;
    return el('div', { class: 'er-pantalla er-intro' },
      el('p', { class: 'er-prompt' }, `> ${s.momento}`),
      el('h2', { tabindex: '-1', 'data-enfocar': true }, s.titulo),
      s.parrafos.map((p) => el('p', {}, p)),
      el('p', { class: 'er-meta' }, s.duracion),
      el('div', { class: 'er-acciones' },
        el('button', { type: 'button', class: 'btn btn-accent', onclick: empezar }, s.boton)
      ),
      el('p', { class: 'er-aviso' }, config.aviso)
    );
  }

  function pantallaFase() {
    const fase = config.fases[estado.indice];
    const orden = estado.orden[fase.id] || fase.noticias.map((_, i) => i);
    const ultima = estado.indice === config.fases.length - 1;

    const elegir = (i) => cambiar({ seleccion: i });
    const confirmar = () => {
      const elecciones = Object.assign({}, estado.elecciones, { [fase.id]: estado.seleccion });
      if (ultima) cambiar({ elecciones, seleccion: null, pantalla: 'final', fin: Date.now() });
      else cambiar({ elecciones, seleccion: null, indice: estado.indice + 1 });
    };

    return el('div', { class: 'er-pantalla' },
      progreso(),
      el('p', { class: 'er-expediente' }, `${fase.momento} · ${fase.titulo}`),
      el('h2', { tabindex: '-1', 'data-enfocar': true }, fase.pregunta),
      el('p', { class: 'er-instruccion' }, 'Solo una de estas informaciones es verdadera. Fíjate en la fuente, en el tono y en lo que te piden hacer.'),
      el('div', { class: 'er-noticias', role: 'radiogroup', 'aria-label': fase.pregunta },
        orden.map((i) => {
          const noticia = fase.noticias[i];
          const marcada = estado.seleccion === i;
          return el('article', {
            class: `er-noticia er-noticia-${noticia.formato}` + (marcada ? ' marcada' : ''),
            onclick: () => elegir(i),
          },
            contenidoNoticia(noticia),
            el('button', {
              type: 'button', class: 'er-elegir', role: 'radio', 'aria-checked': marcada ? 'true' : 'false',
              onclick: (evento) => { evento.stopPropagation(); elegir(i); },
            }, marcada ? '✓ Esta es la que creo' : 'Creo que esta es la verdadera')
          );
        })
      ),
      el('div', { class: 'er-acciones' },
        el('button', { type: 'button', class: 'btn btn-accent', disabled: estado.seleccion === null, onclick: confirmar },
          ultima ? 'Ver cómo termina la historia' : 'Siguiente fase')
      )
    );
  }

  function pantallaFinal() {
    const f = config.final;
    const total = config.fases.length;
    const acertadas = aciertos();
    const minutos = estado.inicio && estado.fin ? Math.max(1, Math.round((estado.fin - estado.inicio) / 60000)) : null;
    const nombre = el('input', { id: 'er-nombre', type: 'text', autocomplete: 'off', maxlength: '40', placeholder: 'Opcional' });
    const vista = el('div', { class: 'er-cert-vista', 'aria-hidden': 'true' }, certificado(''));
    nombre.addEventListener('input', () => vista.replaceChildren(certificado(nombre.value)));

    const valoracion = acertadas === total
      ? 'Has reconstruido el suceso sin colar ningún bulo. Aun así, nadie es inmune: solemos pensar que solo los demás se dejan engañar (el efecto del tercer personaje).'
      : `${total - acertadas === 1 ? 'Ha bastado un bulo' : `Han bastado ${total - acertadas} bulos`} para que tu historia sea distinta de la real. Así se construye la desinformación: pieza a pieza, cada una creíble por separado.`;

    return el('div', { class: 'er-pantalla er-final' },
      progreso(),
      el('p', { class: 'er-prompt' }, '> fin de la simulación'),
      el('h2', { tabindex: '-1', 'data-enfocar': true }, f.titulo),
      el('p', { class: 'lead' }, `Has identificado ${acertadas} de ${total} informaciones verdaderas${minutos ? ` en ${minutos} min` : ''}.`),
      el('p', { class: 'er-nota' }, valoracion),

      el('div', { class: 'er-narrativas' },
        el('section', { class: 'er-narrativa er-narrativa-tuya', 'aria-labelledby': 'er-tuya' },
          el('h3', { id: 'er-tuya' }, 'La historia que te has creído'),
          el('ol', {}, config.fases.map((fase) => {
            const noticia = noticiaElegida(fase);
            if (!noticia) return null;
            return el('li', { class: noticia.real ? 'real' : 'bulo' },
              el('span', { class: 'er-momento' }, fase.momento),
              el('span', {}, noticia.narrativa),
              el('span', { class: 'er-etiqueta' }, noticia.real ? '✓ Verdad' : '✕ Bulo')
            );
          }))
        ),
        el('section', { class: 'er-narrativa er-narrativa-real', 'aria-labelledby': 'er-real' },
          el('h3', { id: 'er-real' }, 'Lo que pasó de verdad'),
          el('ol', {}, config.fases.map((fase) => el('li', {},
            el('span', { class: 'er-momento' }, fase.momento),
            el('span', {}, noticiaReal(fase).narrativa)
          )))
        )
      ),

      el('h3', {}, 'Fase a fase: cómo se reconocía cada bulo'),
      el('div', { class: 'er-desglose' }, config.fases.map((fase) => {
        const elegida = noticiaElegida(fase);
        return el('details', { class: elegida && elegida.real ? 'acierto' : 'fallo' },
          el('summary', {}, `${fase.momento} · ${fase.pregunta}`, el('span', {}, elegida && elegida.real ? '✓ Acertaste' : '✕ Te la colaron')),
          el('ul', {}, fase.noticias.map((noticia) => el('li', { class: noticia.real ? 'real' : 'bulo' },
            el('p', { class: 'er-desglose-titular' },
              el('span', { class: 'er-etiqueta' }, noticia.real ? 'Verdadera' : 'Bulo'),
              noticia === elegida ? el('span', { class: 'er-etiqueta er-tu-eleccion' }, 'Tu elección') : null,
              noticia.titular
            ),
            el('p', {}, el('em', {}, noticia.fuente), ' — ', noticia.explicacion)
          )))
        );
      })),

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
          el('ul', { class: 'er-cert-competencias' }, config.final.competencias.map((competencia) => el('li', {}, competencia)))
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
    const pantallas = { intro: pantallaIntro, fase: pantallaFase, final: pantallaFinal };
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
      if (estado.indice >= config.fases.length) estado = estadoInicial();
      pintar();
    })
    .catch(() => {
      raiz.replaceChildren(el('p', { class: 'er-error', role: 'alert' }, 'No se ha podido cargar el juego. Recarga la página para intentarlo de nuevo.'));
    });
})();
