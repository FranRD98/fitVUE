# Desplegar FitVue en Plesk

Arquitectura: **un único (sub)dominio** (`fitvue.franriera.es`) sirviendo tanto la API Laravel como el SPA de Vue ya compilado. Laravel es quien responde a todo:

- `GET /api/*` → controladores de la API (`routes/api.php`)
- Cualquier otra ruta (`/`, `/dashboard`, `/rutinas`, ...) → el `index.html` de Vue ya compilado (`public/app.html`), y `vue-router` toma el control en el navegador
- Ficheros estáticos reales (`/assets/*.js`, `/icons/*.svg`, `/favicon.ico`...) → servidos directamente por Apache/PHP, sin pasar por Laravel

Todo el repositorio (frontend Vue en la raíz + backend Laravel en `backend/`) vive en un único repo de GitHub.

## 1. Requisitos en Plesk

- PHP 8.3+ con extensiones: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `curl`, `bcmath`, `ctype`, `tokenizer`, `xml`
- Extensión "Git" (para clonar el repo) o acceso SSH/SFTP
- Una base de datos MySQL (Plesk → Bases de datos → Añadir base de datos)
- Composer (Plesk lo incluye vía "Composer" en el dominio, o por SSH)

## 2. Base de datos

En Plesk → Bases de datos, crea una BD (p. ej. `fitvue`) y un usuario con todos los privilegios sobre ella. Anota host/usuario/contraseña.

## 3. Document Root

El Document Root del dominio/subdominio debe apuntar a **`backend/public`**, no a la raíz del repo (si apunta a la raíz, cualquiera podría descargar el código fuente o el `.env`):

Plesk → tu dominio → *Hosting y DNS* → *Configuración de hosting* → Document root: `backend/public` (relativo a donde Plesk clonó el repo, normalmente `httpdocs/backend/public`).

## 4. `.env` de producción

Plesk no sube el `.env` (está en `.gitignore` a propósito). Créalo a mano en `backend/.env` con:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://fitvue.franriera.es
FRONTEND_URL=https://fitvue.franriera.es

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=<tu_bd_plesk>
DB_USERNAME=<tu_usuario_plesk>
DB_PASSWORD=<tu_password_plesk>

STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_PRICE_PREMIUM=price_...
STRIPE_PRICE_PRO=price_...

ADMIN_EMAIL=admin@fitvue.es
ADMIN_PASSWORD=<contraseña real, cámbiala del valor de ejemplo>
```

Como frontend y API comparten el mismo origen, no hace falta preocuparse por CORS (pero no molesta dejarlo con `allowed_origins => ['*']` en `config/cors.php`).

## 5. Comandos (por SSH o "Ejecutar comando" de Plesk, dentro de `backend/`)

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
php artisan admin:sync
php artisan config:cache
php artisan route:cache
```

Da permisos de escritura a `storage/` y `bootstrap/cache/` si Plesk no lo hace automáticamente (`chmod -R 775`).

## 6. SSL

Activa el certificado Let's Encrypt para el dominio desde Plesk.

## 7. Cuenta admin garantizada

`admin@fitvue.es` está protegida por `php artisan admin:sync`, que fuerza su rol a `admin` y su contraseña al valor de `ADMIN_PASSWORD` del `.env`. Ejecútalo (ya se hizo en el paso 5, y se hace solo tras `app:import-supabase-dump`) siempre que:

- Acabes de correr `php artisan migrate:fresh` sin el import.
- Necesites recuperar el acceso porque alguien cambió esa contraseña desde la app.

## 8. Stripe en producción

- En el Dashboard de Stripe, añade un endpoint de webhook `https://fitvue.franriera.es/api/stripe/webhook` escuchando `checkout.session.completed`, y copia el signing secret a `STRIPE_WEBHOOK_SECRET`.
- En cada Payment Link (Premium y Pro), configura la redirección "after payment" a `https://fitvue.franriera.es/pago-aceptado?session_id={CHECKOUT_SESSION_ID}`.
- Sustituye las claves y precios de test por los reales (modo live).

## 9. Cómo publicar cambios del frontend

El HTML/JS/CSS de Vue ya compilado vive dentro de `backend/public/` (versionado en git), para que el despliegue en Plesk no necesite Node.js. Cuando cambies algo en el frontend:

```bash
cd fitVUE
npm run build                      # usa .env.production (VITE_API_URL=/api)
rsync -a --exclude='index.html' --exclude='_redirects' dist/ backend/public/
cp dist/index.html backend/public/app.html
git add backend/public
git commit -m "Actualizar build del frontend"
git push
```

Luego, en Plesk, actualiza el despliegue Git (o haz `git pull` por SSH) — no hace falta repetir `composer install` ni migraciones si solo cambió el frontend.

## 10. Imágenes que aún apuntan a Supabase

Ya se recuperaron y colocaron en `backend/storage/app/public/` (fotos de perfil reales, cabeceras de guías, iconos de categorías) e `public/icons|img` (ilustraciones estáticas del frontend). Si en el futuro subes contenido nuevo desde Supabase que se te haya quedado atrás, sigue el mismo patrón: cópialo a `backend/storage/app/public/...` y actualiza la referencia en la base de datos (o usa `php artisan tinker`).

## 11. Checklist rápido

- [ ] BD MySQL creada en Plesk
- [ ] Document Root del dominio → `backend/public`
- [ ] `backend/.env` creado a mano con credenciales reales y `APP_DEBUG=false`
- [ ] `composer install --no-dev`, `migrate --force`, `storage:link`, `admin:sync` ejecutados
- [ ] Webhook y Payment Links de Stripe apuntando a producción
- [ ] SSL activo
- [ ] `npm run build` + copiado a `backend/public` si tocaste el frontend
