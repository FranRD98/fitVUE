<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
    <link rel="icon" href="/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>fitVUE - La app para mejorar tu estilo de vida</title>
    <meta name="description" content="Entrena, cuida tu alimentación y sigue tu progreso con fitVUE: rutinas, dietas y estadísticas en una sola app para móvil, tablet y ordenador.">

    <!-- Vista previa al compartir el enlace (WhatsApp, redes, Slack...). Sin
         esto, la mayoría de apps muestran el enlace pelado, sin imagen ni
         descripción. -->
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="fitVUE">
    <meta property="og:title" content="fitVUE - La app para mejorar tu estilo de vida">
    <meta property="og:description" content="Entrena, cuida tu alimentación y sigue tu progreso con fitVUE: rutinas, dietas y estadísticas en una sola app.">
    <meta property="og:url" content="{{ config('app.url') }}">
    <meta property="og:image" content="{{ config('app.url') }}/img/icons/pwa-512.png">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="fitVUE - La app para mejorar tu estilo de vida">
    <meta name="twitter:description" content="Entrena, cuida tu alimentación y sigue tu progreso con fitVUE: rutinas, dietas y estadísticas en una sola app.">
    <meta name="twitter:image" content="{{ config('app.url') }}/img/icons/pwa-512.png">

    <!-- PWA -->
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#005F78">

    <!-- iOS: "Añadir a pantalla de inicio" -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <!-- default: barra de estado opaca. black-translucent la deja translúcida
         y iOS difumina (efecto glass) lo que hay bajo el notch. iOS guarda
         este valor al añadir la PWA a inicio: hay que quitarla y volver a
         añadirla para que el cambio se aplique. -->
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
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
