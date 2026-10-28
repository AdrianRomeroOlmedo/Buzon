#!/usr/bin/env bash
#
# Despliegue de ceutamepreocupa.es en Hostinger (o cualquier servidor con SSH).
#
# Se ejecuta EN EL SERVIDOR, desde la copia del repositorio:
#   ./desplegar.sh            → publica lo último de la rama main
#   ./desplegar.sh volver     → vuelve al commit que había antes del último despliegue
#
# Qué hace: descarga main, instala dependencias, ejecuta migraciones pendientes y limpia
# las cachés de Laravel. Mientras tanto la web muestra el aviso de mantenimiento.
#
# Qué NO toca: backend/.env, backend/storage/ (logs, sesiones, token e imagen de Instagram)
# ni backend/public/build/ (ficheros de Vite del panel, que no están en Git).
#
# Variables opcionales: RAMA (por defecto main), PHP (por defecto php), COMPOSER (por defecto composer).

set -euo pipefail

RAMA="${RAMA:-main}"
PHP="${PHP:-php}"
COMPOSER="${COMPOSER:-composer}"

REPO="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP="$REPO/backend"
ULTIMO="$APP/storage/app/ultimo-despliegue" # storage/ no está en Git: sobrevive a los despliegues

paso() { printf '\n\033[1;34m▶ %s\033[0m\n' "$1"; }
error() { printf '\n\033[1;31m✖ %s\033[0m\n' "$1" >&2; exit 1; }

cd "$REPO"

[ -f "$APP/.env" ] || error "Falta $APP/.env. Cópialo antes de desplegar (ver guía de montaje)."

# Los cambios hechos a mano en el servidor se perderían o bloquearían el pull: mejor parar.
if [ -n "$(git status --porcelain --untracked-files=no)" ]; then
    git status --short --untracked-files=no
    error "Hay ficheros del repositorio modificados en el servidor. Revísalos antes de desplegar."
fi

ANTERIOR="$(git rev-parse HEAD)"

if [ -f "$APP/vendor/autoload.php" ]; then
    paso "Activando modo mantenimiento"
    (cd "$APP" && "$PHP" artisan down --retry=15) || true
else
    paso "Primera instalación: todavía no hay vendor/, se omite el modo mantenimiento"
fi
# Pase lo que pase a partir de aquí, la web vuelve a estar disponible al terminar.
trap '(cd "$APP" && "$PHP" artisan up >/dev/null 2>&1) || true' EXIT

if [ "${1:-}" = "volver" ]; then
    [ -f "$ULTIMO" ] || error "No hay ningún despliegue anterior registrado."
    DESTINO="$(cat "$ULTIMO")"
    paso "Volviendo a ${DESTINO:0:7} (las migraciones no se deshacen)"
    git checkout --quiet --detach "$DESTINO"
else
    paso "Descargando $RAMA desde GitHub"
    git fetch --quiet origin "$RAMA"
    git checkout --quiet "$RAMA"
    git merge --ff-only --quiet "origin/$RAMA" \
        || error "La rama $RAMA del servidor no puede avanzar sin fusionar. Revisa el repositorio a mano."
fi

if [ "$(git rev-parse HEAD)" = "$ANTERIOR" ] && [ "${1:-}" != "volver" ]; then
    printf '\nYa estaba publicada la última versión (%s). Se limpian cachés igualmente.\n' "$(git log -1 --format='%h %s')"
fi

cd "$APP"

paso "Instalando dependencias de PHP"
"$COMPOSER" install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress

paso "Ejecutando migraciones pendientes"
"$PHP" artisan migrate --force

paso "Limpiando cachés de configuración, rutas y vistas"
"$PHP" artisan config:clear
"$PHP" artisan route:clear
"$PHP" artisan view:clear

if [ ! -f "$APP/public/build/manifest.json" ]; then
    printf '\n\033[1;33m⚠ Falta backend/public/build/: el login y el panel de revisión se verán sin estilos.\033[0m\n'
    printf '  Compílalo en local con "npm run build" y súbelo a esa carpeta.\n'
fi

paso "Desactivando modo mantenimiento"
"$PHP" artisan up

echo "$ANTERIOR" > "$ULTIMO"

printf '\n\033[1;32m✔ Publicado: %s\033[0m\n' "$(git -C "$REPO" log -1 --format='%h %s (%cr)')"
printf '  Si algo va mal: ./desplegar.sh volver\n'
