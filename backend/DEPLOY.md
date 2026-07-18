# Desplegar FitVue en Plesk

## Cuenta admin garantizada

`admin@fitvue.es` es una cuenta real importada del dump de Supabase, pero además está protegida por `php artisan admin:sync`, que fuerza su rol a `admin` y su contraseña al valor de `ADMIN_PASSWORD` (`.env`). Ejecútalo siempre que:

- Acabes de correr `php artisan migrate:fresh` (sin el import).
- Necesites recuperar el acceso porque alguien cambió esa contraseña desde la app.

Si usas `php artisan app:import-supabase-dump {ruta}`, esto ya se hace automáticamente al final (también reenlaza las imágenes de `storage/app/public` si existen). No hace falta ejecutarlo a mano después de un import.


Arquitectura: **API Laravel + MySQL** en un (sub)dominio, y el **SPA Vue** compilado como archivos estáticos en otro (sub)dominio (o el mismo, en una ruta distinta). Ejemplo usado abajo:

- API: `api.fitvue.com` → proyecto `fitvue-backend`
- App: `app.fitvue.com` (o `fitvue.com`) → build de `fitVUE` (carpeta `dist/`)

## 1. Requisitos en Plesk

- PHP 8.3+ con extensiones: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `curl`, `bcmath`, `ctype`, `tokenizer`, `xml`
- Extensión "Git" o acceso SSH/SFTP para subir archivos
- Una base de datos MySQL (Plesk → Bases de datos → Añadir base de datos)
- Composer (Plesk lo incluye vía "Composer" en el dominio, o por SSH)

## 2. Base de datos

En Plesk → Bases de datos, crea una BD (p. ej. `fitvue`) y un usuario con todos los privilegios sobre ella. Anota host/usuario/contraseña.

## 3. Backend (Laravel)

1. Sube el contenido de `fitvue-backend/` al dominio `api.fitvue.com` (por Git o SFTP), **fuera** del `public_html` si Plesk lo permite, o con el "Document root" del dominio apuntando a `fitvue-backend/public`.
2. En el dominio, configura el **Document Root** para que apunte a la carpeta `public/` del proyecto (Plesk → Dominio → Hosting → Configuración de Apache/nginx → Document root).
3. Copia `.env` y ajusta para producción:
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://api.fitvue.com
   FRONTEND_URL=https://app.fitvue.com

   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_DATABASE=<tu_bd_plesk>
   DB_USERNAME=<tu_usuario_plesk>
   DB_PASSWORD=<tu_password_plesk>

   STRIPE_SECRET=sk_live_...
   STRIPE_WEBHOOK_SECRET=whsec_...
   STRIPE_PRICE_PREMIUM=price_...
   STRIPE_PRICE_PRO=price_...
   ```
4. Restringe CORS a tu dominio real en `config/cors.php` (`allowed_origins` → `['https://app.fitvue.com']` en vez de `['*']`).
5. Por SSH (o el "Composer"/"Ejecutar comando" de Plesk):
   ```
   composer install --no-dev --optimize-autoloader
   php artisan key:generate --force
   php artisan migrate --force
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   ```
6. Da permisos de escritura a `storage/` y `bootstrap/cache/` (Plesk → Administrador de archivos, o `chmod -R 775`).
7. Activa el certificado SSL (Let's Encrypt) para `api.fitvue.com` desde Plesk.

### Stripe en producción

- En el Dashboard de Stripe, añade un endpoint de webhook `https://api.fitvue.com/api/stripe/webhook` escuchando `checkout.session.completed`, y copia el signing secret a `STRIPE_WEBHOOK_SECRET`.
- En cada Payment Link (Premium y Pro), configura la redirección "after payment" a `https://app.fitvue.com/pago-aceptado?session_id={CHECKOUT_SESSION_ID}` — el frontend necesita ese `session_id` para confirmar el pago de forma segura.
- Sustituye `sk_test_...` / `price_..._test` por tus claves y precios reales (modo live).

## 4. Frontend (Vue)

1. En tu máquina, dentro de `fitVUE/`, crea `.env.production`:
   ```
   VITE_API_URL=https://api.fitvue.com/api
   ```
2. Compila: `npm run build` → genera `dist/`.
3. Sube el **contenido** de `dist/` al Document Root de `app.fitvue.com`.
4. Como es una SPA con rutas del lado del cliente (`vue-router` en modo `history`), necesitas reescribir todas las rutas a `index.html`. En Plesk (Apache), añade en "Directivas adicionales de nginx" o un `.htaccess` en la raíz del dominio:
   ```apache
   <IfModule mod_rewrite.c>
     RewriteEngine On
     RewriteBase /
     RewriteRule ^index\.html$ - [L]
     RewriteCond %{REQUEST_FILENAME} !-f
     RewriteCond %{REQUEST_FILENAME} !-d
     RewriteRule . /index.html [L]
   </IfModule>
   ```
   Si Plesk usa nginx como proxy, añade en "Directivas adicionales de nginx":
   ```nginx
   location / {
     try_files $uri $uri/ /index.html;
   }
   ```
5. Activa SSL (Let's Encrypt) también para `app.fitvue.com`.

## 5. Imágenes que aún apuntan a Supabase

El código tenía ~15 URLs fijas a tu bucket de Supabase (icono de perfil por defecto y las imágenes ilustrativas de "Nueva revisión"). Si vas a dejar de pagar Supabase del todo, descarga esos archivos mientras el bucket siga activo y súbelos a `fitVUE/public/icons/...`, actualizando las rutas en:
- `src/components/layout/Header.vue`
- `src/components/layout/dashboard/DashboardHeader.vue`
- `src/components/dashboard/NewReview.vue`
- `src/views/auth/StartChange.vue`

Si solo quieres evitar el plan de pago pero el proyecto Supabase gratuito te vale para servir esos estáticos, puedes dejarlos como están.

## 6. Checklist rápido

- [ ] BD MySQL creada en Plesk
- [ ] `.env` de producción del backend con credenciales reales y `APP_DEBUG=false`
- [ ] `composer install --no-dev`, `migrate --force`, `storage:link` ejecutados
- [ ] CORS restringido al dominio real del frontend
- [ ] Webhook y Payment Links de Stripe apuntando a producción
- [ ] `npm run build` del frontend con `VITE_API_URL` de producción
- [ ] Rewrite a `index.html` configurado en el dominio del frontend
- [ ] SSL activo en ambos dominios
