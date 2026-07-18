<?php

use Illuminate\Support\Facades\Route;

// Sirve el SPA de Vue ya compilado (public/app.html) para cualquier ruta que no sea /api/*
// ni un fichero estático real (esas las sirve Apache/PHP directamente antes de llegar aquí).
Route::get('/{any?}', function () {
    return response()->file(public_path('app.html'));
})->where('any', '.*');
