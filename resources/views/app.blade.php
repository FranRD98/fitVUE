<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
    <link rel="icon" href="/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Fitvue - La app para mejorar tu estilo de vida</title>

    <!-- PWA -->
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#005F78">

    <!-- iOS: "Añadir a pantalla de inicio" -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="fitVUE">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
  <body>
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
