<?php

use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

// Antes del catch-all del SPA: si no, "/{any?}" se los comería y devolvería
// el HTML de la app en vez del robots.txt/sitemap.xml.
Route::get('/robots.txt', [SeoController::class, 'robots']);
Route::get('/sitemap.xml', [SeoController::class, 'sitemap']);

// Sirve el SPA de Vue para cualquier ruta que no sea /api/*; vue-router controla
// la navegación en el cliente. Los ficheros estáticos reales (imágenes, build de
// Vite) los sirve Apache/PHP directamente antes de llegar aquí.
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
