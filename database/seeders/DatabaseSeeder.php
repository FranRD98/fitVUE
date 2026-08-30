<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Garantiza la cuenta admin (email/contraseña de config/admin.php, ver .env).
        Artisan::call('admin:sync');

        // Catálogo de arranque: categorías, ejercicios, rutinas, dietas, ingredientes
        // y guías de ejemplo, para que la app no empiece vacía. Sin usuarios reales.
        $this->call(ReferenceDataSeeder::class);
    }
}
