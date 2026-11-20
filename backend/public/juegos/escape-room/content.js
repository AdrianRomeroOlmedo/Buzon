/* ============================================================================
   CONTENIDO DEL ESCAPE ROOM «OPERACIÓN VERDAD»
   Cátedra Ceuta-IATEB · Universidad de Granada y Ciudad Autónoma de Ceuta
   Público: alumnado de Bachillerato (unos 16 años).
   ----------------------------------------------------------------------------
   Este es el ÚNICO archivo que hay que tocar para cambiar textos, noticias,
   respuestas, explicaciones, pistas o preguntas. No hace falta saber programar:

   · Cambia solo lo que va entre comillas "así". Si un texto lleva comillas
     dentro, usa las españolas «así» para no romper nada.
   · No borres las comas que separan los elementos ni las llaves { } y [ ].
   · Las líneas que empiezan por // son comentarios: no se ven en el juego.
   · Si cambias el contenido de las pruebas, sube el número de «version» para
     que nadie continúe una partida guardada con el contenido antiguo.
   · Para comprobar que no has roto nada, abre index.html en el navegador: si
     algo falla, la pantalla principal mostrará un aviso en lugar del juego.

   RIGOR DEL CONTENIDO
   · Las publicaciones, cuentas y medios que aparecen en pantalla son
     SIMULACIONES EDUCATIVAS: el formato está inventado, pero cada caso se basa
     en un hecho o un bulo real, y su explicación enlaza a la fuente que lo
     verificó (campo «fuentes»). No inventes fuentes: enlaza solo páginas que
     hayas comprobado.
   · Un bulo es una información falsa que se ha comprobado como falsa. Si de
     algo no hay pruebas ni a favor ni en contra, no es un bulo: es una
     afirmación no demostrada. No los mezcles.
   ========================================================================= */

window.CONTENIDO = {

  version: 5,
  // Edición de la web: aparece en «Datos de la partida» para que el profesorado
  // anote qué versión se usó en cada sesión. Cámbiala al publicar cambios.
  edicion: "19-11-2026",

  // LA HISTORIA EN DOS LÍNEAS
  // Quien juega está en la Tierra. Su terminal capta la señal de auxilio de
  // Vere, varado en órbita, que le da acceso remoto a los sistemas de su nave:
  // lo que se ve en pantalla es la cabina de Vere a través de ese enlace. Las
  // baterías de emergencia mantienen el enlace 20 minutos. Al despegar, la nave
  // se va y la señal se pierde; quien juega se queda en la Tierra.

  // ---------------------------------------------------------------------------
  // AJUSTES GENERALES
  // ---------------------------------------------------------------------------
  ajustes: {
    // Cuenta atrás de la batería del enlace. Empieza al aceptar la conexión.
    // Al llegar a cero la partida no se pierde: Vere conecta la reserva
    // auxiliar y se puede seguir jugando hasta despegar (queda anotado en los
    // datos de la partida). Con «activo: false» no hay cuenta atrás.
    temporizador: {
      activo: true,
      minutos: 20,
      etiqueta: "Batería del enlace",
      aviso5: "Quedan 5 minutos de batería en el enlace. Tenemos que darnos prisa.",
      aviso1: "Último minuto de batería. Si se agota, tendré que tirar de la reserva auxiliar.",
      agotado: "Batería agotada. He conectado la reserva auxiliar: el enlace aguanta, pero hay que despegar cuanto antes.",
    },
    // Volumen inicial del sonido (0 a 1). El sonido empieza siempre apagado:
    // cada jugador lo activa desde el panel de sistemas.
    volumen: 0.5,
    // Dirección del servidor donde se guardan las métricas de cada partida
    // (web de la Cátedra, ruta «escape-room.partidas.store» de Laravel). Solo
    // se envían si se acepta el aviso de datos. Abierto desde el disco o desde
    // GitHub Pages, el envío falla sin molestar y el juego sigue igual.
    // Con «url: ""» no se envía nada.
    registro: {
      url: "/escape-room/partidas",
    },
  },

  // ---------------------------------------------------------------------------
  // AVISO DE DATOS: aparece al cargar y hay que aceptarlo para jugar.
  // Se vuelve a pedir en cada partida nueva (puede jugar otra persona).
  // Si cambias lo que se recoge, cambia también «version» (queda guardada con
  // cada partida) y la política de privacidad de la web.
  // ---------------------------------------------------------------------------
  avisoDatos: {
    version: "v1",
    titulo: "Antes de empezar",
    parrafos: [
      "Para evaluar y mejorar esta actividad, la Cátedra Ceuta-IATEB (Universidad de Granada y Ciudad Autónoma de Ceuta) guarda en su servidor los datos de cada partida.",
    ],
    seGuarda: {
      titulo: "Qué se guarda",
      puntos: [
        "Cómo avanzas por el juego: módulos superados, aciertos y fallos, pistas y tiempos.",
        "Tus respuestas al cuestionario de opinión, si decides rellenarlo.",
        "Un código aleatorio para no mezclar partidas, por ejemplo OV-7K3Q-M2PA.",
      ],
    },
    noSeGuarda: {
      titulo: "Qué no se guarda",
      puntos: [
        "Tu nombre, tu correo, tu dirección IP ni ningún dato que te identifique.",
      ],
    },
    notas: [
      "No es un examen: los datos no cuentan para la nota.",
      "Si tienes menos de 14 años, necesitas el permiso de tu madre, padre o tutor legal.",
    ],
    boton: "Aceptar y jugar",
  },

  // ---------------------------------------------------------------------------
  // EL PERSONAJE (habla por el canal de comunicación)
  // ---------------------------------------------------------------------------
  alien: {
    nombre: "Vere",
    saludo: "Señal de auxilio en bucle… ¿Alguien me recibe?",
    alAcertar: [
      "Dato limpio. El reactor lo acepta.",
      "Correcto. La presión del reactor sube.",
      "Bien visto. Eso es exactamente lo que la nave necesita.",
    ],
    alFallar: [
      "Ese dato venía adulterado y el reactor lo ha rechazado. Revisa el análisis y prueba otra vez.",
      "Combustible contaminado. Los bulos están diseñados para parecer verdad: lee la explicación y repite.",
      "El sistema detecta una incoherencia. Vuelve a intentarlo.",
    ],
    alSuperarPrueba: "Módulo restaurado. Un 25 % más de energía y un fragmento del código de activación.",
    alPedirPista: "Te paso los datos del análisis. Lo demás es cosa tuya.",
  },

  // ---------------------------------------------------------------------------
  // PANTALLA DE ARRANQUE
  // ---------------------------------------------------------------------------
  // Lo que ve quien juega antes de conectarse: la señal de auxilio en su terminal.
  portada: {
    titulo: "Operación Verdad",
    aviso: "Señal de auxilio recibida",
    subtitulo: "Tu terminal ha captado una llamada de socorro desde una nave averiada en órbita. Su reactor funciona con información verdadera, y su piloto, Vere, pide acceso remoto a alguien en la Tierra que le ayude a separarla de los bulos.",
    // Datos que llegan con la señal. «correcto: true» se muestra como operativo.
    diagnostico: [
      { sistema: "Origen", estado: "órbita terrestre baja", correcto: true },
      { sistema: "Reactor de verdad", estado: "3 % · crítico", correcto: false },
      { sistema: "Propulsión", estado: "sin energía", correcto: false },
      { sistema: "Batería del enlace", estado: "20 minutos", correcto: false },
    ],
    boton: "Aceptar la conexión",
    nota: "20 minutos, individual o en grupo. No hace falta crear una cuenta.",
  },

  // ---------------------------------------------------------------------------
  // INTRODUCCIÓN: Vere habla por el enlace, una línea por mensaje.
  // ---------------------------------------------------------------------------
  introduccion: {
    titulo: "Enlace establecido",
    estadoCanal: "Enlace remoto activo",
    dialogos: [
      "Te veo. Soy Vere. Lo que tienes en pantalla es la cabina de mi nave: te he dado acceso remoto a sus sistemas.",
      "Esta nave no funciona con combustible: funciona con verdad. Me quedé sin reservas al cruzar vuestra órbita y la información que capto de tu planeta está llena de datos adulterados que mis sensores no saben distinguir.",
      "Las baterías de emergencia mantienen este enlace 20 minutos. Hay cuatro módulos que restaurar: cada uno devuelve un 25 % de energía y un fragmento del código de activación. Tú analizas desde ahí abajo; yo piloto desde aquí.",
    ],
    boton: "Empezar a restaurar la nave",
  },

  // ---------------------------------------------------------------------------
  // LOS CUATRO MÓDULOS
  // · «nombre»: cómo aparece en el panel de sistemas.
  // · «mensaje»: lo que dice Vere por el canal mientras se juega el módulo.
  // · «instrucciones»: lo que se lee en la pantalla principal.
  // · «fragmento»: el trozo del código que entrega al superarlo. Los cuatro
  //   juntos, en orden, forman el código de activación.
  // · «fuentes»: enlaces a la verificación real del caso (nombre y url).
  // ---------------------------------------------------------------------------
  pruebas: {

    // ---------------- PRUEBA 1: DETECTAR EL BULO ----------------------------
    // Cada ronda tiene dos publicaciones: una verdadera y un bulo.
    // Formatos: "red" (red social), "whatsapp", "noticia" (medio digital) o "sms".
    detectar: {
      nombre: "Detectar el bulo",
      titulo: "¿Cuál es verdad?",
      instrucciones: "Dos señales interceptadas sobre el mismo tema. Solo una es verdadera: selecciónala.",
      mensaje: "Mis sensores captan dos señales sobre el mismo asunto. Una me lleva a casa; la otra me deja tirado. ¿Cuál cargo?",
      fragmento: "VE",
      rondas: [
        {
          tema: "Salud",
          pista: "Desconfía de los remedios que «curan» enfermedades graves y que, además, piden reenviarse.",
          tarjetas: [
            {
              verdadera: false,
              formato: "whatsapp",
              reenviado: "Reenviado muchas veces",
              texto: "Beber agua caliente con limón en ayunas cura el cáncer. Lo dicen los médicos pero no quieren que se sepa. ¡Pásalo a todos tus contactos!",
              hora: "08:14",
            },
            {
              verdadera: true,
              formato: "noticia",
              medio: "Periódico digital",
              seccion: "Salud",
              titular: "No, el limón no cura el cáncer",
              entradilla: "Los estudios que circulan se hicieron con células en el laboratorio y están muy lejos de demostrar ningún efecto como tratamiento en personas.",
              firma: "Redacción",
            },
          ],
          explicacion: "El mensaje de WhatsApp promete curar una enfermedad grave con un alimento, se apoya en «los médicos» sin nombre y pide difundirlo. Maldita Ciencia explica que los estudios con compuestos del limón se hicieron con células en laboratorio, muy lejos de servir como cura.",
          fuentes: [
            { nombre: "Maldita.es: No, la limonada o el agua con limón no curan el cáncer", url: "https://maldita.es/malditaciencia/20191211/no-la-limonada-o-el-agua-con-limon-no-curan-el-cancer/" },
          ],
        },
        {
          tema: "Ciencia",
          pista: "Piensa en la distancia: la Luna está a unos 384.000 km. ¿Cómo de ancha es una muralla?",
          tarjetas: [
            {
              verdadera: true,
              formato: "noticia",
              medio: "Revista de divulgación",
              seccion: "Espacio",
              titular: "La Gran Muralla China no se ve a simple vista desde la Luna",
              entradilla: "La NASA explica que, incluso desde la órbita terrestre baja, es muy difícil distinguirla sin teleobjetivo: es larguísima, pero estrecha y del color del terreno.",
              firma: "Sección de ciencia",
            },
            {
              verdadera: false,
              formato: "red",
              autor: "Curiosidades del Mundo",
              usuario: "@curiosidades_top",
              texto: "Dato que te volará la cabeza: la Gran Muralla China es la única construcción humana que se ve desde la Luna.",
              metricas: "12,4 mil me gusta · 3.100 compartidos",
            },
          ],
          explicacion: "Es un mito que circula desde hace casi un siglo. La NASA, con testimonios de astronautas, explica que la muralla no se ve a simple vista desde la Luna y que es difícil distinguirla incluso desde la órbita baja. Que un dato se repita mucho no lo hace verdadero.",
          fuentes: [
            { nombre: "NASA: Great Wall (imagen y explicación)", url: "https://www.nasa.gov/image-article/great-wall/" },
            { nombre: "NASA: China's Wall Less Great in View from Space (PDF)", url: "https://www.nasa.gov/wp-content/uploads/static/history/PDFs/great_wall_less_great_from_space.pdf" },
          ],
        },
        {
          tema: "Ciberseguridad",
          pista: "Fíjate en el remitente y en el enlace: ¿es la web de una empresa de verdad?",
          tarjetas: [
            {
              verdadera: false,
              formato: "sms",
              remitente: "INFO-ENVIO",
              texto: "Su paquete está retenido en aduanas por impago de tasas (1,99 €). Abónelas antes de 24 h o será devuelto: envios-tasas-pago.top",
              hora: "11:02",
            },
            {
              verdadera: true,
              formato: "red",
              autor: "Aula de Ciberseguridad",
              usuario: "@aula_ciberseguridad",
              texto: "Si te llega un SMS de un supuesto paquete retenido que te pide pagar con un enlace, es smishing: no lo abras. INCIBE lleva años avisando de estas campañas.",
              metricas: "842 me gusta · 1.020 compartidos",
            },
          ],
          explicacion: "El SMS reúne las señales del smishing: urgencia, una cantidad pequeña para que no te lo pienses y un enlace a un dominio extraño. INCIBE, el Instituto Nacional de Ciberseguridad, ha alertado de muchas campañas de SMS que suplantan a empresas de paquetería para robar datos o instalar programas maliciosos.",
          fuentes: [
            { nombre: "INCIBE: Suplantadas por SMS empresas de paquetería", url: "https://www.incibe.es/ciudadania/avisos/suplantadas-por-sms-empresas-de-paqueteria-como-correos-y-correos-express" },
          ],
        },
      ],
    },

    // ---------------- PRUEBA 2: VERIFICAR UNA NOTICIA -----------------------
    // Cada ronda es una publicación. «esVerdad: true» si su contenido es cierto.
    verificar: {
      nombre: "Verificar la noticia",
      titulo: "¿Verdadera o bulo?",
      instrucciones: "Analiza la publicación y emite un veredicto.",
      mensaje: "Ahora las señales llegan de una en una. Necesito un veredicto rápido, pero fiable.",
      fragmento: "RA",
      botonVerdad: "Verdadera",
      botonBulo: "Bulo",
      rondas: [
        {
          tema: "Salud",
          publicacion: {
            formato: "noticia",
            medio: "Periódico digital",
            seccion: "Salud",
            titular: "Los antibióticos no sirven contra los virus del resfriado o la gripe",
            entradilla: "Tomarlos sin necesidad no acelera la curación y contribuye a que las bacterias se vuelvan resistentes.",
            firma: "Redacción",
          },
          esVerdad: true,
          pista: "Los antibióticos actúan contra un tipo de microbio concreto. ¿Bacterias o virus?",
          explicacion: "Es verdad. Los antibióticos actúan contra bacterias, no contra virus. La OMS calcula que la mitad de las veces se recetan para afecciones víricas, en las que no tienen ningún efecto, y que ese mal uso alimenta la resistencia a los antibióticos.",
          fuentes: [
            { nombre: "OMS: ¿Cómo detener la resistencia a los antibióticos?", url: "https://www.who.int/es/news-room/commentaries/detail/how-to-stop-antibiotic-resistance-here-s-a-who-prescription" },
          ],
        },
        {
          tema: "Redes y apps",
          publicacion: {
            formato: "whatsapp",
            reenviado: "Reenviado muchas veces",
            texto: "A partir del sábado WhatsApp cobrará 0,01 € por mensaje. Si reenvías esto a 10 contactos, tu logo se volverá azul y seguirás gratis. Lo ha confirmado el director de la empresa.",
            hora: "22:47",
          },
          esVerdad: false,
          pista: "¿Qué efecto técnico podría tener reenviar un mensaje? ¿Quién es ese director?",
          explicacion: "Es un bulo en cadena que circula desde hace años. WhatsApp lo ha negado y Maldita.es lo ha desmentido varias veces: reenviar un mensaje no cambia nada de tu cuenta. Las cadenas usan una autoridad sin nombre y una amenaza para que las difundas.",
          fuentes: [
            { nombre: "Maldita.es: No, WhatsApp no costará 0,01 euros por mensaje", url: "https://maldita.es/malditobulo/20200624/no-whatsapp-no-costara-001-euros-por-mensaje-a-partir-del-sabado-ni-tu-logo-se-volvera-azul-si-reenvias-la-cadena-a-10-contactos/" },
          ],
        },
        {
          tema: "Inteligencia artificial",
          publicacion: {
            formato: "red",
            autor: "Moda y Tendencias",
            usuario: "@moda_tendencias_ya",
            texto: "El papa Francisco sorprende con su nuevo look: un abrigo plumífero blanco de diseño. Esta foto está dando la vuelta al mundo.",
            // «imagen» describe la foto (texto alternativo). Con «foto» se muestra
            // la imagen real; sin «foto», aparece solo la descripción en un recuadro.
            imagen: "Dos fotos del papa Francisco con un enorme abrigo plumífero blanco, caminando por la calle",
            foto: "assets/papa-plumifero-ia.webp",
            metricas: "48 mil me gusta · 21 mil compartidos",
          },
          esVerdad: false,
          pista: "Una búsqueda inversa de la imagen lleva hasta su primera publicación. ¿Dónde apareció?",
          explicacion: "Es un bulo. La imagen se generó con inteligencia artificial (Midjourney) y se publicó por primera vez en un foro de Reddit dedicado a ese programa, según comprobó Maldita.es con una búsqueda inversa de imágenes. Que una foto parezca real ya no basta: hay que buscar su origen.",
          fuentes: [
            { nombre: "Maldita.es: Las imágenes del papa con un abrigo de plumas están creadas con IA", url: "https://maldita.es/malditatecnologia/20230327/papa-francisco-abrigo-plumas-bulo-inteligencia-artificial" },
          ],
        },
      ],
    },

    // ---------------- PRUEBA 3: CLASIFICAR LOS BULOS ------------------------
    // Cada ejemplo indica en «tipo» el «id» del tipo de desinformación que le
    // corresponde. Los tipos siguen la clasificación de Claire Wardle (First
    // Draft) y del informe del Consejo de Europa sobre el desorden informativo.
    clasificar: {
      nombre: "Clasificar los bulos",
      titulo: "Clasifica los bulos",
      instrucciones: "Une cada ejemplo con el tipo de desinformación al que pertenece. Arrastra desde un ejemplo hasta su tipo, o toca un ejemplo y después su tipo.",
      mensaje: "Para calibrar el filtro necesito saber cómo engañan, no solo si engañan. Clasifica estos casos.",
      fragmento: "CI",
      pista: "Pregúntate qué se ha falseado: ¿el contexto de algo real, el propio contenido, quién lo firma, el titular… o todo?",
      botonComprobar: "Comprobar conexiones",
      tipos: [
        { id: "contexto", nombre: "Contexto falso", definicion: "Contenido real compartido con un contexto falso." },
        { id: "manipulado", nombre: "Contenido manipulado", definicion: "Contenido real alterado para engañar." },
        { id: "impostor", nombre: "Contenido impostor", definicion: "Se hace pasar por una fuente real." },
        { id: "conexion", nombre: "Conexión falsa", definicion: "El titular no se corresponde con el contenido." },
        { id: "fabricado", nombre: "Contenido fabricado", definicion: "Totalmente falso, creado para engañar." },
      ],
      ejemplos: [
        {
          texto: "Foto real de una riada de hace años compartida hoy como «así está ahora tu ciudad».",
          tipo: "contexto",
          explicacion: "La foto es auténtica, pero la fecha y el lugar que la acompañan son falsos.",
        },
        {
          texto: "Vídeo de una cantante con la voz clonada por IA anunciando un concierto gratis que nunca convocó.",
          tipo: "manipulado",
          explicacion: "Se parte de imágenes reales y se alteran (aquí, con IA) para que diga algo que nunca dijo.",
        },
        {
          texto: "Captura con el logotipo del Ministerio de Educación que anuncia el aplazamiento de la PAU. El Ministerio nunca la publicó.",
          tipo: "impostor",
          explicacion: "Usa la imagen de una institución real para que el mensaje parezca oficial.",
        },
        {
          texto: "Titular: «Prohíben los móviles en todos los institutos». La noticia habla de un único centro.",
          tipo: "conexion",
          explicacion: "El titular promete algo que el contenido no respalda.",
        },
        {
          texto: "Noticia sobre «un estudio» que demuestra que dormir cuatro horas mejora las notas. El estudio no existe.",
          tipo: "fabricado",
          explicacion: "Todo está inventado: no hay ningún hecho real de partida.",
        },
      ],
      fuentes: [
        { nombre: "Claire Wardle (First Draft): Fake news. It's complicated", url: "https://firstdraftnews.org/articles/fake-news-complicated/" },
        { nombre: "Consejo de Europa: Information Disorder (Wardle y Derakhshan, 2017)", url: "https://rm.coe.int/information-disorder-toward-an-interdisciplinary-framework-for-researc/168076277c" },
      ],
    },

    // ---------------- PRUEBA 4: CUESTIONARIO FINAL --------------------------
    // Se responden todas las preguntas y se corrigen a la vez. Para conseguir
    // el fragmento hay que acertarlas todas; las falladas se pueden repetir.
    // «correcta» es la posición de la respuesta buena, empezando a contar en 0.
    cuestionario: {
      nombre: "Cuestionario final",
      titulo: "Cuestionario de verificación",
      instrucciones: "Responde a todas las preguntas y pulsa «Corregir». Las que falles podrás repetirlas.",
      mensaje: "Último módulo. Este protocolo te servirá aquí y, sobre todo, ahí abajo.",
      fragmento: "DAD",
      botonCorregir: "Corregir respuestas",
      preguntas: [
        {
          texto: "¿Qué diferencia la desinformación de la información errónea?",
          opciones: [
            "Ninguna: son sinónimos",
            "La intención: la desinformación se crea o difunde sabiendo que es falsa",
            "El formato: la desinformación siempre es un vídeo",
            "El alcance: la desinformación llega a más gente",
          ],
          correcta: 1,
          explicacion: "La información errónea se comparte sin saber que es falsa; la desinformación, a sabiendas. La diferencia es la intención (Wardle y Derakhshan, Consejo de Europa).",
          fuente: { nombre: "Consejo de Europa: Information Disorder", url: "https://rm.coe.int/information-disorder-toward-an-interdisciplinary-framework-for-researc/168076277c" },
        },
        {
          texto: "Llegas a una web desconocida con una noticia impactante. ¿Qué es la «lectura lateral»?",
          opciones: [
            "Leer la noticia entera con mucha atención antes de juzgarla",
            "Fijarse en si la web tiene buen diseño y logotipos oficiales",
            "Salir de la página y buscar en otras fuentes qué se sabe de ella y de lo que cuenta",
            "Leer los comentarios de la noticia",
          ],
          correcta: 2,
          explicacion: "Los verificadores profesionales no se quedan dentro de la página: abren otras pestañas para comprobar quién está detrás y qué dicen otras fuentes. Un estudio de la Universidad de Stanford comprobó que así llegaban antes y mejor a la verdad.",
          fuente: { nombre: "Wineburg y McGrew (Stanford): Lateral Reading", url: "https://inquirygroup.org/about/updates/2018/lateral-reading-reading-less-and-learning-more-online" },
        },
        {
          texto: "Circula una foto espectacular de un suceso «de hoy». ¿Cuál es la mejor primera comprobación?",
          opciones: [
            "Ver cuántas veces se ha compartido",
            "Hacer una búsqueda inversa de la imagen para encontrar dónde y cuándo apareció por primera vez",
            "Ampliarla para ver si está nítida",
            "Preguntar en el grupo de clase si alguien la ha visto",
          ],
          correcta: 1,
          explicacion: "La búsqueda inversa de imágenes (Google Lens, TinEye…) muestra si la foto ya circulaba antes o en otro lugar. Así se descubrió el origen de la imagen del papa generada con IA.",
          fuente: { nombre: "Maldita.es: el caso de la imagen del papa", url: "https://maldita.es/malditatecnologia/20230327/papa-francisco-abrigo-plumas-bulo-inteligencia-artificial" },
        },
        {
          texto: "Una afirmación no tiene pruebas ni a favor ni en contra. ¿Cómo la tratas?",
          opciones: [
            "Es un bulo: si no está probado, es falso",
            "Es verdad hasta que alguien demuestre lo contrario",
            "Como algo no demostrado: no la doy por cierta ni la comparto como un hecho, aunque eso no la convierte en un bulo comprobado",
            "Depende de cuánta gente la comparta",
          ],
          correcta: 2,
          explicacion: "«No demostrado» no es lo mismo que «falso». Un bulo es una información que se ha comprobado como falsa; de lo no demostrado, lo honesto es decir que no se sabe y no difundirlo como un hecho.",
        },
        {
          texto: "Un vídeo muestra a un famoso diciendo algo escandaloso y sospechas que puede estar hecho con IA. ¿Qué haces?",
          opciones: [
            "Compartirlo avisando de que «puede ser falso»",
            "Buscar si lo ha publicado una fuente fiable o si los verificadores lo han analizado",
            "Fiarte si la voz suena exactamente como la suya",
            "Descartarlo solo si se ve borroso",
          ],
          correcta: 1,
          explicacion: "Las imágenes y voces generadas con IA pueden ser muy convincentes, así que el aspecto no basta. Lo fiable es buscar el origen y comprobar si medios o verificadores lo han confirmado o desmentido.",
        },
        {
          texto: "Te llega una cadena: «Reenvía esto a 10 contactos o tu cuenta se cerrará». ¿Qué haces?",
          opciones: [
            "Reenviarlo por si acaso",
            "Reenviarlo solo a la familia",
            "No reenviarlo: reenviar no tiene ningún efecto técnico y la amenaza es la señal del bulo",
            "Contestar al remitente pidiendo más datos",
          ],
          correcta: 2,
          explicacion: "Las cadenas usan amenazas y prisa para propagarse. Reenviar un mensaje no cambia nada de tu cuenta: WhatsApp lo ha negado y los verificadores lo han desmentido muchas veces.",
          fuente: { nombre: "Maldita.es: el bulo de WhatsApp de pago", url: "https://maldita.es/malditobulo/20200624/no-whatsapp-no-costara-001-euros-por-mensaje-a-partir-del-sabado-ni-tu-logo-se-volvera-azul-si-reenvias-la-cadena-a-10-contactos/" },
        },
      ],
    },
  },

  // ---------------------------------------------------------------------------
  // PANEL DE ACTIVACIÓN Y FINAL
  // ---------------------------------------------------------------------------
  activacion: {
    titulo: "Activación de la nave",
    texto: "Los cuatro módulos están restaurados. Introduce el código de activación uniendo los fragmentos en orden.",
    mensaje: "Reactor al 100 %. Solo falta el código y nos vamos.",
    boton: "Activar la nave",
    error: "Código incorrecto. Une los fragmentos en el orden de los módulos: 1, 2, 3 y 4.",
    // Pasos de la secuencia de arranque que se encienden uno a uno.
    secuencia: ["Reactor de verdad", "Filtro de desinformación", "Navegación", "Propulsión"],
  },

  // ---------------------------------------------------------------------------
  // CUESTIONARIO DE OPINIÓN (evaluación del piloto)
  // Textos del apartado 6 de la «Propuesta integrada de evaluación». Si cambias
  // algún ítem, cambia también «version» para saber qué versión se respondió.
  // Las respuestas se guardan en el dispositivo y, con el aviso aceptado, se
  // envían al servidor con el resto de datos de la partida. También se pueden
  // descargar en CSV desde la propia web.
  // ---------------------------------------------------------------------------
  cuestionarioOpinion: {
    // El texto de la propuesta habla de «Vero»; aquí se ha cambiado por «Vere».
    version: "Propuesta 1 (no validada), con el personaje renombrado a Vere",
    titulo: "Cuestionario de opinión: Operación Verdad",
    etiquetaCodigo: "Código de la partida, solo si te lo han facilitado",
    introduccion: "Queremos conocer tu opinión sobre la actividad de Vere. No hay respuestas correctas ni incorrectas. Tus respuestas nos ayudarán a mejorar la actividad y no afectarán a tus calificaciones. No escribas tu nombre. Puedes dejar preguntas sin responder.",
    instrucciones: "Marca una opción para cada frase. Si no has llegado a esa parte o no puedes valorarla, marca NV. En la pregunta 12, marca NV si no has utilizado ninguna pista.",
    // Significado de cada valor de la escala, de 1 a 5.
    escala: ["Totalmente en desacuerdo", "En desacuerdo", "Ni de acuerdo ni en desacuerdo", "De acuerdo", "Totalmente de acuerdo"],
    noValorable: "No puedo valorarlo",
    items: [
      "Me ha resultado interesante participar en la misión de Vere.",
      "Creo que esta actividad puede ayudarme a pensar qué comprobar antes de compartir una noticia.",
      "Me ha resultado fácil entender cómo avanzar por la web.",
      "He disfrutado realizando las pruebas de la actividad.",
      "Creo que lo trabajado puede servirme para pensar cómo comprobar una imagen o un vídeo que encuentro en Internet.",
      "Las explicaciones que aparecen después de responder me han resultado claras.",
      "Me ha gustado la historia de la nave y de Vere.",
      "Creo que la actividad puede ayudarme a distinguir mejor entre información fiable e información engañosa.",
      "La historia de Vere me ha ayudado a entender lo que tenía que hacer.",
      "La misión de ayudar a Vere me ha animado a continuar con la actividad.",
      "La dificultad de las pruebas me ha parecido adecuada.",
      "Las pistas me han ayudado a continuar cuando las he necesitado.",
    ],
    abiertas: [
      "¿Qué parte de la actividad o de la historia te ha gustado más? ¿Por qué?",
      "¿Qué cambiarías de la historia, las pruebas o la web para mejorarlas?",
    ],
    mensajeVere: "Tu opinión me ayudará a mejorar la misión para la próxima persona a la que pida ayuda.",
    boton: "Guardar respuestas",
    guardado: "Respuestas guardadas y enviadas con los datos de la partida. Si el profesorado os lo pide, también puedes descargarlas o copiarlas.",
  },

  final: {
    titulo: "Misión completada",
    mensaje: "La nave de Vere abandona la órbita con el reactor lleno de información verdadera. El enlace se ha perdido, pero el registro de la misión se queda en tu terminal. Estas son las técnicas que te llevas:",
    mensajeVere: "Rumbo a casa. Gracias por el combustible. Ahí abajo vas a necesitar ese filtro más que yo. Cambio y cierro.",
    // Frase sobre el tiempo: {resto} se sustituye por la batería que quedaba.
    dentroDePlazo: "Despegue con {resto} de batería en el enlace.",
    fueraDePlazo: "Despegue con la reserva auxiliar, después de agotar los 20 minutos de batería.",
    senalPerdida: "Señal perdida",
    aprendido: [
      "Antes de compartir, para: la prisa y la emoción son las mejores aliadas de los bulos.",
      "Haz lectura lateral: sal de la página y busca qué dicen otras fuentes sobre ella.",
      "Busca el origen de las imágenes con una búsqueda inversa.",
      "Desconfía de las autoridades sin nombre, las amenazas y las promesas milagrosas.",
      "Que algo parezca real (o lo haya hecho una IA convincente) no lo hace verdadero.",
      "No demostrado no es lo mismo que falso: no lo difundas como un hecho.",
    ],
    aviso: "Las publicaciones, cuentas y medios que aparecen en el juego son simulaciones educativas basadas en casos reales. Las explicaciones enlazan a las verificaciones originales.",
    verificadores: [
      { nombre: "Maldita.es", url: "https://maldita.es" },
      { nombre: "Newtral", url: "https://www.newtral.es" },
      { nombre: "EFE Verifica", url: "https://verifica.efe.com" },
    ],
    creditos: "Una actividad de la Cátedra en Inteligencia Artificial y Tecnologías Emergentes para el Bienestar (Cátedra Ceuta-IATEB), de la Universidad de Granada y la Ciudad Autónoma de Ceuta, con motivo del Día Internacional de la Ciudad Educadora.",
    // Créditos de las imágenes (obligatorios por sus licencias).
    creditosImagenes: [
      { texto: "Vía Láctea: ESO/S. Brunier (CC BY 4.0)", url: "https://www.eso.org/public/images/eso0932a/" },
      { texto: "Tierra: NASA, misión Apollo 17 (dominio público)", url: "https://commons.wikimedia.org/wiki/File:The_Earth_seen_from_Apollo_17.jpg" },
      { texto: "Textura metálica: Poly Haven (CC0)", url: "https://polyhaven.com/a/metal_plate_02" },
      { texto: "Papa Francisco con plumífero: imagen falsa generada con IA (Midjourney) por Pablo Xavier en 2023; se reproduce con fines educativos", url: "https://maldita.es/malditatecnologia/20230327/papa-francisco-abrigo-plumas-bulo-inteligencia-artificial" },
    ],
    botonReiniciar: "Nueva misión",
  },
};
