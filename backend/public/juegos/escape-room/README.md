# Operación Verdad

Escape room web sobre desinformación de la **Cátedra Ceuta-IATEB** (Universidad de Granada y Ciudad Autónoma de Ceuta), para alumnado de Bachillerato (unos 16 años). Se creó para el 30 de noviembre, Día Internacional de la Ciudad Educadora.

Quien juega está en la cabina de una nave extraterrestre averiada en órbita. El reactor funciona con **información verdadera**, y para recargarlo hay que restaurar cuatro módulos:

1. **Detectar el bulo**: dos publicaciones (red social, WhatsApp, medio digital o SMS); hay que elegir la verdadera.
2. **Verificar la noticia**: una publicación y un veredicto, «Verdadera» o «Bulo».
3. **Clasificar los bulos**: unir cada ejemplo con su tipo de desinformación mediante líneas. Se puede arrastrar, tocar un elemento de cada columna o usar el teclado.
4. **Cuestionario final**: seis preguntas con corrección. Las falladas se pueden repetir.

Cada módulo da un 25 % de energía y un fragmento del código de activación. Con los cuatro fragmentos se introduce el código (`VERACIDAD`) y empieza la secuencia de despegue.

## La escena

- **Parabrisas**: la Vía Láctea y la Tierra, que son fotografías reales, con el marco de la cúpula y un HUD proyectado.
- **Iluminación**: depende de la energía. La nave empieza en emergencia, con luz roja, el cristal agrietado y pantallas con interferencias. Con cada módulo restaurado, la luz pasa a azul.
- **Tres monitores encastrados en el panel de control**:
  - **Estado de la nave**: los cuatro módulos (pendiente, en curso o restaurado), su fragmento del código y el reactor.
  - **Pantalla principal**: donde se juega. En pantallas grandes tiene su propio desplazamiento.
  - **Canal de comunicación**: Vero, el extraterrestre, que reacciona a cada respuesta. Debajo están los ajustes: sonido (apagado al empezar), volumen, ampliar texto y reiniciar.

El juego es una página estática: HTML, CSS y JavaScript, sin frameworks ni instalación. No envía datos a ningún servidor. El progreso se guarda solo en el navegador de quien juega. El sonido se genera en el propio navegador, sin archivos de audio.

## Archivos

| Archivo | Para qué sirve |
| --- | --- |
| `index.html` | La estructura de la cabina. |
| `content.js` | **Todo el contenido**: textos, publicaciones, respuestas, explicaciones, fuentes, pistas y preguntas. |
| `styles.css` | El aspecto: colores, iluminación, monitores y animaciones. |
| `app.js` | El funcionamiento del juego. No hace falta tocarlo para cambiar textos. |
| `assets/` | Imágenes: `espacio.jpg` (Vía Láctea), `tierra.jpg` (Tierra), `chapa.jpg` (textura del panel) y `favicon.svg`. |

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

- **Cambiar una publicación de la prueba 1:** en `detectar → rondas`, cada ronda tiene dos `tarjetas`. Marca la verdadera con `verdadera: true`. El campo `formato` puede ser `"red"`, `"whatsapp"`, `"noticia"` o `"sms"`, y cada uno usa sus propios campos (copia una tarjeta del mismo formato como modelo).
- **Prueba 3:** cada ejemplo indica en `tipo` el `id` de su tipo de desinformación. Debe haber tantos ejemplos como tipos, uno por tipo.
- **Cuestionario:** `correcta` es la posición de la respuesta buena, **empezando a contar en 0**.
- **Código de activación:** es la unión de los cuatro `fragmento`, en orden.
- **Temporizador:** en `ajustes`, pon `temporizador: { activo: true, minutos: 25 }`. Al llegar a cero solo aparece un aviso.

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

Los créditos aparecen en la pantalla final (campo `creditosImagenes` de `content.js`). Si cambias una imagen, actualiza también su crédito.

## Colores y aspecto

Los colores están al principio de `styles.css`, en `:root`:

- `--ion`: cian de la interfaz.
- `--ambar`: energía y código.
- `--alerta` y `--verificado`: rojo y verde de las respuestas.

La luz de cada nivel de energía está en las reglas `body[data-energia="…"]`. Vero está dibujado en SVG en `app.js` (función `alienSVG`).

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
- Los avisos y las reacciones de Vero se anuncian a los lectores de pantalla.
- Las animaciones se desactivan si el sistema tiene activado «reducir movimiento».
- El botón **Ampliar texto** agranda la letra para proyectar.
