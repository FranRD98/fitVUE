<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routines', function (Blueprint $table) {
            $table->boolean('is_catalog')->default(false)->after('published');
        });

        Schema::table('diets', function (Blueprint $table) {
            $table->boolean('is_catalog')->default(false)->after('meals');
            $table->boolean('published')->default(false)->after('is_catalog');
        });

        // Lo que ya estaba publicado en la web pasa al catálogo y sale
        // del listado de rutinas de clientes.
        DB::table('routines')->where('published', true)->update(['is_catalog' => true]);
    }

    public function down(): void
    {
        Schema::table('routines', function (Blueprint $table) {
            $table->dropColumn('is_catalog');
        });

        Schema::table('diets', function (Blueprint $table) {
            $table->dropColumn(['is_catalog', 'published']);
        });
    }
};
