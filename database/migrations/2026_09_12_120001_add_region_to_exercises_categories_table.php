<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercises_categories', function (Blueprint $table) {
            $table->enum('region', ['superior', 'inferior', 'otros'])->nullable()->after('category_name');
        });

        $now = now();

        $groups = [
            'superior' => [
                'Abdominales', 'Antebrazos', 'Bíceps', 'Cuello', 'Dorsales',
                'Espalda baja', 'Espalda alta', 'Hombros', 'Pecho', 'Trapecio', 'Tríceps',
            ],
            'inferior' => [
                'Abductores', 'Aductores', 'Cuádriceps', 'Glúteos', 'Isquiotibiales', 'Gemelos',
            ],
            'otros' => [
                'Cardio', 'Cuerpo entero', 'Otro',
            ],
        ];

        foreach ($groups as $region => $names) {
            foreach ($names as $name) {
                DB::table('exercises_categories')->updateOrInsert(
                    ['category_name' => $name],
                    ['region' => $region, 'updated_at' => $now, 'created_at' => $now]
                );
            }
        }

        // Cualquier categoría preexistente que no estuviera en la lista canónica
        // (p. ej. nombres antiguos ya usados por ejercicios existentes) se agrupa
        // en "Otros" para que siempre aparezca en algún bloque del selector.
        DB::table('exercises_categories')->whereNull('region')->update(['region' => 'otros']);
    }

    public function down(): void
    {
        Schema::table('exercises_categories', function (Blueprint $table) {
            $table->dropColumn('region');
        });
    }
};
