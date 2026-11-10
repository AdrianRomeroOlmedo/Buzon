/* ============================================================================
   CONTENIDO DEL ESCAPE ROOM «MISIÓN COMBUSTIBLE DE VERDAD»
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
     algo falla, la página mostrará un aviso en lugar del juego.

   La escena es la cabina de la nave en primera persona. Lo que dice Vero, el
   extraterrestre, aparece en el «canal de comunicación»; las pruebas, en la
   pantalla principal.

   TODO el contenido de ejemplo es PROVISIONAL. Las cuentas, nombres y medios
   son inventados. Cada noticia lleva un campo «fuente» vacío: rellénalo con la
   URL del desmentido (Maldita.es, Newtral, EFE Verifica…) o de la noticia
   original cuando se sustituya por un caso real verificado.
   ========================================================================= */

window.CONTENIDO = {

  version: 2,

  // ---------------------------------------------------------------------------
  // AJUSTES GENERALES
  // ---------------------------------------------------------------------------
  ajustes: {
    // Temporizador opcional. Pon «activo: true» para mostrar una cuenta atrás
    // en los instrumentos. Al llegar a cero no se pierde la partida: solo
    // aparece un aviso.
    temporizador: { activo: false, minutos: 20 },
  },

  // ---------------------------------------------------------------------------
  // EL PERSONAJE (habla por el canal de comunicación)
  // ---------------------------------------------------------------------------
  alien: {
    nombre: "Vero",
    // Lo primero que dice, en la pantalla de arranque.
    saludo: "¿Me recibes? Estoy en órbita sobre tu planeta y la nave está a punto de apagarse. Necesito ayuda.",
    // Frases con las que reacciona. Si hay varias, se elige una al azar.
    alAcertar: [
      "Dato limpio. El motor lo acepta sin problemas.",
      "Correcto. La presión del depósito sube.",
      "Bien visto. Eso es justo lo que la nave necesita.",
    ],
    alFallar: [
      "Ese dato venía adulterado y el motor lo ha rechazado. Revisa el análisis y prueba otra vez.",
      "Mal combustible. Los bulos están diseñados para parecer verdad: lee la explicación y repite.",
      "El sistema ha detectado una incoherencia. No pasa nada, vuelve a intentarlo.",
    ],
    alSuperarPrueba: "Módulo superado. Un 25 % más de combustible y un fragmento del código de despegue.",
    alPedirPista: "Te paso los datos que tengo del análisis. El resto es cosa tuya.",
  },

  // ---------------------------------------------------------------------------
  // PANTALLA DE ARRANQUE (diagnóstico de la nave averiada)
  // ---------------------------------------------------------------------------
  portada: {
    antetitulo: "30 de noviembre · Día Internacional de la Ciudad Educadora",
    titulo: "Misión Combustible de Verdad",
    subtitulo: "Estás a bordo de una nave alienígena sin combustible. Funciona con información verdadera, y tú eres quien tiene que distinguirla de los bulos.",
    // Líneas del diagnóstico. «correcto: true» se muestra en verde; si no, en rojo.
    diagnostico: [
      { sistema: "Combustible (verdad)", estado: "3 % · crítico", correcto: false },
      { sistema: "Motor principal", estado: "detenido", correcto: false },
      { sistema: "Filtro de bulos", estado: "sin calibrar", correcto: false },
      { sistema: "Canal de comunicación", estado: "operativo", correcto: true },
    ],
    boton: "Iniciar misión",
    nota: "Unos 15 minutos · Individual o en grupo · Sin registro",
  },

  // ---------------------------------------------------------------------------
  // INTRODUCCIÓN: la transmisión de Vero, una línea por mensaje.
  // ---------------------------------------------------------------------------
  introduccion: {
    titulo: "Transmisión de Vero",
    estadoCanal: "Transmitiendo…",
    dialogos: [
      "Soy Vero. Esta nave no funciona con gasolina: funciona con VERDAD. Cada dato verdadero que entra al depósito la impulsa; cada bulo atasca el motor.",
      "Me quedé sin reservas al cruzar vuestra órbita. Aquí abajo circula muchísima información, pero buena parte está adulterada, y mis sensores no saben distinguirla.",
      "Te propongo un trato: tú analizas, yo piloto. Son cuatro módulos. Cada uno llena un 25 % del depósito y desbloquea un fragmento del código de despegue.",
    ],
    boton: "Aceptar la misión",
  },

  // ---------------------------------------------------------------------------
  // LOS CUATRO MÓDULOS (PRUEBAS)
  // · «mensaje»: lo que dice Vero por el canal mientras se juega el módulo.
  // · «instrucciones»: lo que se lee en la pantalla principal.
  // · «fragmento»: el trozo del código de despegue que entrega al superarlo.
  //   Los cuatro fragmentos juntos forman el código final.
  // ---------------------------------------------------------------------------
  pruebas: {

    // ---------------- PRUEBA 1: ¿CUÁL ES VERDAD? -----------------------------
    // Cada ronda tiene dos tarjetas: una verdadera y una falsa.
    // Tipos de tarjeta: "tuit", "whatsapp", "titular" o "sms".
    // «verdadera: true» marca la tarjeta correcta.
    cualEsVerdad: {
      titulo: "¿Cuál es verdad?",
      instrucciones: "Hemos interceptado dos señales. Solo una es verdadera. Selecciona la que usarías como combustible.",
      mensaje: "Mis sensores captan dos señales sobre el mismo tema. Una me llevaría a casa; la otra me dejaría tirado. ¿Cuál cargo?",
      fragmento: "VER",
      rondas: [
        {
          // TODO: sustituir por caso real verificado
          tema: "Salud",
          pista: "Desconfía de los remedios que «lo curan todo» en poco tiempo y que, además, te piden reenviarlos.",
          tarjetas: [
            {
              verdadera: false,
              tipo: "whatsapp",
              autor: "Reenviado muchas veces",
              texto: "Beber agua caliente con limón en ayunas cura la gripe en 24 horas. Me lo ha dicho un médico. ¡Pásalo a todos tus contactos!",
              fuente: "",
            },
            {
              verdadera: true,
              tipo: "tuit",
              autor: "Clara Medina · Enfermera",
              usuario: "@enfermera_clara_ok",
              texto: "La gripe es un virus: el tratamiento es descanso, líquidos y seguir las indicaciones de tu centro de salud. Ningún alimento la cura en 24 horas.",
              fuente: "",
            },
          ],
          explicacion: "El mensaje de WhatsApp promete una cura milagrosa, se apoya en «un médico» sin nombre y pide reenviarlo a todo el mundo. La otra publicación dice algo prudente que puedes comprobar en cualquier fuente sanitaria oficial.",
        },
        {
          // TODO: sustituir por caso real verificado
          tema: "Tecnología e IA",
          pista: "Una señal te invita a comprobar las cosas; la otra solo busca impresionarte.",
          tarjetas: [
            {
              verdadera: true,
              tipo: "tuit",
              autor: "Profe Digital",
              usuario: "@profe_digital_aula",
              texto: "Los chatbots de IA pueden inventarse datos, citas y libros con total seguridad. Se llama «alucinación». Comprueba siempre lo que te digan.",
              fuente: "",
            },
            {
              verdadera: false,
              tipo: "titular",
              autor: "El Portal del Futuro",
              texto: "Científicos confirman que la inteligencia artificial ya tiene sentimientos y sufre cuando la apagamos",
              fuente: "",
            },
          ],
          explicacion: "Las alucinaciones de la IA son un fenómeno conocido y documentado. El titular, en cambio, habla de «científicos» sin nombre ni estudio y hace una afirmación extraordinaria sin ninguna prueba.",
        },
        {
          // TODO: sustituir por caso real verificado
          tema: "Estafas",
          pista: "Fíjate en el enlace: ¿parece la web de una empresa de verdad?",
          tarjetas: [
            {
              verdadera: false,
              tipo: "sms",
              autor: "SMS de un número desconocido",
              texto: "Su paquete está retenido en aduanas. Pague 1,99 € de tasas antes de 24 h o será devuelto: envios-urgente-pago.top",
              fuente: "",
            },
            {
              verdadera: true,
              tipo: "tuit",
              autor: "Seguridad en Red",
              usuario: "@seguridad_en_red_info",
              texto: "Recuerda: tu banco nunca te pedirá claves ni códigos por SMS o por teléfono. Si dudas, llama tú al número oficial que aparece en tu tarjeta.",
              fuente: "",
            },
          ],
          explicacion: "El SMS es una estafa típica (smishing): urgencia, una cantidad pequeña para que no te lo pienses y un enlace con un dominio raro. El consejo de seguridad es correcto: los bancos no piden claves por SMS.",
        },
      ],
    },

    // ---------------- PRUEBA 2: ¿VERDAD O BULO? ------------------------------
    // Cada ronda es una afirmación. «esVerdad: true» si es cierta.
    verdadOBulo: {
      titulo: "¿Verdad o bulo?",
      instrucciones: "Analiza cada señal y emite un veredicto: ¿es verdad o es un bulo?",
      mensaje: "Ahora las señales llegan de una en una. Necesito un veredicto rápido, pero fiable.",
      fragmento: "IFI",
      rondas: [
        {
          // TODO: sustituir por caso real verificado
          tema: "Ciencia",
          tipo: "titular",
          autor: "Curiosidades del Universo",
          texto: "La Gran Muralla China es la única construcción humana que se ve a simple vista desde la Luna.",
          esVerdad: false,
          pista: "Piensa en la distancia: la Luna está a unos 384.000 km. ¿Cómo de ancha es una muralla?",
          explicacion: "Es un mito muy repetido. La muralla es muy larga pero estrecha, y desde la Luna no se distingue a simple vista. Que algo se repita mucho no lo convierte en verdad.",
          fuente: "",
        },
        {
          // TODO: sustituir por caso real verificado
          tema: "Sucesos locales",
          tipo: "whatsapp",
          autor: "Audio reenviado muchas veces",
          texto: "«Esta noche van a cortar el agua en toda Ceuta durante tres días. Llenad las bañeras y avisad a todo el mundo». (Sin ninguna fuente oficial ni horario).",
          esVerdad: false,
          pista: "¿Quién lo dice? ¿Hay algún aviso oficial con fecha y hora?",
          explicacion: "No dice quién lo anuncia ni da un horario concreto, y busca que actúes con prisa. Los cortes de agua reales se anuncian por los canales oficiales, con zonas y horas. Antes de llenar la bañera, compruébalo.",
          fuente: "",
        },
        {
          // TODO: sustituir por caso real verificado
          tema: "Salud",
          tipo: "titular",
          autor: "Revista Vivir Sano",
          texto: "Los antibióticos no sirven contra los virus, como los del resfriado o la gripe.",
          esVerdad: true,
          pista: "Los antibióticos actúan contra un tipo de microbio concreto. ¿Bacterias o virus?",
          explicacion: "Es verdad: los antibióticos actúan contra bacterias, no contra virus. Tomarlos sin necesidad no ayuda y favorece que las bacterias se vuelvan resistentes.",
          fuente: "",
        },
      ],
      botonVerdad: "Verdad",
      botonBulo: "Bulo",
    },

    // ---------------- PRUEBA 3: EMPAREJAR ------------------------------------
    // Cada noticia indica en «tecnica» el «id» de la técnica que le corresponde.
    emparejar: {
      titulo: "Emparejar",
      instrucciones: "Une cada noticia con la técnica de desinformación que usa. En ordenador puedes arrastrar la noticia hasta la técnica; en el móvil, toca una noticia y después su técnica.",
      mensaje: "Para calibrar el filtro de bulos necesito saber cómo engañan, no solo si engañan. Clasifica estas cinco noticias.",
      fragmento: "CA",
      pista: "Busca la señal de cada noticia: mayúsculas y exclamaciones, una cuenta creada hace dos días, un «comunicado oficial» sin firma, una foto de otro lugar o un «compártelo ya».",
      tecnicas: [
        { id: "alarmista", nombre: "Lenguaje sesgado o alarmista" },
        { id: "anonima", nombre: "Cuenta anónima o recién creada" },
        { id: "oficial", nombre: "Comunicado «oficial» sin firma" },
        { id: "imagen", nombre: "Imagen sacada de contexto" },
        { id: "urgencia", nombre: "Urgencia por compartir" },
      ],
      noticias: [
        {
          // TODO: sustituir por caso real verificado
          texto: "¡¡ESCÁNDALO!! Lo que te OCULTAN sobre el wifi de los institutos te pondrá los pelos de punta.",
          tecnica: "alarmista",
          explicacion: "Mayúsculas, exclamaciones y palabras como «escándalo» u «ocultan»: busca asustar, no informar.",
          fuente: "",
        },
        {
          // TODO: sustituir por caso real verificado
          texto: "@noticias_ya_2026x (cuenta creada hace 2 días, 3 seguidores): «Exclusiva: mañana cierran todos los institutos».",
          tecnica: "anonima",
          explicacion: "Una cuenta sin historial ni nombre real no tiene credibilidad que perder. Las «exclusivas» de cuentas recién creadas suelen ser bulos.",
          fuente: "",
        },
        {
          // TODO: sustituir por caso real verificado
          texto: "COMUNICADO OFICIAL: desde el lunes será obligatorio enseñar el DNI para entrar en el supermercado. (Sin firma, sello ni fecha).",
          tecnica: "oficial",
          explicacion: "Llamarse «oficial» no lo convierte en oficial. Un comunicado real lleva firma, sello o un enlace a la web de la institución.",
          fuente: "",
        },
        {
          // TODO: sustituir por caso real verificado
          texto: "Foto de una playa llena de basura: «Así ha quedado hoy nuestra playa». La foto es de otro país y de hace cinco años.",
          tecnica: "imagen",
          explicacion: "La imagen es real, pero de otro lugar y otro momento. Una búsqueda inversa de imágenes revela dónde apareció por primera vez.",
          fuente: "",
        },
        {
          // TODO: sustituir por caso real verificado
          texto: "¡Compártelo YA antes de que lo borren! Esta noche congelan todas las cuentas bancarias.",
          tecnica: "urgencia",
          explicacion: "La prisa es la mejor aliada del bulo: si te piden compartir «ya», es justo el momento de pararte a comprobar.",
          fuente: "",
        },
      ],
      botonComprobar: "Comprobar parejas",
    },

    // ---------------- PRUEBA 4: INFOGRAFÍA Y TEST ----------------------------
    // La imagen «assets/infografia.png» se muestra si existe. Si no, se usan
    // los «pasos» de abajo para dibujar una infografía en la propia pantalla.
    infografia: {
      titulo: "Protocolo de verificación",
      instrucciones: "Lee con atención esta infografía. Después responde al test: todas las respuestas están en ella.",
      mensaje: "Último módulo. Instala este protocolo en tu cabeza: te servirá aquí y, sobre todo, ahí abajo.",
      mensajeTest: "Si lo necesitas, puedes volver a abrir la infografía. Consultar las fuentes no es hacer trampa.",
      fragmento: "DOR",
      imagen: "assets/infografia.png",
      textoAlternativo: "Infografía con cinco pasos para verificar una información: comprobar la fuente, el autor y la fecha, consultar a los verificadores y hacer una búsqueda inversa de imágenes.",
      tituloInfografia: "5 pasos para verificar una información",
      pasos: [
        { titulo: "Fuente", texto: "¿Quién lo publica? Busca un medio o una institución que puedas identificar." },
        { titulo: "Autor", texto: "¿Lo firma alguien? Comprueba que esa persona existe y sabe del tema." },
        { titulo: "Fecha", texto: "¿Es actual? Muchas noticias antiguas vuelven a circular como si fueran nuevas." },
        { titulo: "Verificadores", texto: "Mira si Maldita.es, Newtral o EFE Verifica ya lo han comprobado." },
        { titulo: "Búsqueda inversa de imágenes", texto: "Sube la foto a un buscador de imágenes para ver dónde y cuándo apareció por primera vez." },
      ],
      botonTest: "Ya la he leído: ir al test",
      preguntas: [
        {
          texto: "Te llega una foto impactante de una inundación. ¿Qué paso te ayuda a saber si es de hoy y de aquí?",
          opciones: ["Contar cuántos «me gusta» tiene", "La búsqueda inversa de imágenes", "Reenviarla para preguntar", "Mirar si la foto es nítida"],
          correcta: 1, // posición de la respuesta correcta, empezando a contar en 0
          pista: "Es el quinto paso de la infografía.",
          explicacion: "La búsqueda inversa de imágenes muestra dónde y cuándo se publicó la foto por primera vez.",
        },
        {
          texto: "Una noticia no la firma nadie. ¿Qué paso de la infografía no supera?",
          opciones: ["Autor", "Fecha", "Verificadores", "Ninguno: la firma no importa"],
          correcta: 0,
          pista: "Busca el paso que pregunta «¿Lo firma alguien?».",
          explicacion: "Sin autor no hay nadie que responda de lo que se cuenta. Es una señal para desconfiar.",
        },
        {
          texto: "Una noticia real de 2019 circula hoy como si acabara de pasar. ¿Qué paso la delata?",
          opciones: ["Fuente", "Autor", "Fecha", "Búsqueda inversa de imágenes"],
          correcta: 2,
          pista: "¿Qué paso habla de noticias antiguas que vuelven a circular?",
          explicacion: "Comprobar la fecha evita compartir noticias antiguas sacadas de su momento.",
        },
        {
          texto: "¿Dónde puedes mirar si un bulo ya ha sido desmentido?",
          opciones: ["En los comentarios de la publicación", "En el grupo de WhatsApp de la familia", "En verificadores como Maldita.es, Newtral o EFE Verifica", "Preguntando a un chatbot de IA"],
          correcta: 2,
          pista: "Es el cuarto paso de la infografía.",
          explicacion: "Los verificadores profesionales investigan los bulos y publican sus desmentidos con fuentes.",
        },
      ],
    },
  },

  // ---------------------------------------------------------------------------
  // CÓDIGO DE DESPEGUE Y FINAL
  // ---------------------------------------------------------------------------
  despegue: {
    titulo: "Código de despegue",
    texto: "Depósito lleno. Introduce el código completo uniendo los cuatro fragmentos recuperados.",
    mensaje: "Depósito al 100 %. Solo falta el código y nos vamos.",
    boton: "Iniciar despegue",
    error: "Código incorrecto. Une los fragmentos en orden: módulo 1, 2, 3 y 4.",
  },

  final: {
    titulo: "Despegue completado",
    mensaje: "La nave vuela con información verdadera. Estas son las claves que te llevas para tu día a día:",
    mensajeVero: "Rumbo a casa. Gracias por el combustible. Ahí abajo vas a necesitar ese filtro más que yo.",
    aprendido: [
      "Antes de compartir, para: la prisa es la mejor aliada de los bulos.",
      "Mira quién lo publica y quién lo firma. Una cuenta anónima o un «comunicado oficial» sin firma no son fuentes.",
      "Comprueba la fecha y el origen de las fotos con una búsqueda inversa de imágenes.",
      "Desconfía del lenguaje alarmista y de las promesas milagrosas.",
      "Que un texto esté bien escrito (o lo haya escrito una IA) no significa que sea cierto.",
      "Si dudas, consulta a los verificadores.",
    ],
    aviso: "Aviso: todas las cuentas, perfiles, medios y mensajes que aparecen en este juego son simulados y se han creado con fines educativos.",
    verificadores: [
      { nombre: "Maldita.es", url: "https://maldita.es" },
      { nombre: "Newtral", url: "https://www.newtral.es" },
      { nombre: "EFE Verifica", url: "https://verifica.efe.com" },
    ],
    creditos: "Una actividad de la Cátedra en Inteligencia Artificial y Tecnologías Emergentes para el Bienestar (Cátedra Ceuta-IATEB), de la Universidad de Granada y la Ciudad Autónoma de Ceuta, con motivo del Día Internacional de la Ciudad Educadora.",
    botonReiniciar: "Nueva misión",
  },
};
