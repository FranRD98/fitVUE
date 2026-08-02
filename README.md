# fitVUE

Aplicación para seguimiento de entrenamientos, dietas y progreso físico. Proyecto único de Laravel (backend + API) con Vue 3 integrado de forma nativa vía Vite (`resources/js`), sin servicios externos de pago (Supabase sustituido por MySQL propio).

## Stack

- **Backend**: Laravel 13, MySQL, Sanctum (auth por token), Stripe (pagos)
- **Frontend**: Vue 3, Vue Router, Pinia, Tailwind CSS 4, Chart.js — todo dentro de `resources/js/`, compilado con el Vite de Laravel
- Un único proyecto, un único `Document Root` (`public/`), sin frontend separado

## Desarrollo local

Requisitos: PHP 8.3+, Composer, Node.js, MySQL.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# Configura DB_* en .env con tu MySQL local, luego:
php artisan migrate
php artisan storage:link
php artisan admin:sync   # crea/asegura la cuenta admin@fitvue.es
```

Arranca todo con:

```bash
composer run dev
```

(o por separado: `php artisan serve` + `npm run dev`)

## Importar datos reales de Supabase

Si tienes un dump de `pg_dumpall` de la antigua base de Supabase:

```bash
php artisan app:import-supabase-dump /ruta/al/dump.sql
```

Migra usuarios, rutinas, ejercicios, dietas, ingredientes, guías y revisiones, remapeando los UUID de Supabase a IDs de MySQL y conservando los hashes de contraseña originales (bcrypt, compatibles con Laravel).

## Despliegue

Ver [DEPLOY.md](DEPLOY.md) para la guía completa de despliegue en Plesk.
