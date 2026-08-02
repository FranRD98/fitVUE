<?php

use Illuminate\Support\Facades\Route;

// Sirve el SPA de Vue para cualquier ruta que no sea /api/*; vue-router controla
// la navegación en el cliente. Los ficheros estáticos reales (imágenes, build de
// Vite) los sirve Apache/PHP directamente antes de llegar aquí.
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
