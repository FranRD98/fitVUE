#!/bin/sh
# Post-deployment actions (Plesk → Git → fitvue.franriera.es → "Additional
# deployment actions"). Se ejecuta tras el "git pull" automático, en la raíz
# del proyecto (el "pwd" del log lo confirma).
#
# No usa "set -e": si un paso falla se deja constancia en el log y se sigue
# con el resto en vez de cortar el despliegue a medias (p. ej. si composer
# install falla pero las migraciones sí podrían aplicarse igualmente).
#
# Ajusta PHP_BIN si cambia la versión/ruta de PHP en Plesk (se puede
# sobrescribir con la variable de entorno PHP_BIN, útil para probar el
# script en local con otra ruta de PHP).
PHP_BIN="${PHP_BIN:-/opt/plesk/php/8.3/bin/php}"

run() {
  echo "+ $*"
  "$@"
  echo "  -> exit $?"
}

{
  echo "=== Deploy $(date '+%Y-%m-%d %H:%M:%S') ==="
  pwd
  run "$PHP_BIN" -v

  # vendor/ no viaja en el repo (.gitignore): hace falta tras cualquier
  # cambio en composer.json/composer.lock (p. ej. al añadir resend/resend-php).
  run "$PHP_BIN" "$(command -v composer || echo /usr/local/bin/composer)" install --no-dev --optimize-autoloader --no-interaction

  run "$PHP_BIN" artisan migrate --force

  # Enlace storage -> public/storage. Si ya existe, Laravel lo avisa y
  # continúa (no hace fallar el script).
  run "$PHP_BIN" artisan storage:link

  # Config/rutas/vistas cacheadas: hay que regenerarlas en cada deploy o
  # Laravel sigue sirviendo la versión vieja (p. ej. el cambio de
  # APP_LOCALE no se aplicaría sin este paso).
  run "$PHP_BIN" artisan config:clear
  run "$PHP_BIN" artisan config:cache
  run "$PHP_BIN" artisan route:clear
  run "$PHP_BIN" artisan route:cache
  run "$PHP_BIN" artisan view:clear
  run "$PHP_BIN" artisan view:cache

  echo "=== Fin del deploy ==="
} > public/deploy-log.txt 2>&1
