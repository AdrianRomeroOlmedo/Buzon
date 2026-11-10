# Misión Combustible de Verdad

Escape room web sobre desinformación y bulos de la **Cátedra Ceuta-IATEB** (Universidad de Granada y Ciudad Autónoma de Ceuta), creado para el 30 de noviembre, Día Internacional de la Ciudad Educadora.

Está pensado para alumnado de Bachillerato (unos 16 años). Se juega en primera persona desde la cabina de una nave alienígena:

- Arriba está el parabrisas, con la Tierra, el HUD de vuelo y el marco de la cúpula.
- Abajo está el panel de control, con tres monitores y una fila de mandos:
  - **instrumentos**: depósito, código de despegue y estado de la nave;
  - **pantalla principal**, donde se juegan las pruebas;
  - **canal de comunicación**, donde Vero, el extraterrestre, aparece en videollamada.

En pantallas grandes, la cabina ocupa toda la ventana y el juego se desplaza dentro del monitor central.

La nave funciona con **verdad** y se ha quedado sin combustible en órbita. Quien juega supera 4 módulos. Cada uno llena un 25 % del depósito y entrega un fragmento del código de despegue:

1. **¿Cuál es verdad?** Dos mensajes por ronda y hay que elegir el verdadero.
2. **¿Verdad o bulo?** Una información por ronda y dos botones.
3. **Emparejar** cinco noticias con cinco técnicas de desinformación (arrastrando o tocando).
4. **Infografía** sobre cómo verificar información y un test de cuatro preguntas.

El juego es una página estática: HTML, CSS y JavaScript, sin frameworks ni instalación. No guarda datos en ningún servidor. El progreso queda solo en el navegador de quien juega.

## Archivos

| Archivo | Para qué sirve |
| --- | --- |
| `index.html` | La página. Normalmente no hace falta tocarla. |
| `content.js` | **Todo el contenido**: textos, noticias, respuestas, explicaciones, pistas y preguntas. |
| `styles.css` | El aspecto: colores, tamaños y animaciones. |
| `app.js` | El funcionamiento del juego. No hace falta tocarlo para cambiar textos. |
| `assets/` | Imágenes: la infografía (`infografia.png`) y el icono de la pestaña (`favicon.svg`). |

## Probarlo en tu ordenador

Haz doble clic en `index.html` y se abrirá en el navegador. No hace falta servidor.

## Cómo editar el contenido

Abre `content.js` con cualquier editor de texto (el Bloc de notas sirve, aunque es más cómodo [Visual Studio Code](https://code.visualstudio.com/)). Está comentado por secciones.

Reglas básicas:

- Cambia solo el texto que va entre comillas `"así"`. Si necesitas comillas dentro de un texto, usa las españolas `«así»`.
- No borres las comas que separan los elementos, ni las llaves `{ }` ni los corchetes `[ ]`.
- Las líneas que empiezan por `//` son comentarios y no se ven en el juego.
- Cuando cambies las pruebas, **sube el número de `version`** (por ejemplo, de `1` a `2`). Así nadie continúa una partida guardada con el contenido antiguo.
- Guarda y recarga la página. Si algo se ha roto, el juego mostrará un aviso en lugar de las pantallas. Revisa el último cambio: casi siempre falta una coma o una comilla.

Ejemplos:

- **Cambiar una noticia de la prueba 2:** busca `verdadOBulo`, y dentro de `rondas` cambia `texto`, `esVerdad` (`true` si es verdad, `false` si es bulo), `explicacion` y `pista`.
- **Añadir una ronda:** copia una ronda entera, desde su `{` hasta su `},` (con la coma), y pégala justo debajo. Después cambia sus textos.
- **Cambiar la respuesta correcta de una pregunta del test:** `correcta` es la posición de la opción buena, **empezando a contar en 0**. La primera opción es `0`, la segunda `1`, y así sucesivamente.
- **Emparejar:** cada noticia indica en `tecnica` el `id` de su técnica (`"alarmista"`, `"anonima"`, `"oficial"`, `"imagen"` o `"urgencia"`).
- **Activar el temporizador:** en `ajustes`, pon `temporizador: { activo: true, minutos: 20 }`. Al llegar a cero solo aparece un aviso: se puede seguir jugando.
- **Cambiar el código final:** es la unión de los cuatro `fragmento` de las pruebas (ahora `VER` + `IFI` + `CA` + `DOR` = `VERIFICADOR`).

### Antes de usarlo con público

El contenido actual es **provisional**. Todas las cuentas, nombres y medios son inventados. Cada noticia lleva la marca `// TODO: sustituir por caso real verificado` y un campo `fuente` vacío. Para cada caso:

1. Sustituye el texto por un caso real ya verificado.
2. Rellena `fuente` con la URL del desmentido (Maldita.es, Newtral, EFE Verifica…) o de la noticia original.
3. Mantén inventados los nombres de las cuentas y perfiles: no imites a personas, medios ni instituciones reales.
4. Borra la línea `// TODO` cuando el caso esté revisado.

## Cómo cambiar las imágenes y los colores

- **Infografía:** guarda tu imagen como `assets/infografia.png`. Si el archivo existe, el juego la muestra. Si no existe, dibuja una versión con los 5 pasos de `content.js`. Si cambias la imagen, actualiza también `textoAlternativo` en `content.js` (es lo que lee un lector de pantalla) y revisa que las preguntas del test se puedan responder con ella.
- **La Tierra del parabrisas:** es `assets/tierra.jpg`, la fotografía «The Blue Marble» de la misión Apollo 17 de la NASA. Es de **dominio público**, y los créditos del juego lo indican. Para cambiarla, sustituye el archivo por otra imagen cuadrada con la Tierra centrada sobre fondo negro. Si falta, se dibuja una Tierra azul con CSS.
- **Icono de la pestaña:** sustituye `assets/favicon.svg`.
- **Colores:** están al principio de `styles.css`, en el bloque `:root`. Por ejemplo, `--ambar` es el color de los botones y del combustible, y `--holo` el cian del HUD y de los rótulos. Comprueba que el texto se sigue leyendo bien sobre los colores nuevos.
- **Vero** está dibujado en SVG dentro de `app.js` (función `alienSVG`), con cuatro expresiones.
- **El parabrisas, el panel de control y los mandos** están hechos con HTML, SVG y CSS: el marco, el HUD y los mandos en `index.html`, y su aspecto en las secciones «Parabrisas» y «Panel de control» de `styles.css`.
- **Lo que dice Vero** en cada módulo está en el campo `mensaje` de cada prueba en `content.js`. Las instrucciones que se leen en la pantalla principal están en `instrucciones`.

## Publicarlo en GitHub Pages, paso a paso

1. Entra en [github.com](https://github.com) con tu cuenta (o créala).
2. Pulsa **New repository**. Ponle un nombre, por ejemplo `escape-room-verdad`, márcalo como **Public** y pulsa **Create repository**.
3. En la página del repositorio vacío, pulsa **uploading an existing file**.
4. Arrastra **el contenido** de esta carpeta: `index.html`, `content.js`, `app.js`, `styles.css`, `README.md` y la carpeta `assets`. `index.html` tiene que quedar en la raíz del repositorio, no dentro de otra carpeta.
5. Escribe un mensaje (por ejemplo, «Primera versión») y pulsa **Commit changes**.
6. Ve a **Settings → Pages**. En **Build and deployment**, elige **Source: Deploy from a branch**, rama **main** y carpeta **/ (root)**, y pulsa **Save**.
7. Espera uno o dos minutos y recarga esa página. Aparecerá la dirección pública, del tipo `https://tu-usuario.github.io/escape-room-verdad/`.

Para actualizarlo, sube los archivos cambiados de la misma forma (**Add file → Upload files**). La web se actualiza sola en uno o dos minutos.

## En la web de la Cátedra

Esta carpeta también se sirve desde la web de la Cátedra, en `/juegos/escape-room/index.html`, y la dirección `/escape-room` redirige allí. Se publica con el resto de la web, sin ningún paso extra.

## Accesibilidad

- Se puede jugar entero con teclado. En la prueba de emparejar, se elige una noticia y después su técnica con Tab y Enter.
- Los aciertos y fallos se indican con texto y símbolos (✓ y ✕), no solo con colores.
- Los avisos se anuncian a los lectores de pantalla.
- Las animaciones se reducen si el sistema tiene activado «reducir movimiento».
- El botón **A+** de los instrumentos agranda el texto para proyectarlo en pantalla.
