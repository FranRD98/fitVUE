<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
    <link rel="icon" href="/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Fitvue - La app para mejorar tu estilo de vida</title>

    <!-- PWA -->
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#005F78">

    <!-- iOS: "Añadir a pantalla de inicio" -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="fitVUE">
    <link rel="apple-touch-icon" href="/img/icons/apple-touch-icon.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
      #app-splash {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        transition: opacity 0.25s ease;
      }
      #app-splash img {
        width: 96px;
        height: 96px;
        border-radius: 22px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
      }
    </style>
  </head>
  <body>
    <!-- Splash visible desde el primer pintado (antes de que cargue el JS de
         Vue), para que la primera carga se vea como una app nativa arrancando
         en vez de una pantalla en blanco. Se retira desde app.js al montar. -->
    <div id="app-splash">
      <img src="/img/icons/apple-touch-icon.png" alt="fitVUE">
    </div>
    <div id="app"></div>

    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
          navigator.serviceWorker.register('/sw.js').catch(() => {});
        });
      }
    </script>
  </body>
</html>
