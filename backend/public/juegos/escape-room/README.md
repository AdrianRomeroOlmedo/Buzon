# Operación Verdad

Escape room web sobre desinformación de la **Cátedra Ceuta-IATEB** (Universidad de Granada y Ciudad Autónoma de Ceuta), para alumnado de Bachillerato (unos 16 años). Se creó para el 30 de noviembre, Día Internacional de la Ciudad Educadora.

Quien juega está **en la Tierra**. Su terminal capta una señal de auxilio: Vere, un extraterrestre con la nave averiada en órbita, pide acceso remoto. Al aceptar la conexión, la pantalla pasa de la estática a la cámara de cabina de la nave: lo que se ve es lo que Vere ve, y quien juega analiza la información desde abajo mientras Vere pilota desde arriba.

El reactor de la nave funciona con **información verdadera**, y para recargarlo hay que superar cuatro módulos:

1. **Detectar el bulo**: dos publicaciones (red social, WhatsApp, medio digital o SMS); hay que elegir la verdadera.
2. **Verificar la noticia**: una publicación y un veredicto, «Verdadera» o «Bulo».
3. **Clasificar los bulos**: unir cada ejemplo con su tipo de desinformación mediante líneas. Se puede arrastrar, tocar un elemento de cada columna o usar el teclado.
4. **Cuestionario final**: seis preguntas, de una en una. Cada respuesta se comprueba y su explicación aparece siempre, también al acertar, antes de pasar a la siguiente. Si se falla, se puede volver a intentar.

Cada módulo da un 25 % de energía y un código de la secuencia de activación. Con los cuatro códigos se introduce la secuencia (`VERACIDAD`) y empieza la secuencia de despegue. Al despegar, la nave se aleja y **el enlace se pierde**: vuelve la estática con el rótulo «Señal perdida» y el canal de comunicación queda con el último mensaje recibido.

### Cronómetro: 20 minutos de batería

Las baterías de la nave solo mantienen el enlace **20 minutos**, y así lo explica Vere en la introducción. La cuenta atrás empieza al aceptar la conexión y se ve en el panel de estado y en el HUD del parabrisas:

- Con **5 minutos** restantes, el reloj pasa a ámbar y Vere avisa.
- Con **1 minuto**, pasa a rojo y Vere vuelve a avisar.
- **Al llegar a cero no se pierde la partida**: Vere conecta la reserva auxiliar y se puede terminar. El final indica si el despegue fue dentro de plazo (y con cuánta batería) o con la reserva auxiliar, y los datos de la partida lo registran.

## La escena

- **Parabrisas (imagen del enlace)**: la Vía Láctea y la Tierra, que son fotografías reales, con el marco de la cúpula, un HUD con la señal y la batería del enlace, y el rótulo «Cámara de cabina · Vere». Antes de conectar y después del despegue se ve estática.
- **Pantallas sin señal**: antes de aceptar la conexión, el panel de estado y el canal de comunicación muestran «Sin señal».
- **Iluminación**: depende de la energía. La nave empieza en emergencia, con luz roja, el cristal agrietado y pantallas con interferencias. Con cada módulo recargado, la luz pasa a azul.
- **Tres monitores encastrados en el panel de control**:
  - **Estado de la nave**: los cuatro módulos (pendiente, en curso o recargado), su código y el reactor.
  - **Pantalla principal**: donde se juega. En pantallas grandes tiene su propio desplazamiento.
  - **Canal de comunicación**: Vere, el extraterrestre, que reacciona a cada respuesta. Debajo están los ajustes: sonido (apagado al empezar), volumen, ampliar texto, datos de la partida y reiniciar.

El juego es una página estática: HTML, CSS y JavaScript, sin frameworks ni instalación. El sonido se genera en el propio navegador, sin archivos de audio.

## Privacidad

- **No hay ninguna petición externa al cargar.** La tipografía está incluida en `assets/fuentes/` y no se pide a Google.
- **Aviso de datos al cargar.** Antes de la portada aparece un aviso con lo que se guarda y lo que no. Hay que pulsar «Aceptar y jugar» para jugar. Se vuelve a pedir en cada partida nueva, porque puede jugar otra persona. Los textos están en `avisoDatos` de `content.js`.
- **Las métricas se guardan en la base de datos de la web**, solo con el aviso aceptado (ver «Base de datos» más abajo). No se guarda nombre, correo, IP ni cookies: cada partida se identifica solo por su código aleatorio.
- **Fuera de la web de la Cátedra** (abierto desde el disco o en GitHub Pages) no hay servidor: el envío falla sin avisar y el juego funciona igual, con los datos solo en el dispositivo.
- **Los enlaces externos** (las fuentes de cada caso, los verificadores y los créditos) solo se abren si se pulsan, en una pestaña nueva y sin enviar a esa web desde qué página se viene.

## Datos para la evaluación del piloto

Estos cambios responden a la «Propuesta integrada de evaluación de Operación Verdad».

### Datos de la partida

El botón **Datos de la partida**, en los ajustes, abre un resumen de la partida de ese dispositivo. Está disponible también en partidas sin terminar. Incluye:

- **Código de partida**: un código seudónimo (por ejemplo, `OV-7K3Q-M2PA`) que se crea al pulsar «Aceptar la conexión». Sirve para no confundir registros; no identifica a nadie.
- **Versión de la web**: el campo `edicion` de `content.js` y la versión del contenido.
- **Estado**: sin iniciar, en curso (con el módulo) o finalizada, y módulos recargados.
- **Tiempos**:
  - Tiempo total, desde «Aceptar la conexión» hasta el despegue (incluye pausas). Solo aparece en partidas finalizadas.
  - Tiempo transcurrido hasta ahora, en partidas sin terminar.
  - Tiempo activo aproximado: solo cuenta mientras la pestaña está visible y ha habido alguna interacción en los últimos 2 minutos.
- **Pistas solicitadas**, en total y por módulo. No incluye la ayuda del profesorado ni de compañeros.
- **Contadores por módulo**: cada uno mide una sola cosa, en lugar del antiguo contador mezclado de «errores».
  - Módulos 1 y 2: rondas acertadas al primer intento y respuestas incorrectas.
  - Módulo 3: conexiones correctas en la primera comprobación, comprobaciones con algún error y conexiones incorrectas en total.
  - Módulo 4: preguntas acertadas al primer intento, comprobaciones con error y respuestas incorrectas en total.
  - Códigos de activación incorrectos.
- **Batería**: si se agotaron los 20 minutos y, en partidas finalizadas, la batería restante al despegar.
- **Cuestionario de opinión**: respondido o sin responder.

Lo que aún no se ha podido observar aparece como **«No disponible»**, nunca como cero. Por ejemplo, el módulo 3 de una partida que se abandonó en el módulo 2.

El resumen se puede **copiar** (texto) o **descargar en CSV**: una fila por partida, separada por punto y coma y en UTF-8, lista para Excel o LibreOffice. Para unir varias partidas, se copian las filas de datos (sin la cabecera) en una misma hoja.

La pantalla final muestra el tiempo, las pistas y los **aciertos al primer intento** (de 17 respuestas posibles). Estos datos describen la partida: no son una calificación ni una medida de aprendizaje.

### Dispositivos compartidos

Al abrir la web con una partida guardada, se pregunta si se quiere **continuar** o **empezar una partida nueva**. Cada partida nueva tiene su propio código. Al empezar una partida nueva, la anterior se borra del dispositivo, pero lo que ya se envió sigue en la base de datos. Sin servidor (GitHub Pages o el archivo abierto desde el disco), conviene copiar o descargar antes los datos de la anterior.

### Cuestionario de opinión

Es el cuestionario del apartado 6 de la propuesta, con los textos tal cual, en `cuestionarioOpinion` de `content.js`. Si se cambia algún ítem, hay que cambiar también su `version`.

- **Cuándo**: es opcional. Se ofrece al terminar la misión y también se puede abrir desde «Datos de la partida», aunque la misión haya quedado incompleta.
- **Cómo se responde**: 12 frases con escala del 1 al 5 y **NV** («No puedo valorarlo»), y dos preguntas abiertas opcionales. Se puede dejar cualquier pregunta sin responder. No hay que escribir ningún código: las respuestas se guardan junto al código de la partida, que se muestra encima de las preguntas. No se pide el nombre.
- **Dónde se guardan las respuestas**: en el dispositivo y, con el aviso aceptado, en la base de datos, junto a los datos de la partida.
- **También se pueden entregar** con **Descargar respuestas (CSV)** o **Copiar**. Las respuestas van en las mismas columnas del CSV de la partida (`item_1` a `item_12`, `pregunta_13` y `pregunta_14`):
  - Las respuestas numéricas se guardan como 1 a 5.
  - NV se guarda como `NV`.
  - Las preguntas sin responder quedan **vacías**: no se convierten en 3 ni en 0.

### Base de datos

Las métricas de cada partida se guardan en la tabla `partidas_escape_room` de la web Laravel:

- **Cuándo se envían**: en cada cambio de pantalla, al guardar el cuestionario de opinión y al cerrar o cambiar de pestaña. Cada envío es la instantánea completa y sustituye a la anterior, así que una partida abandonada a medias también queda registrada hasta donde llegó.
- **Qué se guarda**: `codigo`, `estado`, `finalizada`, `version_web`, la versión del aviso aceptado y su fecha, `datos` (JSON con las mismas columnas que el CSV de «Datos de la partida») y `opinion` (JSON con las columnas del cuestionario, o vacío).
- **A dónde se envían**: `POST /escape-room/partidas` (ruta `escape-room.partidas.store`). La dirección está en `ajustes.registro.url` de `content.js`; con `url: ""` no se envía nada.
- **Protección**: el servidor rechaza envíos sin el aviso aceptado, con un código mal formado o con campos raros, y limita las peticiones por IP (300 por minuto, porque en un aula muchos equipos comparten la misma IP). La IP no se guarda.
- **Borrar una partida**: por su código, que aparece en «Datos de la partida».

Antes de usarlo en la web publicada hay que ejecutar las migraciones (`php artisan migrate`).

## Archivos

| Archivo | Para qué sirve |
| --- | --- |
| `index.html` | La estructura de la cabina. |
| `content.js` | **Todo el contenido**: textos, publicaciones, respuestas, explicaciones, fuentes, pistas y preguntas. |
| `styles.css` | El aspecto: colores, iluminación, monitores y animaciones. |
| `app.js` | El funcionamiento del juego. No hace falta tocarlo para cambiar textos. |
| `assets/` | Imágenes: `espacio.jpg` (Vía Láctea), `tierra.jpg` (Tierra), `chapa.jpg` (textura del panel), `papa-plumifero-ia.webp` (imagen falsa del módulo 2) y `favicon.svg`. En `assets/fuentes/`, la tipografía Barlow y su licencia. |

## Probarlo en tu ordenador

Haz doble clic en `index.html`. No hace falta servidor.

## Cómo editar el contenido

Abre `content.js` con cualquier editor de texto. Está comentado por secciones.

- Cambia solo el texto entre comillas `"así"`. Si necesitas comillas dentro de un texto, usa las españolas `«así»`.
- No borres las comas, ni las llaves `{ }`, ni los corchetes `[ ]`.
- Las líneas que empiezan por `//` son comentarios.
- Cuando cambies las pruebas, **sube el número de `version`** para que nadie continúe una partida antigua.
- Guarda y recarga la página. Si algo se rompe, la pantalla principal mostrará un aviso: casi siempre falta una coma o una comilla.

Ejemplos:

- **Cambiar una publicación de la prueba 1:** en `detectar → rondas`, cada ronda tiene dos `tarjetas`. Marca la verdadera con `verdadera: true`. En las publicaciones de red social, `imagen` es la descripción de la foto y `foto` la ruta del archivo (por ejemplo, `assets/mi-foto.webp`); sin `foto`, se ve solo la descripción en un recuadro. El campo `formato` puede ser `"red"`, `"whatsapp"`, `"noticia"` o `"sms"`, y cada uno usa sus propios campos (copia una tarjeta del mismo formato como modelo).
- **Prueba 3:** cada ejemplo indica en `tipo` el `id` de su tipo de desinformación. Debe haber tantos ejemplos como tipos, uno por tipo.
- **Cuestionario:** `correcta` es la posición de la respuesta buena, **empezando a contar en 0**.
- **Secuencia de activación:** es la unión de los cuatro `fragmento` (en el juego se llaman «códigos»), en orden.
- **Cronómetro:** en `ajustes → temporizador` se cambian los minutos (`minutos: 20`), la etiqueta y los avisos de Vere (`aviso5`, `aviso1`, `agotado`). Con `activo: false` desaparece la cuenta atrás. Si cambias los minutos, revisa también los textos de la portada, la introducción y el final que los mencionan.

### Rigor del contenido

- Las publicaciones que aparecen en pantalla son **simulaciones educativas**: el formato, las cuentas y los medios son inventados y llevan la etiqueta «Simulación educativa». Cada caso se basa en un hecho o un bulo real, y su explicación enlaza a la verificación original (campo `fuentes`).
- **Enlaza solo fuentes que hayas comprobado.** No inventes referencias.
- **Un bulo es una información que se ha comprobado como falsa.** Si de algo no hay pruebas ni a favor ni en contra, no es un bulo: es una afirmación no demostrada. El cuestionario incluye una pregunta sobre esa diferencia.
- La prueba 3 usa la tipología de Claire Wardle (First Draft) y del informe del Consejo de Europa sobre el desorden informativo.

Fuentes que usa el contenido actual (todas comprobadas):

- [Maldita.es: No, la limonada o el agua con limón no curan el cáncer](https://maldita.es/malditaciencia/20191211/no-la-limonada-o-el-agua-con-limon-no-curan-el-cancer/)
- [NASA: Great Wall](https://www.nasa.gov/image-article/great-wall/) y [China's Wall Less Great in View from Space (PDF)](https://www.nasa.gov/wp-content/uploads/static/history/PDFs/great_wall_less_great_from_space.pdf)
- [INCIBE: Suplantadas por SMS empresas de paquetería](https://www.incibe.es/ciudadania/avisos/suplantadas-por-sms-empresas-de-paqueteria-como-correos-y-correos-express)
- [OMS: ¿Cómo detener la resistencia a los antibióticos?](https://www.who.int/es/news-room/commentaries/detail/how-to-stop-antibiotic-resistance-here-s-a-who-prescription)
- [Maldita.es: No, WhatsApp no costará 0,01 euros por mensaje](https://maldita.es/malditobulo/20200624/no-whatsapp-no-costara-001-euros-por-mensaje-a-partir-del-sabado-ni-tu-logo-se-volvera-azul-si-reenvias-la-cadena-a-10-contactos/)
- [Maldita.es: Las imágenes del papa con un abrigo de plumas están creadas con IA](https://maldita.es/malditatecnologia/20230327/papa-francisco-abrigo-plumas-bulo-inteligencia-artificial)
- [Claire Wardle (First Draft): Fake news. It's complicated](https://firstdraftnews.org/articles/fake-news-complicated/)
- [Consejo de Europa: Information Disorder (Wardle y Derakhshan, 2017)](https://rm.coe.int/information-disorder-toward-an-interdisciplinary-framework-for-researc/168076277c)
- [Wineburg y McGrew (Stanford): Lateral Reading](https://inquirygroup.org/about/updates/2018/lateral-reading-reading-less-and-learning-more-online)

## Imágenes y licencias

| Archivo | Origen | Licencia |
| --- | --- | --- |
| `assets/espacio.jpg` | Recorte de la panorámica de la Vía Láctea de [ESO/S. Brunier](https://www.eso.org/public/images/eso0932a/) | CC BY 4.0: hay que citar «ESO/S. Brunier» |
| `assets/tierra.jpg` | «The Blue Marble», [NASA, misión Apollo 17](https://commons.wikimedia.org/wiki/File:The_Earth_seen_from_Apollo_17.jpg) | Dominio público |
| `assets/chapa.jpg` | Textura [metal_plate_02 de Poly Haven](https://polyhaven.com/a/metal_plate_02) | CC0 |
| `assets/papa-plumifero-ia.webp` | Imagen **falsa** del papa Francisco con un plumífero, generada con IA (Midjourney) por Pablo Xavier en 2023 y verificada por [Maldita.es](https://maldita.es/malditatecnologia/20230327/papa-francisco-abrigo-plumas-bulo-inteligencia-artificial). Se usa en la ronda 3 del módulo 2 | Se reproduce con fines educativos, para analizar un caso de desinformación. Las imágenes generadas solo con IA no suelen tener derechos de autor, pero muestra a una persona real: úsala solo en este contexto |
| `assets/fuentes/*.woff2` | Tipografía [Barlow](https://github.com/jpt/barlow) (Barlow y Barlow Condensed) | SIL Open Font License 1.1 (`assets/fuentes/OFL.txt`) |

Los créditos aparecen en la pantalla final (campo `creditosImagenes` de `content.js`). Si cambias una imagen, actualiza también su crédito.

## Colores y aspecto

Los colores están al principio de `styles.css`, en `:root`:

- `--ion`: cian de la interfaz.
- `--ambar`: energía y código.
- `--alerta` y `--verificado`: rojo y verde de las respuestas.

La luz de cada nivel de energía está en las reglas `body[data-energia="…"]`. Vere está dibujado en SVG en `app.js` (función `alienSVG`).

## Publicarlo en GitHub Pages, paso a paso

1. Entra en [github.com](https://github.com) y pulsa **New repository**. Ponle un nombre, márcalo como **Public** y pulsa **Create repository**.
2. Pulsa **uploading an existing file** y arrastra el contenido de esta carpeta: `index.html`, `content.js`, `app.js`, `styles.css`, `README.md` y la carpeta `assets`. `index.html` debe quedar en la raíz.
3. Pulsa **Commit changes**.
4. En **Settings → Pages**, elige **Deploy from a branch**, rama **main**, carpeta **/ (root)**, y pulsa **Save**.
5. En uno o dos minutos aparecerá la dirección pública, del tipo `https://tu-usuario.github.io/nombre-del-repositorio/`.

## En la web de la Cátedra

Esta carpeta se sirve también desde la web de la Cátedra, en `/juegos/escape-room/index.html`. La dirección `/escape-room` redirige allí.

## Accesibilidad

- Se puede jugar entero con teclado. En la prueba 3 se elige un ejemplo y después su tipo con Tab y Enter.
- Los aciertos, fallos y estados se indican con texto e iconos, no solo con colores.
- Los avisos y las reacciones de Vere se anuncian a los lectores de pantalla.
- Las animaciones se desactivan si el sistema tiene activado «reducir movimiento».
- El botón **Ampliar** agranda la letra para proyectar.
- La ventana de datos de la partida se cierra con Esc y devuelve el foco al juego.
