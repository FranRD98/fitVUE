# Desplegar FitVue en Plesk

Un único proyecto Laravel con Vue integrado (vía Vite) sirviendo todo desde la raíz del repositorio. Nada de subcarpetas ni dominios separados.

## 1. Requisitos en Plesk

- **PHP 8.3 o superior** (con 8.2 falla `composer install`: Laravel 13 y varias dependencias lo exigen)
- Extensiones PHP: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `curl`, `bcmath`, `ctype`, `tokenizer`, `xml`
- Extensión "Git" (o SSH/SFTP) para desplegar el repo
- Una base de datos MySQL (Plesk → Bases de datos)
- Composer (por SSH o el toolkit de Plesk)

No hace falta Node.js en el servidor: el build de Vue ya viaja compilado y versionado dentro de `public/build/`.

## 2. Base de datos

Plesk → Bases de datos → crea una BD (p. ej. `fitvue`) y un usuario con todos los privilegios. Anota host/usuario/contraseña — **usa `DB_CONNECTION=mysql`, no `sqlite`** (es el valor por defecto de un Laravel recién creado; si no lo cambias, `migrate` crea un SQLite local en vez de tocar tu base MySQL real, y no te dará ningún error visible).

## 3. Desplegar el código

En Plesk, sobre el dominio/subdominio:

1. Configura el despliegue Git apuntando al repo de GitHub, rama `main`.
2. Al buscar la aplicación Laravel ("scan for existing applications"), debería detectarla automáticamente: `artisan` está en la raíz del repo, que es justo lo que exige el Toolkit.
3. **Document Root** → `public/` (la carpeta `public` de la raíz del repo, no una subcarpeta).

## 4. `.env` de producción

Plesk no sube el `.env` (está en `.gitignore` a propósito). Créalo a mano en la raíz del proyecto:

```env
APP_NAME=FitVue
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://tu-dominio.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=<tu_bd_plesk>
DB_USERNAME=<tu_usuario_plesk>
DB_PASSWORD=<tu_password_plesk>

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=log

STRIPE_SECRET=sk_live_xxxxxxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxx
STRIPE_PRICE_PREMIUM=price_xxxxxxxxxxxx
STRIPE_PRICE_PRO=price_xxxxxxxxxxxx

ADMIN_EMAIL=admin@fitvue.es
ADMIN_PASSWORD=<tu contraseña real>
ADMIN_NAME=Admin
ADMIN_LAST_NAME=FitVue
```

Dejar `APP_KEY` vacío: se rellena solo en el siguiente paso.

## 5. Comandos (por SSH o el "Ejecutar comando" del Toolkit de Laravel)

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate --force
php artisan migrate --force --seed
php artisan storage:link
php artisan config:cache
php artisan route:cache
```

`--seed` ya deja la app lista para usar, sin pasos extra: crea la cuenta admin garantizada y carga un catálogo de arranque (categorías, ejercicios, rutinas, dietas, ingredientes y guías de ejemplo, todo atribuido a la cuenta admin, sin usuarios reales ni datos personales). Si prefieres migrar y sembrar por separado: `php artisan migrate --force` y luego `php artisan db:seed --force`.

Da permisos de escritura a `storage/` y `bootstrap/cache/` si Plesk no lo hace automáticamente (`chmod -R 775`).

## 6. SSL

Activa el certificado Let's Encrypt para el dominio desde Plesk.

## 7. Importar tus datos reales (opcional, si vienes de Supabase)

El paso anterior ya deja la app funcional con contenido de catálogo. Si además quieres tus usuarios, rutinas y dietas reales de la antigua Supabase (con sus contraseñas originales, hashes bcrypt compatibles), sube tu dump de `pg_dumpall` al servidor (por SFTP, a una ruta fuera de `public/`) y ejecuta:

```bash
php artisan app:import-supabase-dump /ruta/al/dump.sql
```

Esto **sustituye** el catálogo de ejemplo por tus datos reales (usuarios, rutinas, ejercicios, dietas, ingredientes, guías y revisiones), fuerza la cuenta admin garantizada y reenlaza las imágenes de `storage/app/public` si ya las subiste ahí.

**Borra el dump del servidor en cuanto termines** — contiene datos personales reales (emails, contraseñas hasheadas).

## 8. Cuenta admin garantizada

`admin@fitvue.es` está protegida por `php artisan admin:sync`, que fuerza su rol a `admin` y su contraseña al valor de `ADMIN_PASSWORD` del `.env`. Ejecútalo (ya se hace solo tras `app:import-supabase-dump`) siempre que necesites recuperar el acceso porque alguien cambió esa contraseña desde la app.

## 9. Stripe en producción

- Dashboard de Stripe → añade un webhook `https://tu-dominio.com/api/stripe/webhook` escuchando `checkout.session.completed`, y copia el signing secret a `STRIPE_WEBHOOK_SECRET`.
- En cada Payment Link (Premium y Pro), configura la redirección "after payment" a `https://tu-dominio.com/pago-aceptado?session_id={CHECKOUT_SESSION_ID}`.
- Sustituye las claves y precios de test por los reales (modo live).

## 10. Publicar cambios del frontend

El build de Vue vive versionado en `public/build/` para no depender de Node en el servidor. Cuando cambies algo del frontend:

```bash
npm run build
git add public/build
git commit -m "Actualizar build del frontend"
git push
```

Luego actualiza el despliegue Git en Plesk (o `git pull` por SSH). Si solo cambió el frontend, no hace falta repetir `composer install` ni migraciones.

## 11. Checklist rápido

- [ ] PHP 8.3+ activo en el dominio
- [ ] BD MySQL creada en Plesk
- [ ] Document Root → `public/` (raíz del repo)
- [ ] `.env` creado a mano, con `DB_CONNECTION=mysql` (¡no `sqlite`!) y credenciales reales
- [ ] `composer install --no-dev`, `migrate --force --seed`, `storage:link` ejecutados
- [ ] Datos reales importados (opcional) y dump borrado del servidor
- [ ] Webhook y Payment Links de Stripe apuntando a producción
- [ ] SSL activo
