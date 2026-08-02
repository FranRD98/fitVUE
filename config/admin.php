<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cuenta de administrador garantizada
    |--------------------------------------------------------------------------
    |
    | Estos valores definen la cuenta admin "de emergencia" que el comando
    | `php artisan admin:sync` crea o fuerza en cada entorno, para asegurar
    | que siempre hay una forma de entrar aunque se reimporten datos externos.
    |
    */

    'email' => env('ADMIN_EMAIL', 'admin@fitvue.es'),
    'password' => env('ADMIN_PASSWORD', 'fitvue2026'),
    'name' => env('ADMIN_NAME', 'Admin'),
    'last_name' => env('ADMIN_LAST_NAME', 'FitVue'),

];
